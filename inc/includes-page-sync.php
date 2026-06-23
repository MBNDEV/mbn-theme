<?php
/**
 * Page Content Sync - Import/Export page content as patterns or files.
 *
 * Use this for "structural" pages that should be consistent across environments
 * (e.g., About, Services, Privacy Policy).
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define filesystem permission constants if not already defined
if ( ! defined( 'FS_CHMOD_DIR' ) ) {
	define( 'FS_CHMOD_DIR', 0755 );
}
if ( ! defined( 'FS_CHMOD_FILE' ) ) {
	define( 'FS_CHMOD_FILE', 0644 );
}

// Load configuration
require_once get_theme_file_path( 'inc/includes-page-sync-config.php' );

/**
 * Add Page Sync submenu under Tools.
 */
function custom_theme_add_page_sync_menu() {
	add_management_page(
      __( 'Page Content Sync', 'mbn-theme' ),
      __( 'Page Content Sync', 'mbn-theme' ),
      'manage_options',
      'page-content-sync',
      'custom_theme_render_page_sync_page'
	);
}
add_action( 'admin_menu', 'custom_theme_add_page_sync_menu' );

/**
 * Get all pages eligible for sync (published pages only).
 *
 * @return array Array of WP_Post objects.
 */
function custom_theme_get_syncable_pages() {
	return get_posts(
      array(
		  'post_type'      => 'page',
		  'post_status'    => 'publish',
		  'posts_per_page' => -1,
		  'orderby'        => 'title',
		  'order'          => 'ASC',
	  )
	);
}

/**
 * Get available page pattern files for import selection.
 *
 * @return array Array of file info: filename, slug, title, status.
 */
function custom_theme_get_importable_page_files() {
	$pattern_dir   = get_theme_file_path( 'template-parts/page-patterns' );
	$pattern_files = is_dir( $pattern_dir ) ? glob( $pattern_dir . '/*.json' ) : array();
	$files         = array();

  foreach ( (array) $pattern_files as $file ) {
      $raw  = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
      $data = $raw ? json_decode( $raw, true ) : null;
    if ( ! is_array( $data ) || empty( $data['title'] ) || empty( $data['slug'] ) ) {
        continue;
    }
      $files[] = array(
          'filename' => basename( $file ),
          'slug'     => $data['slug'],
          'title'    => $data['title'],
          'status'   => isset( $data['status'] ) ? $data['status'] : 'publish',
      );
  }

	return $files;
}

/**
 * Get featured image data for export.
 *
 * @param int $page_id Page ID.
 * @return array Array with 'url' and 'path' keys.
 */
function custom_theme_get_featured_image_data( $page_id ) {
	$featured_image_id = get_post_thumbnail_id( $page_id );
	$data              = array(
		'url'  => '',
		'path' => '',
	);

	if ( ! $featured_image_id ) {
		return $data;
	}

	$image_url = wp_get_attachment_url( $featured_image_id );

	// Check if image is in theme assets (preferred for structural pages)
	if ( false !== strpos( $image_url, get_theme_file_uri( 'assets/' ) ) ) {
		// Image is in theme assets - store relative path
		$data['path'] = str_replace( get_theme_file_uri( '' ), '', $image_url );
		$data['path'] = ltrim( $data['path'], '/' );
	} else {
		// Image is in uploads folder (user content) - store full URL
		$data['url'] = $image_url;
	}

	return $data;
}

/**
 * Get filtered custom fields for export.
 *
 * @param int $page_id Page ID.
 * @return array Filtered custom fields.
 */
function custom_theme_get_filtered_custom_fields( $page_id ) {
	$all_meta      = get_post_meta( $page_id );
	$custom_fields = array();

	// Filter out WordPress internal meta (starts with _)
	// But keep some important ones like _wp_page_template
	$export_meta_keys = array( '_wp_page_template' );

  foreach ( $all_meta as $key => $values ) {
      // Skip internal WordPress meta except whitelisted ones
    if ( '_' === substr( $key, 0, 1 ) && ! in_array( $key, $export_meta_keys, true ) ) {
        continue;
    }

      // Store meta (handle serialized data)
      $custom_fields[ $key ] = is_array( $values ) && 1 === count( $values ) ? $values[0] : $values;
  }

	return $custom_fields;
}

/**
 * Decode JSON Unicode escape sequences in content.
 *
 * WordPress stores block attributes with JSON-encoded HTML entities.
 * This function decodes them back to their original characters.
 *
 * @param string $content Content with potential JSON Unicode escapes.
 * @return string Content with decoded Unicode sequences.
 */
function custom_theme_decode_json_unicode_in_content( $content ) {
	// Decode common JSON Unicode escape sequences
	$replacements = array(
		'\u003c' => '<',
		'\u003e' => '>',
		'\u0022' => '"',
		'\u0027' => "'",
		'\u0026' => '&',
	);

	return str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
}

// phpcs:disable Generic.Metrics.CyclomaticComplexity.MaxExceeded

/**
 * Export a page's content to a pattern file.
 *
 * @param int $page_id Page ID to export.
 * @return bool|string File path on success, false on failure.
 * @throws Exception If export fails.
 */
