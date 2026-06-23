<?php
/**
 * Custom post type: Block Templates (reusable layouts; Header/Footer Template posts drive site chrome via the editor).
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Slug for the global Header Template Block Template post.
 *
 * @return string
 */
function custom_theme_header_template_slug(): string {
  return 'header-template';
}

/**
 * Slug for the global Footer Template Block Template post.
 *
 * @return string
 */
function custom_theme_footer_template_slug(): string {
  return 'footer-template';
}

/**
 * Register the Block Templates post type.
 *
 * @return void
 */
function custom_theme_register_block_template_post_type(): void {
  $labels = array(
	  'name'               => __( 'Block Templates', 'mbn-theme' ),
	  'singular_name'      => __( 'Block Template', 'mbn-theme' ),
	  'add_new'            => __( 'Add New', 'mbn-theme' ),
	  'add_new_item'       => __( 'Add New Block Template', 'mbn-theme' ),
	  'edit_item'          => __( 'Edit Block Template', 'mbn-theme' ),
	  'new_item'           => __( 'New Block Template', 'mbn-theme' ),
	  'view_item'          => __( 'View Block Template', 'mbn-theme' ),
	  'search_items'       => __( 'Search Block Templates', 'mbn-theme' ),
	  'not_found'          => __( 'No block templates found.', 'mbn-theme' ),
	  'not_found_in_trash' => __( 'No block templates found in Trash.', 'mbn-theme' ),
	  'all_items'          => __( 'Block Templates', 'mbn-theme' ),
  );

  register_post_type(
    'mbn_block_template',
    array(
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'query_var'           => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'has_archive'         => false,
		'hierarchical'        => false,
		'show_in_rest'        => true,
		'menu_position'       => 21,
		'menu_icon'           => 'dashicons-layout',
		'supports'            => array( 'title', 'editor', 'revisions' ),
    )
  );
}
add_action( 'init', 'custom_theme_register_block_template_post_type', 5 );

/**
 * Resolve a Block Template post ID by slug.
 *
 * @param string $slug Post name slug.
 * @return int Post ID or 0.
 */
function custom_theme_get_block_template_id_by_slug( string $slug ): int {
  if ( '' === $slug ) {
    return 0;
  }

  $post = get_page_by_path( $slug, OBJECT, 'mbn_block_template' );
  if ( $post instanceof \WP_Post ) {
    return (int) $post->ID;
  }

  return 0;
}

/**
 * Resolve a Block Template post ID by slug (any status, including draft and trash).
 *
 * Used to avoid duplicate auto-created templates when a matching post already exists.
 *
 * @param string $slug Post name slug.
 * @return int Post ID or 0.
 */
function custom_theme_get_block_template_id_by_slug_any_status( string $slug ): int {
  $slug = sanitize_title( $slug );
  if ( '' === $slug ) {
    return 0;
  }

  $posts = get_posts(
    array(
		'post_type'              => 'mbn_block_template',
		'name'                   => $slug,
		'post_status'            => 'any',
		'posts_per_page'         => 1,
		'fields'                 => 'ids',
		'suppress_filters'       => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'no_found_rows'          => true,
    )
  );

  if ( empty( $posts ) ) {
    return 0;
  }

  return (int) $posts[0];
}

/**
 * Post IDs to omit from the Template block association picker (global chrome + page template layouts).
 *
 * @return array<int, int>
 */
function custom_theme_get_block_template_post_ids_excluded_from_template_block(): array {
  $slugs = array(
	  custom_theme_header_template_slug(),
	  custom_theme_footer_template_slug(),
  );

  if ( function_exists( 'custom_theme_get_layout_template_file_slugs' ) ) {
    $slugs = array_merge( $slugs, custom_theme_get_layout_template_file_slugs() );
  }

  $slugs[] = 'blank';
  $slugs[] = 'sidebar';
  $slugs[] = 'blank-template';
  $slugs[] = 'sidebar-template';

  $slugs = array_unique( array_filter( array_map( 'sanitize_title', $slugs ) ) );
  $ids   = array();

  foreach ( $slugs as $slug ) {
    $id = custom_theme_get_block_template_id_by_slug_any_status( $slug );
    if ( $id > 0 ) {
      $ids[] = $id;
    }
  }

  return array_values( array_unique( $ids ) );
}