function custom_theme_export_page_to_pattern( $page_id ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
	$page = get_post( $page_id );

  if ( ! $page instanceof \WP_Post ) {
      throw new Exception( sprintf( 'Invalid page ID: %d. Post does not exist.', absint( $page_id ) ) );
  }

  if ( 'page' !== $page->post_type ) {
      throw new Exception( sprintf( 'Post ID %d is not a page (type: %s).', absint( $page_id ), esc_html( $page->post_type ) ) );
  }

	$slug       = $page->post_name;
	$title      = $page->post_title;
	$content    = custom_theme_decode_json_unicode_in_content( $page->post_content );
	$excerpt    = $page->post_excerpt;
	$status     = $page->post_status;
	$parent     = $page->post_parent;
	$menu_order = $page->menu_order;
	$template   = get_page_template_slug( $page_id );

  if ( empty( $slug ) ) {
      throw new Exception( sprintf( 'Page "%s" has no slug. Please set a permalink.', esc_html( $title ) ) );
  }

	// Get featured image data
	$image_data          = custom_theme_get_featured_image_data( $page_id );
	$featured_image_url  = $image_data['url'];
	$featured_image_path = $image_data['path'];

	// Get filtered custom fields
	$custom_fields = custom_theme_get_filtered_custom_fields( $page_id );

	// Initialize WP_Filesystem.
	global $wp_filesystem;
  if ( empty( $wp_filesystem ) ) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
      WP_Filesystem();
  }

	// Create pattern file
	$pattern_dir = get_theme_file_path( 'template-parts/page-patterns' );

	// Create directory if it doesn't exist
  if ( ! $wp_filesystem->is_dir( $pattern_dir ) ) {
    if ( ! $wp_filesystem->mkdir( $pattern_dir, FS_CHMOD_DIR ) ) {
        throw new Exception( sprintf( 'Failed to create directory: %s. Check file permissions.', esc_html( $pattern_dir ) ) );
    }
  }

	// Check if directory is writable
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
  if ( ! is_writable( $pattern_dir ) ) {
      throw new Exception( sprintf( 'Directory is not writable: %s. Check file permissions.', esc_html( $pattern_dir ) ) );
  }

	$export_data = array(
		'title'               => $title,
		'slug'                => $slug,
		'status'              => $status,
		'excerpt'             => $excerpt,
		'parent_slug'         => $parent > 0 ? get_post_field( 'post_name', $parent ) : '',
		'menu_order'          => absint( $menu_order ),
		'template'            => $template,
		'featured_image_url'  => $featured_image_url,
		'featured_image_path' => $featured_image_path,
		'custom_fields'       => $custom_fields,
		'content'             => $content,
	);

	$file_content = wp_json_encode( $export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

	if ( false === $file_content ) {
		throw new Exception( sprintf( 'Failed to encode page data for "%s" as JSON.', esc_html( $title ) ) );
	}

	$file_path = $pattern_dir . '/' . $slug . '.json';

	// Write file using WP_Filesystem.
	$written = $wp_filesystem->put_contents( $file_path, $file_content, FS_CHMOD_FILE );

    if ( false === $written ) {
      throw new Exception( sprintf( 'Failed to write file: %s. Check file permissions.', esc_html( $file_path ) ) );
    }

	return $file_path;
}
// phpcs:enable Generic.Metrics.CyclomaticComplexity.MaxExceeded

/**
 * Import an external image into the media library.
 *
 * @param string $image_url URL of the image to import.
 * @param int    $post_id   Post ID to attach the image to.
 * @return int Attachment ID on success, 0 on failure.
 */
function custom_theme_import_external_image( $image_url, $post_id = 0 ) {
  if ( empty( $image_url ) ) {
      return 0;
  }

	// Check if URL is accessible
	$response = wp_remote_head( $image_url );
  if ( is_wp_error( $response ) ) {
      return 0;
  }

	// Download image
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( $image_url );

  if ( is_wp_error( $tmp ) ) {
      return 0;
  }

	$file_array = array(
		'name'     => basename( $image_url ),
		'tmp_name' => $tmp,
	);

	// Upload to media library
	$attachment_id = media_handle_sideload( $file_array, $post_id );

	// Clean up temp file
	if ( file_exists( $tmp ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
		unlink( $tmp );
	}

	if ( is_wp_error( $attachment_id ) ) {
		return 0;
	}

	return $attachment_id;
}

/**
 * Get or create an attachment for a theme image file.
 *
 * @param string $file_path Absolute path to image file in theme.
 * @param int    $post_id   Post ID to attach to.
 * @return int Attachment ID, or 0 on failure.
 */
function custom_theme_get_or_create_theme_image_attachment( $file_path, $post_id = 0 ) {
  if ( ! file_exists( $file_path ) ) {
      return 0;
  }

	$filename = basename( $file_path );

	// Check if attachment already exists for this file
	$existing = get_posts(
      array(
		  'post_type'      => 'attachment',
		  'post_status'    => 'inherit',
		  'posts_per_page' => 1,
		  'meta_query'     => array(
			  array(
				  'key'   => '_theme_asset_file',
				  'value' => $file_path,
			  ),
		  ),
	  )
	);

  if ( ! empty( $existing ) ) {
      return $existing[0]->ID;
  }

	// Create attachment
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$filetype = wp_check_filetype( $filename );

	$attachment = array(
		'guid'           => get_theme_file_uri( str_replace( get_theme_file_path( '' ), '', $file_path ) ),
		'post_mime_type' => $filetype['type'],
		'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	$attachment_id = wp_insert_attachment( $attachment, $file_path, $post_id );

    if ( is_wp_error( $attachment_id ) ) {
      return 0;
    }

    // Generate attachment metadata
    $attach_data = wp_generate_attachment_metadata( $attachment_id, $file_path );
    wp_update_attachment_metadata( $attachment_id, $attach_data );

    // Mark as theme asset for future lookups
    update_post_meta( $attachment_id, '_theme_asset_file', $file_path );

    return $attachment_id;
}

/**
 * Validate pattern file data structure.
 *
 * @param mixed  $data Data from pattern file.
 * @param string $filename File name for error messages.
 * @throws Exception If data is invalid.
 */
function custom_theme_validate_pattern_file_data( $data, $filename ) {
  if ( ! is_array( $data ) ) {
      throw new Exception( sprintf( 'Invalid file format in %s: expected array, got %s', esc_html( $filename ), esc_html( gettype( $data ) ) ) );
  }

  if ( ! isset( $data['title'] ) || ! isset( $data['slug'] ) || ! isset( $data['content'] ) ) {
      throw new Exception( sprintf( 'Missing required fields (title, slug, content) in %s', esc_html( $filename ) ) );
  }
}

/**
 * Resolve parent page ID from slug.
 *
 * @param string $parent_slug Parent page slug.
 * @return int Parent page ID or 0 if not found.
 */
function custom_theme_resolve_parent_page_id( $parent_slug ) {
  if ( empty( $parent_slug ) ) {
      return 0;
  }

	$parent_page = get_page_by_path( $parent_slug, OBJECT, 'page' );
  if ( $parent_page instanceof \WP_Post ) {
      return $parent_page->ID;
  }

	return 0;
}

/**
 * Handle featured image for imported page.
 *
 * @param int    $page_id Page ID.
 * @param string $featured_image_path Theme-relative image path.
 * @param string $featured_image_url External image URL.
 */
function custom_theme_handle_page_featured_image( $page_id, $featured_image_path, $featured_image_url ) {
  if ( ! empty( $featured_image_path ) ) {
      // Image is in theme assets - attach by path (preferred).
      $theme_image_path = get_theme_file_path( $featured_image_path );

    if ( file_exists( $theme_image_path ) ) {
        $attachment_id = custom_theme_get_or_create_theme_image_attachment( $theme_image_path, $page_id );
      if ( $attachment_id > 0 ) {
        set_post_thumbnail( $page_id, $attachment_id );
      }
    }
  } elseif ( ! empty( $featured_image_url ) ) {
      // Image is external URL - try to find or download.
      $attachment_id = attachment_url_to_postid( $featured_image_url );

    if ( $attachment_id > 0 ) {
        set_post_thumbnail( $page_id, $attachment_id );
    } else {
        // Download external image.
        $image_id = custom_theme_import_external_image( $featured_image_url, $page_id );
      if ( $image_id > 0 ) {
          set_post_thumbnail( $page_id, $image_id );
      }
    }
  }
}

/**
 * Resolve a template slug, falling back to the blank template.
 *
 * @param string $template Raw template value from pattern data.
 * @return string Resolved template slug.
 */
function custom_theme_resolve_page_template_slug( $template ) {
  if ( empty( $template ) || 'default' === $template ) {
      return 'page-templates/template-blank.php';
  }
	return $template;
}

/**
 * Normalize the _wp_page_template meta value to a valid template slug string.
 *
 * @param mixed $meta_value Raw meta value.
 * @return string Safe template slug.
 */
function custom_theme_sanitize_page_template_meta_value( $meta_value ) {
  if ( is_array( $meta_value ) ) {
      $meta_value = array_values(
        array_filter(
          $meta_value,
          'is_string'
        )
      );
      $meta_value = ! empty( $meta_value ) ? $meta_value[0] : '';
  }

  if ( ! is_string( $meta_value ) ) {
      $meta_value = '';
  }

	$meta_value = trim( $meta_value );

  if ( '' === $meta_value || 'default' === $meta_value ) {
      return 'page-templates/template-blank.php';
  }

	return $meta_value;
}

/**
 * Ensure _wp_page_template in custom fields defaults to the blank template.
 *
 * @param array $custom_fields Custom fields array.
 * @return array Normalized custom fields.
 */
function custom_theme_normalize_custom_fields_template( $custom_fields ) {
	$meta                               = isset( $custom_fields['_wp_page_template'] ) ? $custom_fields['_wp_page_template'] : '';
	$custom_fields['_wp_page_template'] = custom_theme_sanitize_page_template_meta_value( $meta );
	return $custom_fields;
}

/**
 * Normalize page pattern data with defaults for optional fields.
 *
 * @param array $data Raw pattern data.
 * @return array Normalized data with all optional fields set.
 */
function custom_theme_normalize_page_pattern_data( $data ) {
	$normalized = wp_parse_args(
      $data,
      array(
		  'status'              => 'publish',
		  'excerpt'             => '',
		  'parent_slug'         => '',
		  'menu_order'          => 0,
		  'template'            => '',
		  'featured_image_path' => '',
		  'featured_image_url'  => '',
		  'custom_fields'       => array(),
	  )
	);

	$normalized['menu_order']    = (int) $normalized['menu_order'];
	$normalized['template']      = custom_theme_resolve_page_template_slug( $normalized['template'] );
	$normalized['custom_fields'] = custom_theme_normalize_custom_fields_template( $normalized['custom_fields'] );

	return $normalized;
}

/**
 * Create or update a WordPress page.
 *
 * @param array        $post_data Post data array.
 * @param WP_Post|null $existing Existing page object or null.
 * @return int Page ID.
 * @throws Exception If page creation/update fails.
 */
function custom_theme_create_or_update_page( $post_data, $existing ) {
  if ( $existing instanceof \WP_Post ) {
      // Update existing page.
      $post_data['ID'] = $existing->ID;
      $result          = wp_update_post( $post_data, true );

    if ( is_wp_error( $result ) ) {
        throw new Exception( sprintf( 'Failed to update page "%s": %s', esc_html( $post_data['post_title'] ), esc_html( $result->get_error_message() ) ) );
    }

      return $existing->ID;
  } else {
      // Create new page.
      $result = wp_insert_post( $post_data, true );

    if ( is_wp_error( $result ) ) {
        throw new Exception( sprintf( 'Failed to create page "%s": %s', esc_html( $post_data['post_title'] ), esc_html( $result->get_error_message() ) ) );
    }

      return $result;
  }
}

/**
 * Replace domain URLs in content for the current environment.
 *
 * This replaces common local/staging URLs with the current site URL.
 *
 * @param string $content Page content.
 * @return string Content with replaced URLs.
 */
function custom_theme_replace_domain_urls_in_content( $content ) {
	// Get source domains from config
	$source_domains = custom_theme_get_source_domains();

	// Get current site URL (without trailing slash)
	$current_site_url = untrailingslashit( get_site_url() );

	// Skip replacement if current URL is already in the source list
  if ( in_array( $current_site_url, $source_domains, true ) ) {
      // Remove current site URL from replacements to avoid self-replacement
      $source_domains = array_filter(
        $source_domains,
        function ( $domain ) use ( $current_site_url ) {
            return $domain !== $current_site_url;
        }
      );
  }

	// Replace each source domain with current site URL
  foreach ( $source_domains as $source_domain ) {
    if ( false !== strpos( $content, $source_domain ) ) {
        $content = str_replace( $source_domain, $current_site_url, $content );
    }
  }

	// Strip environment-specific media library IDs from block attributes.
	// Image IDs are local to each environment; blocks fall back to their
	// default theme assets when only the URL (or no value) is present.
	$content = preg_replace( '/"[a-zA-Z]+ImageId":\d+,?\s*/U', '', $content );
	// Clean up any trailing commas left before a closing brace/bracket.
	$content = preg_replace( '/,(\s*[}\]])/m', '$1', $content );

	return $content;
}

/**
 * Import single page from pattern data.
 *
 * @param array $data Page pattern data.
 * @return int Created or updated page ID.
 * @throws Exception If import fails.
 */
function custom_theme_import_single_page_from_pattern( $data ) {
	// Normalize optional fields with defaults.
	$normalized = custom_theme_normalize_page_pattern_data( $data );

	// Resolve parent ID if parent slug provided.
	$parent_id = 0;
  if ( ! empty( $normalized['parent_slug'] ) ) {
      $parent_id = custom_theme_resolve_parent_page_id( $normalized['parent_slug'] );
  }

	// Check if page exists.
	$existing = get_page_by_path( $data['slug'], OBJECT, 'page' );

	// Replace domain URLs in content for current environment
	$content = custom_theme_replace_domain_urls_in_content( $data['content'] );

	$post_data = array(
		'post_type'    => 'page',
		'post_title'   => $data['title'],
		'post_name'    => $data['slug'],
		'post_content' => $content,
		'post_excerpt' => $normalized['excerpt'],
		'post_status'  => $normalized['status'],
		'post_parent'  => $parent_id,
		'menu_order'   => $normalized['menu_order'],
	);

	// Create or update page.
	$page_id = custom_theme_create_or_update_page( $post_data, $existing );

	// Set page template.
	if ( ! empty( $normalized['template'] ) ) {
		update_post_meta( $page_id, '_wp_page_template', $normalized['template'] );
	}

	// Handle featured image.
	custom_theme_handle_page_featured_image( $page_id, $normalized['featured_image_path'], $normalized['featured_image_url'] );

	// Set custom fields.
	if ( ! empty( $normalized['custom_fields'] ) ) {
		// Only private meta keys that are explicitly whitelisted may be written.
		// Public meta keys (no leading underscore) are always allowed.
		$allowed_private_meta = array( '_wp_page_template' );

      foreach ( $normalized['custom_fields'] as $meta_key => $meta_value ) {
          $meta_key = sanitize_key( $meta_key );
        if ( '_' === substr( $meta_key, 0, 1 ) && ! in_array( $meta_key, $allowed_private_meta, true ) ) {
            continue;
        }
        if ( '_wp_page_template' === $meta_key ) {
            $meta_value = custom_theme_sanitize_page_template_meta_value( $meta_value );
        }
          update_post_meta( $page_id, $meta_key, $meta_value );
      }
	}

	return $page_id;
}

/**
 * Resolve and validate the list of pattern files to import.
 *
 * @param string $pattern_dir   Absolute path to the patterns directory.
 * @param array  $selected_files Optional basenames filter. Empty = all files.
 * @return array Absolute file paths to import.
 * @throws Exception If the directory or files are invalid.
 */
function custom_theme_resolve_pattern_files( $pattern_dir, $selected_files ) {
  if ( ! is_dir( $pattern_dir ) ) {
      throw new Exception( sprintf( 'Page patterns directory not found: %s', esc_html( $pattern_dir ) ) );
  }

  if ( ! is_readable( $pattern_dir ) ) {
      throw new Exception( sprintf( 'Page patterns directory is not readable: %s. Check file permissions.', esc_html( $pattern_dir ) ) );
  }

	$all_files = glob( $pattern_dir . '/*.json' );

  if ( empty( $all_files ) ) {
      throw new Exception( sprintf( 'No page pattern files found in: %s', esc_html( $pattern_dir ) ) );
  }

  if ( empty( $selected_files ) ) {
      return $all_files;
  }

	$filtered = array_values(
      array_filter(
        $all_files,
        function ( $f ) use ( $selected_files ) {
            return in_array( basename( $f ), $selected_files, true );
        }
      )
	);

  if ( empty( $filtered ) ) {
      throw new Exception( esc_html__( 'No matching pattern files found for the selected pages.', 'mbn-theme' ) );
  }

	return $filtered;
}

/**
 * Normalize the page import mode to a supported value.
 *
 * @param string $mode Raw import mode from request/UI.
 * @return string One of: skip_existing, update_existing, create_copy.
 */
function custom_theme_page_sync_normalize_import_mode( $mode ) {
	$normalized = sanitize_key( (string) $mode );
	$allowed    = array( 'skip_existing', 'update_existing', 'create_copy' );

  if ( ! in_array( $normalized, $allowed, true ) ) {
      return 'skip_existing';
  }

	return $normalized;
}

/**
 * Generate a unique page slug for imported copies.
 *
 * @param string $base_slug Base slug from pattern file.
 * @return string Unique slug that does not collide with existing pages.
 */
function custom_theme_page_sync_generate_copy_slug( $base_slug ) {
	$base_slug = sanitize_title( $base_slug );
	$candidate = $base_slug . '-imported-copy';
	$index     = 2;

  while ( get_page_by_path( $candidate, OBJECT, 'page' ) instanceof \WP_Post ) {
      $candidate = $base_slug . '-imported-copy-' . $index;
      ++$index;
  }

	return $candidate;
}

/**
 * Import a single pattern file and return whether the page was created or updated.
 *
 * @param string $file Absolute path to the pattern file.
 * @param string $import_mode Import mode: skip_existing, update_existing, create_copy.
 * @return string 'created', 'updated', 'skipped', or 'copied'.
 * @throws Exception If the file is unreadable, invalid, or import fails.
 */
function custom_theme_import_page_file( $file, $import_mode = 'skip_existing' ) {
  if ( ! is_readable( $file ) ) {
      throw new Exception( sprintf( 'File is not readable: %s', esc_html( basename( $file ) ) ) );
  }

	$raw  = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$data = $raw ? json_decode( $raw, true ) : null;

  if ( null === $data || JSON_ERROR_NONE !== json_last_error() ) {
      throw new Exception( sprintf( 'Invalid JSON in file: %s', esc_html( basename( $file ) ) ) );
  }

	$filename = basename( $file );

	custom_theme_validate_pattern_file_data( $data, $filename );
	$import_mode = custom_theme_page_sync_normalize_import_mode( $import_mode );

	$existing = get_page_by_path( $data['slug'], OBJECT, 'page' );

  if ( $existing instanceof \WP_Post ) {
    if ( 'skip_existing' === $import_mode ) {
        return 'skipped';
    }

    if ( 'create_copy' === $import_mode ) {
        $data['slug']  = custom_theme_page_sync_generate_copy_slug( $data['slug'] );
        $data['title'] = sprintf( '%s (Imported Copy)', $data['title'] );
        custom_theme_import_single_page_from_pattern( $data );
        return 'copied';
    }
  }

	custom_theme_import_single_page_from_pattern( $data );

	return $existing instanceof \WP_Post ? 'updated' : 'created';
}

/**
 * Import page content from pattern files.
 *
 * @param array  $selected_files Optional list of filenames (basename) to import. Empty = import all.
 * @param string $import_mode    Import mode: skip_existing, update_existing, create_copy.
 * @return array Array with created/updated/skipped/copied counts and optional errors.
 * @throws Exception If import fails completely.
 */
function custom_theme_import_pages_from_patterns( $selected_files = array(), $import_mode = 'skip_existing' ) {
	$pattern_dir   = get_theme_file_path( 'template-parts/page-patterns' );
	$pattern_files = custom_theme_resolve_pattern_files( $pattern_dir, $selected_files );
	$import_mode   = custom_theme_page_sync_normalize_import_mode( $import_mode );

	$counts = array(
		'created' => 0,
		'updated' => 0,
		'skipped' => 0,
		'copied'  => 0,
    );
	$errors = array();

    foreach ( $pattern_files as $file ) {
      try {
          ++$counts[ custom_theme_import_page_file( $file, $import_mode ) ];
      } catch ( Exception $e ) {
        $errors[] = basename( $file ) . ': ' . $e->getMessage();
      }
    }

	$result = $counts;

    if ( ! empty( $errors ) ) {
      $result['errors'] = $errors;
      if ( 0 === array_sum( $counts ) ) {
          throw new Exception( 'Import failed: ' . implode( ' | ', array_map( 'esc_html', $errors ) ) );
      }
    }

	return $result;
}

/**
 * One-time bulk repair for invalid _wp_page_template values on pages.
 *
 * Repairs historical bad data (for example, array values imported from pattern files)
 * so core template/body class logic always receives a string.
 *
 * @return void
 */
function custom_theme_bulk_repair_page_template_meta_once() {
	global $wpdb;

	$repair_option_key = 'custom_theme_page_template_meta_bulk_repaired_v1';

  if ( '1' === get_option( $repair_option_key, '0' ) ) {
      return;
  }

	$rows = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT pm.meta_id, pm.post_id, pm.meta_value
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = %s
			AND p.post_type = %s",
        '_wp_page_template',
        'page'
      )
	);

  if ( empty( $rows ) ) {
      update_option( $repair_option_key, '1', false );
      return;
  }

  foreach ( $rows as $row ) {
          $raw_meta_value = maybe_unserialize( $row->meta_value );

    if ( ! is_string( $raw_meta_value ) ) {
            $sanitized_meta_value = custom_theme_sanitize_page_template_meta_value( $raw_meta_value );

            $wpdb->update(
              $wpdb->postmeta,
              array( 'meta_value' => $sanitized_meta_value ),
              array( 'meta_id' => (int) $row->meta_id ),
              array( '%s' ),
              array( '%d' )
            );

            clean_post_cache( (int) $row->post_id );
    }
  }

	update_option( $repair_option_key, '1', false );
}
add_action( 'init', 'custom_theme_bulk_repair_page_template_meta_once', 5 );