/**
 * Load Block Template content from a template-parts PHP file.
 *
 * @param string $template_slug Template slug (e.g., 'header-template' or 'footer-template').
 * @return string Block markup content.
 * @throws Exception If file cannot be loaded.
 */
function custom_theme_load_template_content_from_file( string $template_slug ): string {
  $file_path = get_theme_file_path( 'template-parts/' . $template_slug . '.php' );

  if ( ! file_exists( $file_path ) ) {
    throw new Exception( sprintf( 'Template file not found: %s', esc_html( $file_path ) ) );
  }

  if ( ! is_readable( $file_path ) ) {
    throw new Exception( sprintf( 'Template file is not readable: %s. Check file permissions.', esc_html( $file_path ) ) );
  }

  // Extract rendered content using output buffering.
  ob_start();
  // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_include
  include $file_path;
  $content = ob_get_clean();
  $content = trim( $content );

  return $content;
}

/**
 * Import or update a single block template from file.
 *
 * @param string $slug Template slug.
 * @param string $title Template title.
 * @param string $file_name Template file name (without .php).
 * @param bool   $force Force update if template exists.
 * @return bool True if imported/updated, false if skipped.
 * @throws Exception If import fails.
 */
function custom_theme_import_single_template( string $slug, string $title, string $file_name, bool $force = false ): bool {
  $template_id = custom_theme_get_block_template_id_by_slug( $slug );
  $content     = custom_theme_load_template_content_from_file( $file_name );

  if ( 0 === $template_id ) {
    // Create new template
    $result = wp_insert_post(
      array(
		  'post_type'    => 'mbn_block_template',
		  'post_title'   => $title,
		  'post_name'    => $slug,
		  'post_status'  => 'publish',
		  'post_content' => $content,
      ),
      true
    );

    if ( is_wp_error( $result ) ) {
      throw new Exception( sprintf( 'Failed to create %s: %s', esc_html( $title ), esc_html( $result->get_error_message() ) ) );
    }

    return true;
  }

  if ( $force && '' !== $content ) {
    // Update existing template
    $result = wp_update_post(
      array(
		  'ID'           => $template_id,
		  'post_content' => $content,
      )
    );

    if ( is_wp_error( $result ) ) {
      throw new Exception( sprintf( 'Failed to update %s: %s', esc_html( $title ), esc_html( $result->get_error_message() ) ) );
    }

    return true;
  }

  return false;
}

/**
 * Create default Header Template and Footer Template posts from template-parts files.
 * Run once on theme activation, or manually trigger with 'Sync Templates' button.
 *
 * @param bool $force Force re-sync even if already seeded.
 * @return void
 * @throws Exception If import fails.
 */
function custom_theme_maybe_seed_default_block_templates( bool $force = false ): void {
  if ( ! $force && '1' === get_option( 'custom_theme_block_defaults_seeded', '' ) ) {
    return;
  }

  if ( ! post_type_exists( 'mbn_block_template' ) ) {
    throw new Exception( 'Block Template post type is not registered.' );
  }

  $errors   = array();
  $imported = 0;

  // Import Header Template
  try {
    if ( custom_theme_import_single_template(
      custom_theme_header_template_slug(),
      __( 'Header Template', 'mbn-theme' ),
      'header-template',
      $force
    ) ) {
      ++$imported;
    }
  } catch ( Exception $e ) {
    $errors[] = 'Header Template: ' . $e->getMessage();
  }

  // Import Footer Template
  try {
    if ( custom_theme_import_single_template(
      custom_theme_footer_template_slug(),
      __( 'Footer Template', 'mbn-theme' ),
      'footer-template',
      $force
    ) ) {
      ++$imported;
    }
  } catch ( Exception $e ) {
    $errors[] = 'Footer Template: ' . $e->getMessage();
  }

  // Report errors if any
  if ( ! empty( $errors ) ) {
    $error_message = implode( ' | ', array_map( 'esc_html', $errors ) );
    if ( 0 === $imported ) {
      // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
      throw new Exception( $error_message );
    }
    // If some succeeded, throw a warning-level exception
    // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
    throw new Exception( 'Partial import: ' . $error_message );
  }

  update_option( 'custom_theme_block_defaults_seeded', '1' );
}
add_action( 'init', 'custom_theme_maybe_seed_default_block_templates', 20 );

/**
 * Global site header HTML from the Header Template Block Template post (block editor content).
 *
 * @return string HTML fragment for inside theme header (run through the_content filters).
 */
function custom_theme_get_global_header_template_output_html(): string {
  $post_id = custom_theme_get_block_template_id_by_slug( custom_theme_header_template_slug() );

  if ( $post_id <= 0 ) {
    // Debug: Template not found
    if ( current_user_can( 'edit_posts' ) && WP_DEBUG ) {
      return '<!-- Header Template post not found (slug: ' . custom_theme_header_template_slug() . ') -->';
    }
    return '';
  }

  $post = get_post( $post_id );
  if ( ! $post instanceof \WP_Post ) {
    return '';
  }

  if ( 'publish' !== $post->post_status ) {
    // Debug: Template not published
    if ( current_user_can( 'edit_posts' ) && WP_DEBUG ) {
      return '<!-- Header Template exists but is not published (status: ' . $post->post_status . ') -->';
    }
    return '';
  }

  $content = $post->post_content;

  // Parse blocks and render them
  if ( has_blocks( $content ) ) {
    $html = do_blocks( $content );
  } else {
    $html = apply_filters( 'the_content', $content );
  }

  // Debug: Empty content
  if ( '' === trim( wp_strip_all_tags( $html ) ) && current_user_can( 'edit_posts' ) && WP_DEBUG ) {
    return '<!-- Header Template is published but has no visible content. Edit it at: ' . get_edit_post_link( $post_id ) . ' -->';
  }

  return is_string( $html ) ? $html : '';
}

/**
 * Global site footer HTML from the Footer Template Block Template post (block editor content).
 *
 * @return string HTML fragment for inside theme footer (run through the_content filters).
 */
function custom_theme_get_global_footer_template_output_html(): string {
  $post_id = custom_theme_get_block_template_id_by_slug( custom_theme_footer_template_slug() );

  if ( $post_id <= 0 ) {
    // Debug: Template not found
    if ( current_user_can( 'edit_posts' ) && WP_DEBUG ) {
      return '<!-- Footer Template post not found (slug: ' . custom_theme_footer_template_slug() . ') -->';
    }
    return '';
  }

  $post = get_post( $post_id );
  if ( ! $post instanceof \WP_Post ) {
    return '';
  }

  if ( 'publish' !== $post->post_status ) {
    // Debug: Template not published
    // Debug: Template not published
    if ( current_user_can( 'edit_posts' ) && WP_DEBUG ) {
      return '<!-- Footer Template exists but is not published (status: ' . $post->post_status . ') -->';
    }
    return '';
  }

  $content = $post->post_content;

  // Parse blocks and render them
  if ( has_blocks( $content ) ) {
    $html = do_blocks( $content );
  } else {
    $html = apply_filters( 'the_content', $content );
  }

  // Debug: Empty content
  if ( '' === trim( wp_strip_all_tags( $html ) ) ) {
    // Log: Empty footer
    if ( current_user_can( 'edit_posts' ) && WP_DEBUG ) {
      return '<!-- Footer Template is published but has no visible content. Edit it at: ' . get_edit_post_link( $post_id ) . ' -->';
    }
  }

  return is_string( $html ) ? $html : '';
}

/**
 * Admin list table: add Badges column for Block Templates.
 *
 * @param array<string, string> $columns Columns.
 * @return array<string, string>
 */