/**
 * Handle export pages action.
 *
 * @param array $page_ids Array of page IDs to export.
 * @return array Export results with 'exported' count and 'errors' array.
 * @throws Exception If no pages selected.
 */
function custom_theme_handle_export_pages( $page_ids ) {
  if ( empty( $page_ids ) ) {
      throw new Exception( esc_html__( 'Please select at least one page to export.', 'mbn-theme' ) );
  }

	$exported = 0;
	$errors   = array();

  foreach ( $page_ids as $page_id ) {
    try {
        $file_path = custom_theme_export_page_to_pattern( $page_id );
      if ( $file_path ) {
        ++$exported;
      }
    } catch ( Exception $e ) {
        $errors[] = sprintf( 'Page ID %d: %s', $page_id, $e->getMessage() );
    }
  }

  if ( 0 === $exported ) {
      throw new Exception(
        esc_html__( 'No pages were exported. ', 'mbn-theme' ) .
          ( ! empty( $errors ) ? implode( '; ', array_map( 'esc_html', $errors ) ) : '' )
      );
  }

	return array(
		'exported' => $exported,
		'errors'   => $errors,
	);
}

/**
 * Handle export pages action.
 *
 * @param array $page_ids Array of page IDs to export.
 */
function custom_theme_handle_export_pages_action( $page_ids ) {
  try {
      $result = custom_theme_handle_export_pages( $page_ids );

      $message = sprintf(
          // translators: %d is the number of pages exported.
        __( '%d page(s) exported to template-parts/page-patterns/ successfully!', 'mbn-theme' ),
        $result['exported']
      );

    if ( ! empty( $result['errors'] ) ) {
      $message .= ' ' . __( 'Errors:', 'mbn-theme' ) . ' ' . implode( '; ', $result['errors'] );
    }

      add_settings_error(
        'custom_theme_page_sync',
        'export_success',
        $message,
        empty( $result['errors'] ) ? 'success' : 'warning'
      );
  } catch ( Exception $e ) {
      add_settings_error(
        'custom_theme_page_sync',
        'export_error',
        sprintf(
              // translators: %s is the error message.
          __( 'Export failed: %s', 'mbn-theme' ),
          $e->getMessage()
        ),
        'error'
      );
  }
}