function custom_theme_block_template_posts_columns( array $columns ): array {
  $out = array();
  foreach ( $columns as $id => $label ) {
    $out[ $id ] = $label;
    if ( 'title' === $id ) {
      $out['custom_theme_badges'] = __( 'Badges', 'mbn-theme' );
    }
  }

  return $out;
}

/**
 * Admin list table: output badge markup for Block Templates.
 *
 * @param string $column Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function custom_theme_block_template_posts_custom_column( string $column, int $post_id ): void {
  if ( 'custom_theme_badges' !== $column ) {
    return;
  }

  $post = get_post( $post_id );
  if ( ! $post instanceof \WP_Post || 'mbn_block_template' !== $post->post_type ) {
    return;
  }

  $slug = (string) $post->post_name;

  if ( custom_theme_header_template_slug() === $slug ) {
    echo '<span class="carbon-template-badge carbon-template-badge--global">' . esc_html__( 'Global', 'mbn-theme' ) . '</span> ';
    echo '<span class="carbon-template-badge carbon-template-badge--chrome">' . esc_html__( 'Header', 'mbn-theme' ) . '</span>';
    return;
  }

  if ( custom_theme_footer_template_slug() === $slug ) {
    echo '<span class="carbon-template-badge carbon-template-badge--global">' . esc_html__( 'Global', 'mbn-theme' ) . '</span> ';
    echo '<span class="carbon-template-badge carbon-template-badge--chrome">' . esc_html__( 'Footer', 'mbn-theme' ) . '</span>';
    return;
  }

  if ( custom_theme_block_slug_is_page_template_layout( $slug ) ) {
    echo '<span class="carbon-template-badge carbon-template-badge--page">' . esc_html__( 'Page Templates', 'mbn-theme' ) . '</span>';
    return;
  }

  echo '&mdash;';
}

/**
 * Admin: styles for Block Template list badges.
 *
 * @return void
 */
function custom_theme_block_template_admin_list_styles(): void {
  $screen = get_current_screen();
  if ( ! $screen || 'edit-mbn_block_template' !== $screen->id ) {
    return;
  }

  echo '<style>
    .column-custom_theme_badges { width: 14em; }
    .carbon-template-badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 3px;
      font-size: 11px;
      font-weight: 600;
      line-height: 1.6;
      vertical-align: middle;
    }
    .carbon-template-badge--global {
      background: #dcdcde;
      color: #1d2327;
    }
    .carbon-template-badge--chrome {
      background: #f0f0f1;
      color: #50575e;
    }
    .carbon-template-badge--page {
      background: #d6f0e8;
      color: #0a4a3a;
    }
  </style>';
}

add_filter( 'manage_mbn_block_template_posts_columns', 'custom_theme_block_template_posts_columns' );
add_action( 'manage_mbn_block_template_posts_custom_column', 'custom_theme_block_template_posts_custom_column', 10, 2 );
add_action( 'admin_head', 'custom_theme_block_template_admin_list_styles' );

/**
 * Whether this Block Template is global chrome or a theme page layout (must not be moved to Trash from published state).
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function custom_theme_block_template_post_is_protected_from_trash( int $post_id ): bool {
  $post = get_post( $post_id );
  if ( ! $post instanceof \WP_Post || 'mbn_block_template' !== $post->post_type ) {
    return false;
  }

  $slug = (string) $post->post_name;

  if ( custom_theme_header_template_slug() === $slug ) {
    return true;
  }

  if ( custom_theme_footer_template_slug() === $slug ) {
    return true;
  }

  if ( function_exists( 'custom_theme_block_slug_is_page_template_layout' ) && custom_theme_block_slug_is_page_template_layout( $slug ) ) {
    return true;
  }

  return false;
}

/**
 * Block trashing protected Block Templates; allow permanent delete only when already in trash.
 *
 * @param array<int, string> $caps    Primitive caps for the user.
 * @param string             $cap     Capability name.
 * @param int                $user_id User ID.
 * @param array<int, mixed>  $args    Arguments (post ID for delete_post).
 * @return array<int, string>
 */