/**
 * Handle import pages action.
 *
 * @param array  $selected_files Optional list of filenames to import. Empty = import all.
 * @param string $import_mode    Import mode: skip_existing, update_existing, create_copy.
 */
function custom_theme_handle_import_pages_action( $selected_files = array(), $import_mode = 'skip_existing' ) {
  try {
			$result = custom_theme_import_pages_from_patterns( $selected_files, $import_mode );

      $message = sprintf(
          // translators: %1$d is pages created, %2$d is pages updated.
        __( 'Import complete! Created: %1$d, Updated: %2$d', 'mbn-theme' ),
        $result['created'],
        $result['updated']
      );

      // Add URL replacement notice
      $current_site   = untrailingslashit( get_site_url() );
      $source_domains = custom_theme_get_source_domains();
      $other_domains  = array_filter(
        $source_domains,
        function ( $domain ) use ( $current_site ) {
            return $domain !== $current_site;
        }
      );

    if ( ! empty( $other_domains ) ) {
        $message .= sprintf(
          ' | %s',
          __( 'URLs automatically updated to current site domain.', 'mbn-theme' )
        );
    }

    if ( isset( $result['errors'] ) && ! empty( $result['errors'] ) ) {
      $message .= ' | ' . __( 'Errors:', 'mbn-theme' ) . ' ' . implode( '; ', $result['errors'] );
    }

    if ( ! empty( $result['copied'] ) ) {
				// translators: %d is number of pages created as imported copies.
			$copied_message = __( 'Copied: %d', 'mbn-theme' );
        $message           .= sprintf(
          ' | ' . $copied_message,
          (int) $result['copied']
        );
    }

    if ( ! empty( $result['skipped'] ) ) {
				// translators: %d is number of existing pages skipped.
			$skipped_message = __( 'Skipped existing: %d', 'mbn-theme' );
        $message            .= sprintf(
          ' | ' . $skipped_message,
          (int) $result['skipped']
        );
    }

      add_settings_error(
        'custom_theme_page_sync',
        'import_success',
        $message,
        ( isset( $result['errors'] ) && ! empty( $result['errors'] ) ) ? 'warning' : 'success'
      );
  } catch ( Exception $e ) {
      add_settings_error(
        'custom_theme_page_sync',
        'import_error',
        sprintf(
              // translators: %s is the error message.
          __( 'Import failed: %s', 'mbn-theme' ),
          $e->getMessage()
        ),
        'error'
      );
  }
}

/**
 * Handle page sync actions.
 *
 * @throws Exception If sync action fails.
 *
 * phpcs:disable Generic.Metrics.CyclomaticComplexity
 */
function custom_theme_handle_page_sync_actions() {
  if ( ! isset( $_POST['custom_theme_page_sync_action'] ) ) {
      return;
  }

  if ( ! current_user_can( 'manage_options' ) ) {
      return;
  }

	check_admin_referer( 'custom_theme_page_sync', 'custom_theme_page_sync_nonce' );

	$action = sanitize_text_field( $_POST['custom_theme_page_sync_action'] );

  if ( 'export_pages' === $action ) {
      $page_ids = isset( $_POST['page_ids'] ) ? array_map( 'intval', (array) $_POST['page_ids'] ) : array();
      custom_theme_handle_export_pages_action( $page_ids );
  } elseif ( 'import_pages' === $action ) {
      $selected_files = isset( $_POST['page_files'] )
          ? array_map( 'sanitize_file_name', (array) $_POST['page_files'] )
          : array();
	  $import_mode    = isset( $_POST['import_mode'] )
		  ? custom_theme_page_sync_normalize_import_mode( sanitize_text_field( wp_unslash( $_POST['import_mode'] ) ) )
		  : 'skip_existing';
	  $sync_password  = isset( $_POST['sync_password'] )
		  ? (string) wp_unslash( $_POST['sync_password'] )
		  : '';

	if ( ! custom_theme_verify_sync_password( $sync_password ) ) {
		$message = '' === custom_theme_get_sync_password()
			? esc_html__( 'Import blocked: sync password is not configured. Define CUSTOM_THEME_SYNC_PASSWORD in wp-config.php or environment.', 'mbn-theme' )
			: esc_html__( 'Import blocked: invalid sync password.', 'mbn-theme' );

		add_settings_error( 'custom_theme_page_sync', 'invalid_sync_password', $message, 'error' );
		return;
	}

    if ( empty( $selected_files ) ) {
        add_settings_error(
          'custom_theme_page_sync',
          'import_no_selection',
          esc_html__( 'Please select at least one page to import.', 'mbn-theme' ),
          'error'
        );
        return;
    }

	custom_theme_handle_import_pages_action( $selected_files, $import_mode );
  } elseif ( 'save_domain_settings' === $action ) {
      $local_url      = isset( $_POST['local_url'] ) ? sanitize_text_field( wp_unslash( $_POST['local_url'] ) ) : '';
      $deployment_url = isset( $_POST['deployment_url'] ) ? sanitize_text_field( wp_unslash( $_POST['deployment_url'] ) ) : '';
      custom_theme_handle_save_domain_settings( $local_url, $deployment_url );
  }
}
add_action( 'admin_init', 'custom_theme_handle_page_sync_actions' );

/**
 * Validate a single sync URL and return an error message or null.
 *
 * @param string $url   URL to validate (already trimmed).
 * @param string $label Human-readable field name for error messages.
 * @return string|null Error message, or null if valid.
 */
function custom_theme_validate_sync_url( $url, $label ) {
  if ( ! preg_match( '/^https?:\/\//i', $url ) ) {
      // translators: %s is the field name (e.g. "Local URL").
      return sprintf( __( '%s must start with http:// or https://', 'mbn-theme' ), $label );
  }
  if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
      // translators: %s is the field name.
      return sprintf( __( '%s is not a valid URL format', 'mbn-theme' ), $label );
  }
	return null;
}

/**
 * Handle saving domain URL settings.
 *
 * @param string $local_url      Local development URL (already sanitized).
 * @param string $deployment_url Deployment server URL (already sanitized).
 * @return void
 */