function custom_theme_block_template_map_meta_cap_protect_trash( array $caps, string $cap, int $user_id, array $args ): array {
  if ( 'delete_post' !== $cap || empty( $args[0] ) ) {
    return $caps;
  }

  $post_id = (int) $args[0];
  if ( ! custom_theme_block_template_post_is_protected_from_trash( $post_id ) ) {
    return $caps;
  }

  $post = get_post( $post_id );
  if ( ! $post instanceof \WP_Post ) {
    return $caps;
  }

  if ( 'trash' === $post->post_status ) {
    return $caps;
  }

  return array( 'do_not_allow' );
}

/**
 * Remove Trash row action for protected Block Templates (not already in trash).
 *
 * @param array<string, string> $actions Row actions.
 * @param \WP_Post              $post   Post object.
 * @return array<string, string>
 */
function custom_theme_block_template_row_actions_remove_trash( array $actions, \WP_Post $post ): array {
  if ( 'mbn_block_template' !== $post->post_type ) {
    return $actions;
  }

  if ( 'trash' === $post->post_status ) {
    return $actions;
  }

  if ( ! custom_theme_block_template_post_is_protected_from_trash( $post->ID ) ) {
    return $actions;
  }

  unset( $actions['trash'] );

  return $actions;
}

add_filter( 'map_meta_cap', 'custom_theme_block_template_map_meta_cap_protect_trash', 10, 4 );
add_filter( 'post_row_actions', 'custom_theme_block_template_row_actions_remove_trash', 10, 2 );

/**
 * Exclude block templates from the WordPress core sitemap.
 *
 * @param array<string, WP_Post_Type> $post_types Post types included in the sitemap.
 * @return array<string, WP_Post_Type>
 */
function custom_theme_block_template_exclude_from_sitemap( $post_types ) {
  unset( $post_types['mbn_block_template'] );

  return $post_types;
}

/**
 * Prevent block template singles from being indexed by search engines.
 *
 * @param array<string, bool|string> $robots Robots directives.
 * @return array<string, bool|string>
 */
function custom_theme_block_template_robots_noindex( $robots ) {
  if ( is_singular( 'mbn_block_template' ) ) {
    $robots['noindex']   = true;
    $robots['nofollow']  = true;
    $robots['noarchive'] = true;
  }

  return $robots;
}

/**
 * Block anonymous front-end access to block template singles.
 */
function custom_theme_block_template_block_public_view() {
  if ( ! is_singular( 'mbn_block_template' ) ) {
    return;
  }

  if ( is_preview() && current_user_can( 'edit_posts' ) ) {
    return;
  }

  if ( current_user_can( 'edit_posts' ) ) {
    return;
  }

  global $wp_query;

  $wp_query->set_404();
  status_header( 404 );
  nocache_headers();
}

/**
 * Exclude block templates from Yoast SEO sitemaps when the plugin is active.
 *
 * @param bool   $excluded   Whether the post type is excluded.
 * @param string $post_type  Post type slug.
 * @return bool
 */
function custom_theme_block_template_yoast_exclude_sitemap( $excluded, $post_type ) {
  if ( 'mbn_block_template' === $post_type ) {
    return true;
  }

  return $excluded;
}

/**
 * Exclude block templates from Rank Math sitemaps when the plugin is active.
 *
 * @param bool   $exclude   Whether the post type is excluded.
 * @param string $post_type Post type slug.
 * @return bool
 */
function custom_theme_block_template_rank_math_exclude_sitemap( $exclude, $post_type ) {
  if ( 'mbn_block_template' === $post_type ) {
    return true;
  }

  return $exclude;
}

add_filter( 'wp_sitemaps_post_types', 'custom_theme_block_template_exclude_from_sitemap' );
add_filter( 'wp_robots', 'custom_theme_block_template_robots_noindex' );
add_action( 'template_redirect', 'custom_theme_block_template_block_public_view' );
add_filter( 'wpseo_sitemap_exclude_post_type', 'custom_theme_block_template_yoast_exclude_sitemap', 10, 2 );
add_filter( 'rank_math/sitemap/exclude_post_type', 'custom_theme_block_template_rank_math_exclude_sitemap', 10, 2 );