function custom_theme_handle_save_domain_settings( $local_url, $deployment_url ) {
	$errors = array();

  if ( ! empty( $local_url ) ) {
      $local_url = untrailingslashit( trim( $local_url ) );
      $error     = custom_theme_validate_sync_url( $local_url, __( 'Local URL', 'mbn-theme' ) );
    if ( $error ) {
        $errors[] = $error;
    }
  }

  if ( ! empty( $deployment_url ) ) {
      $deployment_url = untrailingslashit( trim( $deployment_url ) );
      $error          = custom_theme_validate_sync_url( $deployment_url, __( 'Deployment URL', 'mbn-theme' ) );
    if ( $error ) {
        $errors[] = $error;
    }
  }

  if ( ! empty( $errors ) ) {
      add_settings_error( 'custom_theme_page_sync', 'invalid_urls', implode( ' | ', $errors ), 'error' );
      return;
  }

	update_option( 'custom_theme_local_url', $local_url );
	update_option( 'custom_theme_deployment_url', $deployment_url );

  if ( empty( $local_url ) && empty( $deployment_url ) ) {
      add_settings_error( 'custom_theme_page_sync', 'domains_cleared', __( 'Domain URLs cleared. Using default local domain.', 'mbn-theme' ), 'success' );
  } else {
      add_settings_error( 'custom_theme_page_sync', 'domains_saved', __( 'Domain URLs saved successfully!', 'mbn-theme' ), 'success' );
  }
}

/**
 * Render Page Content Sync page.
 *
 * phpcs:disable Generic.Metrics.CyclomaticComplexity,Generic.Metrics.NestingLevel
 */
function custom_theme_render_page_sync_page() {
	$pages = custom_theme_get_syncable_pages();
  ?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Page Content Sync', 'mbn-theme' ); ?></h1>
		
		<?php settings_errors( 'custom_theme_page_sync' ); ?>

		<!-- Domain URL Settings -->
		<div id="domain-settings" class="card" style="max-width: 900px; margin-top: 20px; background: #f0f6fc; border-left: 4px solid #2271b1;">
			<h2><?php esc_html_e( 'Domain URL Settings', 'mbn-theme' ); ?></h2>
			<p><?php esc_html_e( 'Configure source URLs used for domain replacement during import.', 'mbn-theme' ); ?></p>

			<?php
			$local_url      = get_option( 'custom_theme_local_url', '' );
			$deployment_url = get_option( 'custom_theme_deployment_url', '' );
			$is_configured  = ! empty( $local_url ) || ! empty( $deployment_url );
			?>

			<form method="post" style="margin-top: 15px;">
				<?php wp_nonce_field( 'custom_theme_page_sync', 'custom_theme_page_sync_nonce' ); ?>
				<input type="hidden" name="custom_theme_page_sync_action" value="save_domain_settings">

				<div style="background: #fff; padding: 20px; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 15px;">
					
					<!-- Local URL Field -->
					<div style="margin-bottom: 20px;">
						<label for="local-url" style="display: block; font-weight: 600; margin-bottom: 8px;">
							🏠 Local Development URL
						</label>
						<input 
							type="url" 
							id="local-url" 
							name="local_url" 
							value="<?php echo esc_attr( $local_url ); ?>"
							placeholder="https://hastingsandhastings.dev.local"
							style="width: 100%; max-width: 500px; padding: 8px 12px; font-size: 14px; border: 1px solid #8c8f94; border-radius: 4px;"
						>
					</div>

					<!-- Deployment URL Field -->
					<div>
						<label for="deployment-url" style="display: block; font-weight: 600; margin-bottom: 8px;">
							🌐 Deployment Server URL
						</label>
						<input 
							type="url" 
							id="deployment-url" 
							name="deployment_url" 
							value="<?php echo esc_attr( $deployment_url ); ?>"
							placeholder="https://newsite-staging.hastingsandhastings.com"
							style="width: 100%; max-width: 500px; padding: 8px 12px; font-size: 14px; border: 1px solid #8c8f94; border-radius: 4px;"
						>
					</div>
				</div>

				<div style="display: flex; gap: 10px; align-items: center;">
					<button type="submit" class="button button-primary">
						💾 Save Settings
					</button>
					<?php if ( $is_configured ) : ?>
						<button 
							type="button" 
							class="button button-secondary" 
							onclick="if(confirm('Clear domain settings?')) { document.getElementById('local-url').value=''; document.getElementById('deployment-url').value=''; this.form.submit(); }"
						>
							🗑️ Clear Settings
						</button>
					<?php endif; ?>
				</div>
			</form>
			<p style="margin-top: 12px; color: #50575e;">
				<?php esc_html_e( 'Current site URL:', 'mbn-theme' ); ?> <code><?php echo esc_html( get_site_url() ); ?></code>
			</p>
		</div>

		<div class="card" style="max-width: 900px; margin-top: 20px;">
			<h2>📤 Export Pages to Files</h2>
			<p><?php esc_html_e( 'Select pages to export to template-parts/page-patterns/*.json.', 'mbn-theme' ); ?></p>
			
			<?php if ( empty( $pages ) ) : ?>
				<p><em>No published pages found.</em></p>
			<?php else : ?>
				<form method="post" style="margin-top: 20px;">
					<?php wp_nonce_field( 'custom_theme_page_sync', 'custom_theme_page_sync_nonce' ); ?>
					<input type="hidden" name="custom_theme_page_sync_action" value="export_pages">
					
					<table class="widefat" style="margin-bottom: 20px;">
						<thead>
							<tr>
								<th style="width: 40px;">
									<input type="checkbox" id="select-all-pages" onclick="
										var checkboxes = document.querySelectorAll('input[name=\'page_ids[]\']');
										checkboxes.forEach(function(cb) { cb.checked = this.checked; }.bind(this));
									">
								</th>
								<th>Page Title</th>
								<th>Slug</th>
								<th>Status</th>
								<th>Featured Image</th>
								<th>Last Modified</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $pages as $page ) : ?>
								<?php
								$has_thumbnail = has_post_thumbnail( $page->ID );
								$page_template = get_page_template_slug( $page->ID );
								$template_slug = '';
								if ( is_string( $page_template ) ) {
									$template_slug = $page_template;
								} elseif ( is_array( $page_template ) ) {
									$first_template = reset( $page_template );
                                  if ( is_string( $first_template ) ) {
                                      $template_slug = $first_template;
                                  }
								}
								?>
								<tr>
									<td>
										<input type="checkbox" name="page_ids[]" value="<?php echo esc_attr( $page->ID ); ?>">
									</td>
									<td>
										<strong><?php echo esc_html( $page->post_title ); ?></strong>
										<?php if ( $page->post_parent > 0 ) : ?>
											<br><small style="color: #666;">↳ Child of: <?php echo esc_html( get_the_title( $page->post_parent ) ); ?></small>
										<?php endif; ?>
										<?php if ( ! empty( $template_slug ) && 'default' !== $template_slug ) : ?>
											<br><small style="color: #2271b1;">📄 Template: <?php echo esc_html( basename( $template_slug, '.php' ) ); ?></small>
										<?php endif; ?>
										<div class="row-actions">
											<a href="<?php echo esc_url( get_edit_post_link( $page->ID ) ); ?>" target="_blank">Edit</a> |
											<a href="<?php echo esc_url( get_permalink( $page->ID ) ); ?>" target="_blank">View</a>
										</div>
									</td>
									<td><code><?php echo esc_html( $page->post_name ); ?></code></td>
									<td>
										<span class="status-<?php echo esc_attr( $page->post_status ); ?>">
											<?php echo esc_html( ucfirst( $page->post_status ) ); ?>
										</span>
									</td>
									<td style="text-align: center;">
										<?php if ( $has_thumbnail ) : ?>
											<span style="color: #46b450;" title="Has featured image">✓</span>
										<?php else : ?>
											<span style="color: #ddd;">—</span>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html( get_the_modified_date( 'Y-m-d H:i', $page->ID ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					
					<button type="submit" class="button button-primary">
						📤 Export Selected Pages to Files
					</button>
				</form>
			<?php endif; ?>
		</div>

		<div class="card" style="max-width: 900px; margin-top: 20px;">
			<h2>📥 Import Pages from Files</h2>
			<p>Select pages to import from <code>template-parts/page-patterns/*.json</code> files. Import Mode controls whether existing slugs are skipped, updated, or copied.</p>
			<p style="color: #50575e;"><strong><?php esc_html_e( 'Note:', 'mbn-theme' ); ?></strong> <?php esc_html_e( 'Overwrite happens only in Update existing mode.', 'mbn-theme' ); ?></p>

			<?php $importable_files = custom_theme_get_importable_page_files(); ?>
			<?php if ( empty( $importable_files ) ) : ?>
				<p><em>No page pattern files found in <code>template-parts/page-patterns/</code>. Export pages first.</em></p>
			<?php else : ?>
				<form method="post" style="margin-top: 20px;">
					<?php wp_nonce_field( 'custom_theme_page_sync', 'custom_theme_page_sync_nonce' ); ?>
					<input type="hidden" name="custom_theme_page_sync_action" value="import_pages">
					<div style="background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;padding:12px 14px;margin:0 0 15px 0;">
						<label for="page-import-mode" style="display:block;font-weight:600;margin-bottom:6px;">
							<?php esc_html_e( 'Import Mode', 'mbn-theme' ); ?>
						</label>
						<select id="page-import-mode" name="import_mode" style="min-width:280px;">
							<option value="skip_existing" selected><?php esc_html_e( 'Skip existing pages (safe)', 'mbn-theme' ); ?></option>
							<option value="update_existing"><?php esc_html_e( 'Update existing pages', 'mbn-theme' ); ?></option>
							<option value="create_copy"><?php esc_html_e( 'Create imported copies for existing pages', 'mbn-theme' ); ?></option>
						</select>
						<p style="margin:6px 0 0;color:#646970;">
							<?php esc_html_e( 'Choose how to handle files whose slug already exists in the database.', 'mbn-theme' ); ?>
						</p>
						<?php if ( custom_theme_is_sync_password_required() ) : ?>
							<label for="page-sync-password" style="display:block;font-weight:600;margin:10px 0 6px;">
								<?php esc_html_e( 'Sync Password', 'mbn-theme' ); ?>
							</label>
							<input type="password" id="page-sync-password" name="sync_password" autocomplete="current-password" style="min-width:280px;" required>
							<p style="margin:6px 0 0;color:#646970;">
								<?php esc_html_e( 'Required on production before import runs.', 'mbn-theme' ); ?>
							</p>
						<?php endif; ?>
					</div>

					<table class="widefat" style="margin-bottom: 15px;">
						<thead>
							<tr>
								<th style="width: 40px;">
									<input type="checkbox" id="select-all-import-pages" title="Select all">
								</th>
								<th>Page Title</th>
								<th>Slug</th>
								<th>Status</th>
								<th>File</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $importable_files as $file_info ) : ?>
								<?php $db_page = get_page_by_path( $file_info['slug'], OBJECT, 'page' ); ?>
								<tr>
									<td>
										<input type="checkbox" name="page_files[]" value="<?php echo esc_attr( $file_info['filename'] ); ?>" checked>
									</td>
									<td><strong><?php echo esc_html( $file_info['title'] ); ?></strong></td>
									<td><code><?php echo esc_html( $file_info['slug'] ); ?></code></td>
									<td><?php echo esc_html( ucfirst( $file_info['status'] ) ); ?></td>
									<td><code><?php echo esc_html( $file_info['filename'] ); ?></code></td>
									<td>
										<?php if ( $db_page instanceof \WP_Post ) : ?>
											<span style="color: #f0b849;">&#8635; Will Update</span>
										<?php else : ?>
											<span style="color: #46b450;">+ Will Create</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<script>
					document.getElementById( 'select-all-import-pages' ).addEventListener( 'change', function () {
						document.querySelectorAll( 'input[name="page_files[]"]' ).forEach( function ( cb ) {
							cb.checked = this.checked;
						}, this );
					} );
					</script>

					<button type="submit" class="button button-secondary">
						📥 Import Selected Pages from Files
					</button>
				</form>
			<?php endif; ?>
		</div>

	</div>
	<?php
}
