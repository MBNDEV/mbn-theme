<?php
/**
 * Template Sync Tools - Import/Export Block Templates.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Template Tools submenu page.
 */
function custom_theme_add_template_tools_page() {
	add_submenu_page(
      'edit.php?post_type=mbn_block_template',
      __( 'Template Sync Tools', 'mbn-theme' ),
      __( 'Sync Tools', 'mbn-theme' ),
      'manage_options',
      'template-sync-tools',
      'custom_theme_render_template_tools_page'
	);
}
add_action( 'admin_menu', 'custom_theme_add_template_tools_page' );

/**
 * Get all templates that can be imported or exported, keyed by slug.
 *
 * Each entry contains: title, type (system|layout), source_dir, source_file, export_dir, export_file.
 *
 * @return array
 */
function custom_theme_get_all_syncable_templates() {
	$header_slug = custom_theme_header_template_slug();
	$footer_slug = custom_theme_footer_template_slug();

	$templates = array(
		$header_slug => array(
			'title'       => 'Header Template',
			'type'        => 'system',
			'source_dir'  => 'template-parts',
			'source_file' => 'header-template',
			'export_dir'  => 'template-parts',
			'export_file' => 'header-template',
		),
		$footer_slug => array(
			'title'       => 'Footer Template',
			'type'        => 'system',
			'source_dir'  => 'template-parts',
			'source_file' => 'footer-template',
			'export_dir'  => 'template-parts',
			'export_file' => 'footer-template',
		),
	);

	foreach ( custom_theme_get_layout_template_file_slugs() as $slug ) {
		$layout_name        = preg_replace( '/^template-/', '', $slug );
		$templates[ $slug ] = array(
			'title'       => custom_theme_layout_template_title_from_slug( $slug ),
			'type'        => 'layout',
			'source_dir'  => 'page-templates',
			'source_file' => $slug,
			'export_dir'  => 'template-parts/layouts',
			'export_file' => $layout_name,
		);
	}

	return $templates;
}

/**
 * Normalize template import mode to a supported value.
 *
 * @param string $mode Raw mode from request/UI.
 * @return string One of: skip_existing, update_existing, create_copy.
 */
function custom_theme_template_sync_normalize_import_mode( $mode ) {
	$normalized = sanitize_key( (string) $mode );
	$allowed    = array( 'skip_existing', 'update_existing', 'create_copy' );

  if ( ! in_array( $normalized, $allowed, true ) ) {
      return 'skip_existing';
  }

	return $normalized;
}

/**
 * Generate a unique template post slug for imported copies.
 *
 * @param string $base_slug Base template slug.
 * @return string Unique post_name for mbn_block_template.
 */
function custom_theme_template_sync_generate_copy_slug( $base_slug ) {
	$base_slug = sanitize_title( $base_slug );
	$candidate = $base_slug . '-imported-copy';
	$index     = 2;

  while ( custom_theme_get_block_template_id_by_slug( $candidate ) > 0 ) {
      $candidate = $base_slug . '-imported-copy-' . $index;
      ++$index;
  }

	return $candidate;
}

/**
 * Import a single template by slug using its source file.
 *
 * @param string $slug        Template slug.
 * @param array  $info        Template info from custom_theme_get_all_syncable_templates().
 * @param string $import_mode Import mode: skip_existing, update_existing, create_copy.
 * @return string One of: created, updated, skipped, copied, missing_file.
 * @throws Exception If database operation fails.
 */
function custom_theme_import_template_for_slug( $slug, $info, $import_mode = 'skip_existing' ) {
	$file_path   = get_theme_file_path( $info['source_dir'] . '/' . $info['source_file'] . '.php' );
	$import_mode = custom_theme_template_sync_normalize_import_mode( $import_mode );

  if ( ! file_exists( $file_path ) ) {
			return 'missing_file';
  }

	// Read raw file and strip the PHP preamble (ABSPATH guard + docblock) to extract
	// only the block markup. Using file_get_contents avoids executing potentially
	// broken PHP and prevents Fatal Errors from crashing the Sync admin page.
	$_raw    = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$content = ( false !== $_raw ) ? trim( (string) preg_replace( '/^.*?\?>\s*/s', '', $_raw ) ) : '';

  if ( '' === $content ) {
			return 'missing_file';
  }

	$post_id = custom_theme_get_block_template_id_by_slug( $slug );
	$exists  = $post_id > 0;

  if ( $exists && 'skip_existing' === $import_mode ) {
          return 'skipped';
  }

  if ( $exists && 'create_copy' === $import_mode ) {
          $copy_slug = custom_theme_template_sync_generate_copy_slug( $slug );
          $result    = wp_insert_post(
            array(
				'post_type'    => 'mbn_block_template',
				'post_title'   => sprintf( '%s (Imported Copy)', $info['title'] ),
				'post_name'    => $copy_slug,
				'post_status'  => 'publish',
				'post_content' => $content,
            ),
            true
          );

    if ( is_wp_error( $result ) ) {
          throw new Exception( esc_html( $result->get_error_message() ) );
    }

          return 'copied';
  }

  if ( ! $exists ) {
    $result = wp_insert_post(
      array(
          'post_type'    => 'mbn_block_template',
          'post_title'   => $info['title'],
          'post_name'    => $slug,
          'post_status'  => 'publish',
          'post_content' => $content,
      ),
      true
    );

      $action = 'created';
  } else {
      $result = wp_update_post(
        array(
			'ID'           => $post_id,
			'post_content' => $content,
		)
      );

		$action = 'updated';
  }

  if ( is_wp_error( $result ) ) {
      throw new Exception( esc_html( $result->get_error_message() ) );
  }

	return $action;
}

/**
 * Import a single template file.
 *
 * @param string $slug Template slug.
 * @return bool True if imported successfully, false otherwise.
 * @throws Exception If import fails.
 */
function custom_theme_import_single_template_file( $slug ) {
	$file_path = get_theme_file_path( 'page-templates/' . $slug . '.php' );
  if ( ! file_exists( $file_path ) ) {
      return false;
  }

	// Read raw file and strip the PHP preamble (ABSPATH guard + docblock) to extract
	// only the block markup. Using file_get_contents avoids executing potentially
	// broken PHP and prevents Fatal Errors from crashing the Sync admin page.
	$_raw    = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$content = ( false !== $_raw ) ? trim( (string) preg_replace( '/^.*?\?>\s*/s', '', $_raw ) ) : '';

  if ( '' === $content ) {
      return false;
  }

	// Get or create Block Template post
	$post_id = custom_theme_get_block_template_id_by_slug( $slug );

  if ( 0 === $post_id ) {
      // Create new post
      $title      = custom_theme_layout_template_title_from_slug( $slug );
      $created_id = wp_insert_post(
        array(
			'post_type'    => 'mbn_block_template',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_content' => $content,
		),
        true
      );

    if ( is_wp_error( $created_id ) ) {
      throw new Exception( esc_html( $created_id->get_error_message() ) );
    }
  } else {
      // Update existing post
      $updated = wp_update_post(
        array(
			'ID'           => $post_id,
			'post_content' => $content,
		)
      );

    if ( is_wp_error( $updated ) ) {
        throw new Exception( esc_html( $updated->get_error_message() ) );
    }
  }

	return true;
}

/**
 * Import templates from files. Optionally limit to a set of slugs.
 *
 * @param array  $selected_slugs Slugs to import. Empty = import all.
 * @param string $import_mode    Import mode: skip_existing, update_existing, create_copy.
 * @throws Exception If no templates were imported and all failed.
 */
function custom_theme_import_all_templates_from_files( $selected_slugs = array(), $import_mode = 'skip_existing' ) {
	$counts      = array(
		'created' => 0,
		'updated' => 0,
		'skipped' => 0,
		'copied'  => 0,
	);
	$errors      = array();
	$templates   = custom_theme_get_all_syncable_templates();
	$import_mode = custom_theme_template_sync_normalize_import_mode( $import_mode );

    if ( ! empty( $selected_slugs ) ) {
      $templates = array_intersect_key( $templates, array_flip( $selected_slugs ) );
    }

    foreach ( $templates as $slug => $info ) {
      try {
			$action = custom_theme_import_template_for_slug( $slug, $info, $import_mode );

        if ( isset( $counts[ $action ] ) ) {
            ++$counts[ $action ];
        }
      } catch ( Exception $e ) {
        $errors[] = sprintf( '%s: %s', esc_html( $info['title'] ), $e->getMessage() );
      }
    }

	$successful_total = (int) $counts['created'] + (int) $counts['updated'] + (int) $counts['skipped'] + (int) $counts['copied'];

	if ( ! empty( $errors ) && 0 === $successful_total ) {
      throw new Exception( implode( ' | ', array_map( 'esc_html', $errors ) ) );
    }

	$message = sprintf(
		// translators: %1$d created, %2$d updated.
      __( 'Import complete! Created: %1$d, Updated: %2$d', 'mbn-theme' ),
      $counts['created'],
      $counts['updated']
	);

  if ( $counts['copied'] > 0 ) {
      // translators: %d is number of templates created as imported copies.
          $message .= sprintf( ' | ' . __( 'Copied: %d', 'mbn-theme' ), (int) $counts['copied'] );
  }

  if ( $counts['skipped'] > 0 ) {
      // translators: %d is number of existing templates skipped.
          $message .= sprintf( ' | ' . __( 'Skipped existing: %d', 'mbn-theme' ), (int) $counts['skipped'] );
  }

  if ( ! empty( $errors ) ) {
      $message .= ' ' . __( 'Warnings:', 'mbn-theme' ) . ' ' . implode( '; ', $errors );
  }

	add_settings_error(
      'custom_theme_sync',
      'import_success',
      $message,
      empty( $errors ) ? 'success' : 'warning'
	);
}

/**
 * Handle sync actions.
 */
function custom_theme_handle_template_sync_actions() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
  if ( ! isset( $_POST['custom_theme_sync_action'] ) ) {
      return;
  }

  if ( ! current_user_can( 'manage_options' ) ) {
      return;
  }

	check_admin_referer( 'custom_theme_sync_templates', 'custom_theme_sync_nonce' );

	$action         = sanitize_text_field( $_POST['custom_theme_sync_action'] );
	$selected_slugs = isset( $_POST['template_slugs'] )
		? array_map( 'sanitize_text_field', (array) $_POST['template_slugs'] )
		: array();

  if ( empty( $selected_slugs ) ) {
      add_settings_error(
        'custom_theme_sync',
        'no_selection',
        esc_html__( 'Please select at least one template.', 'mbn-theme' ),
        'error'
      );
      return;
  }

  if ( 'import_from_files' === $action ) {
		$import_mode   = isset( $_POST['import_mode'] )
			? custom_theme_template_sync_normalize_import_mode( sanitize_text_field( wp_unslash( $_POST['import_mode'] ) ) )
			: 'skip_existing';
		$sync_password = isset( $_POST['sync_password'] )
			? (string) wp_unslash( $_POST['sync_password'] )
			: '';

    if ( ! custom_theme_verify_sync_password( $sync_password ) ) {
        $message = '' === custom_theme_get_sync_password()
            ? esc_html__( 'Import blocked: sync password is not configured. Define CUSTOM_THEME_SYNC_PASSWORD in wp-config.php or environment.', 'mbn-theme' )
            : esc_html__( 'Import blocked: invalid sync password.', 'mbn-theme' );

        add_settings_error( 'custom_theme_sync', 'invalid_sync_password', $message, 'error' );
        return;
    }

    try {
				custom_theme_import_all_templates_from_files( $selected_slugs, $import_mode );
    } catch ( Exception $e ) {
        add_settings_error(
          'custom_theme_sync',
          'import_error',
          sprintf(
                // translators: %s is the error message.
            __( 'Import failed: %s', 'mbn-theme' ),
            $e->getMessage()
          ),
          'error'
        );
    }
  } elseif ( 'export_to_files' === $action ) {
    try {
        custom_theme_export_templates_to_files( $selected_slugs );
    } catch ( Exception $e ) {
        add_settings_error(
          'custom_theme_sync',
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
}
add_action( 'admin_init', 'custom_theme_handle_template_sync_actions' );

/**
 * Validate export directories exist and are writable.
 *
 * @param array $dirs Array of directory names to validate.
 * @throws Exception If directory validation fails.
 */
function custom_theme_validate_export_directories( $dirs ) {
  foreach ( $dirs as $dir_name ) {
      $dir_path = get_theme_file_path( $dir_name );
	if ( ! is_dir( $dir_path ) ) {
        wp_mkdir_p( $dir_path );
    }
    if ( ! is_dir( $dir_path ) ) {
        throw new Exception( esc_html( sprintf( 'Directory does not exist: %s', $dir_path ) ) );
    }
      // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
    if ( ! is_writable( $dir_path ) ) {
        throw new Exception( esc_html( sprintf( 'Directory is not writable: %s. Check file permissions.', $dir_path ) ) );
    }
  }
}

/**
 * Generate template file content with PHP header.
 *
 * @param WP_Post $post Template post object.
 * @return string File content.
 */
function custom_theme_generate_template_file_content( $post ) {
	$file_content  = "<?php\n";
	$file_content .= "/**\n";
	$file_content .= ' * ' . $post->post_title . " Block Template.\n";
	$file_content .= " * \n";
	$file_content .= ' * Syncs with "' . $post->post_title . "\" Block Template post.\n";
	$file_content .= " * Edit in WordPress admin, then export using Block Templates → Sync Tools.\n";
	$file_content .= " * \n";
	$file_content .= " * @package CustomTheme\n";
	$file_content .= " */\n\n";
	$file_content .= "if ( ! defined( 'ABSPATH' ) ) {\n";
	$file_content .= "\texit;\n";
	$file_content .= "}\n";
	$file_content .= "?>\n";
	$file_content .= $post->post_content;

	return $file_content;
}

/**
 * Export a single template to file.
 *
 * @param string $slug Template slug.
 * @param array  $config Export configuration with 'filename' and 'dir'.
 * @param object $wp_filesystem WP_Filesystem instance.
 * @return bool True if exported successfully.
 * @throws Exception If export fails.
 */
function custom_theme_export_single_template( $slug, $config, $wp_filesystem ) {
	$post_id = custom_theme_get_block_template_id_by_slug( $slug );
  if ( $post_id <= 0 ) {
      throw new Exception( esc_html( sprintf( 'Template post not found for slug: %s', $slug ) ) );
  }

	$post = get_post( $post_id );
  if ( ! $post instanceof \WP_Post ) {
      throw new Exception( esc_html( sprintf( 'Invalid post object for ID: %d', $post_id ) ) );
  }

	$file_content = custom_theme_generate_template_file_content( $post );
	$file_path    = get_theme_file_path( $config['dir'] . '/' . $config['filename'] . '.php' );

	// Write file using WP_Filesystem.
	$written = $wp_filesystem->put_contents( $file_path, $file_content, FS_CHMOD_FILE );

  if ( false === $written ) {
      throw new Exception( esc_html( sprintf( 'Failed to write file: %s. Check file permissions.', $file_path ) ) );
  }

	return true;
}

/**
 * Export Block Template posts to PHP files. Optionally limit to a set of slugs.
 *
 * @param array $selected_slugs Slugs to export. Empty = export all.
 * @throws Exception If no templates were exported.
 */
function custom_theme_export_templates_to_files( $selected_slugs = array() ) {
	$exported  = 0;
	$errors    = array();
	$templates = custom_theme_get_all_syncable_templates();

  if ( ! empty( $selected_slugs ) ) {
      $templates = array_intersect_key( $templates, array_flip( $selected_slugs ) );
  }

	// Initialize WP_Filesystem.
	global $wp_filesystem;
  if ( empty( $wp_filesystem ) ) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
      WP_Filesystem();
  }

	// Validate all unique export directories up front.
	$dirs = array_unique( array_column( $templates, 'export_dir' ) );
	custom_theme_validate_export_directories( $dirs );

  foreach ( $templates as $slug => $info ) {
    try {
        $config = array(
            'filename' => $info['export_file'],
            'dir'      => $info['export_dir'],
        );
        if ( custom_theme_export_single_template( $slug, $config, $wp_filesystem ) ) {
            ++$exported;
        }
    } catch ( Exception $e ) {
        $errors[] = sprintf( '%s: %s', esc_html( $info['title'] ), $e->getMessage() );
    }
  }

  if ( $exported > 0 ) {
      $message = sprintf(
          // translators: %d is the number of templates exported.
        __( '%d template(s) exported successfully!', 'mbn-theme' ),
        $exported
      );

    if ( ! empty( $errors ) ) {
      $message .= ' ' . __( 'Errors:', 'mbn-theme' ) . ' ' . implode( '; ', $errors );
    }

      add_settings_error(
        'custom_theme_sync',
        'export_success',
        $message,
        empty( $errors ) ? 'success' : 'warning'
      );
      return;
  }

	$error_message = __( 'No templates were exported.', 'mbn-theme' );

  if ( ! empty( $errors ) ) {
      $error_message .= ' ' . __( 'Errors:', 'mbn-theme' ) . ' ' . implode( '; ', array_map( 'esc_html', $errors ) );
  } else {
      $error_message .= ' ' . __( 'Make sure Block Template posts exist.', 'mbn-theme' );
  }

	throw new Exception( esc_html( $error_message ) );
}

/**
 * Render export destinations diagnostic table.
 */
function custom_theme_render_export_destinations_table() {
  ?>
	<div class="card" style="max-width: 800px; background: #fff3cd; border-left: 4px solid #ffc107;">
		<h2>🔍 Export Destinations (Debug Info)</h2>
		<table class="widefat striped" style="margin-top: 10px;">
			<thead>
				<tr>
					<th>Block Template</th>
					<th>Export Location</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>Header Template</strong> (<?php echo esc_html( custom_theme_header_template_slug() ); ?>)</td>
					<td><code>template-parts/header-template.php</code></td>
				</tr>
				<tr>
					<td><strong>Footer Template</strong> (<?php echo esc_html( custom_theme_footer_template_slug() ); ?>)</td>
					<td><code>template-parts/footer-template.php</code></td>
				</tr>
				<?php
				$page_slugs = custom_theme_get_layout_template_file_slugs();
				foreach ( $page_slugs as $slug ) {
					$layout_name = preg_replace( '/^template-/', '', $slug );
					$title       = custom_theme_layout_template_title_from_slug( $slug );
                  ?>
					<tr>
						<td><strong><?php echo esc_html( $title ); ?></strong> (<?php echo esc_html( $slug ); ?>)</td>
						<td><code>template-parts/layouts/<?php echo esc_html( $layout_name ); ?>.php</code></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<p style="margin-top: 10px;">
			<em>This table shows where each Block Template will be exported when you click the Export button.</em>
		</p>
	</div>
	<?php
}

/**
 * Render import card section.
 */
function custom_theme_render_import_card() {
	$templates = custom_theme_get_all_syncable_templates();
  ?>
	<div class="card" style="max-width: 800px;">
		<h2>📥 Import Templates from Files</h2>
		<p>Select templates to import from source files into the database.</p>

		<form method="post" style="margin-top: 20px;">
			<?php wp_nonce_field( 'custom_theme_sync_templates', 'custom_theme_sync_nonce' ); ?>
			<input type="hidden" name="custom_theme_sync_action" value="import_from_files">

			<div style="background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;padding:12px 14px;margin:0 0 15px 0;">
				<label for="template-import-mode" style="display:block;font-weight:600;margin-bottom:6px;">
					<?php esc_html_e( 'Import Mode', 'mbn-theme' ); ?>
				</label>
				<select id="template-import-mode" name="import_mode" style="min-width:280px;">
					<option value="skip_existing" selected><?php esc_html_e( 'Skip existing templates (safe)', 'mbn-theme' ); ?></option>
					<option value="update_existing"><?php esc_html_e( 'Update existing templates', 'mbn-theme' ); ?></option>
					<option value="create_copy"><?php esc_html_e( 'Create imported copies for existing templates', 'mbn-theme' ); ?></option>
				</select>
				<p style="margin:6px 0 0;color:#646970;">
					<?php esc_html_e( 'Choose how to handle template slugs that already exist in the database.', 'mbn-theme' ); ?>
				</p>
				<?php if ( custom_theme_is_sync_password_required() ) : ?>
					<label for="template-sync-password" style="display:block;font-weight:600;margin:10px 0 6px;">
						<?php esc_html_e( 'Sync Password', 'mbn-theme' ); ?>
					</label>
					<input type="password" id="template-sync-password" name="sync_password" autocomplete="current-password" style="min-width:280px;" required>
					<p style="margin:6px 0 0;color:#646970;">
						<?php esc_html_e( 'Required on production before import runs.', 'mbn-theme' ); ?>
					</p>
				<?php endif; ?>
			</div>

			<table class="widefat" style="margin-bottom: 15px;">
				<thead>
					<tr>
						<th style="width: 40px;">
							<input type="checkbox" id="select-all-import-templates" title="Select all">
						</th>
						<th>Template</th>
						<th>Source File</th>
						<th>Type</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $templates as $slug => $info ) : ?>
						<?php
						$file_path   = get_theme_file_path( $info['source_dir'] . '/' . $info['source_file'] . '.php' );
						$file_exists = file_exists( $file_path );
						$post_id     = custom_theme_get_block_template_id_by_slug( $slug );
						$row_style   = ! $file_exists ? ' style="opacity:0.5;"' : '';
						?>
						<tr<?php echo $row_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<td>
								<input type="checkbox" name="template_slugs[]" value="<?php echo esc_attr( $slug ); ?>"
									<?php checked( $file_exists ); ?> <?php disabled( ! $file_exists ); ?>>
							</td>
							<td><strong><?php echo esc_html( $info['title'] ); ?></strong></td>
							<td>
								<code><?php echo esc_html( $info['source_dir'] . '/' . $info['source_file'] . '.php' ); ?></code>
								<?php if ( ! $file_exists ) : ?>
									<span style="color:#d63638;"> (not found)</span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( ucfirst( $info['type'] ) ); ?></td>
							<td>
								<?php if ( $post_id > 0 ) : ?>
									<span style="color:#f0b849;">&#8635; Will Update</span>
								<?php else : ?>
									<span style="color:#46b450;">+ Will Create</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<script>
			document.getElementById( 'select-all-import-templates' ).addEventListener( 'change', function () {
				document.querySelectorAll( 'input[name="template_slugs[]"]:not(:disabled)' ).forEach( function ( cb ) {
					cb.checked = this.checked;
				}, this );
			} );
			</script>

			<button type="submit" class="button button-primary">
				📥 Import Selected from Files
			</button>
		</form>
	</div>
	<?php
}

/**
 * Render export card section.
 */
function custom_theme_render_export_card() {
	$templates = custom_theme_get_all_syncable_templates();
  ?>
	<div class="card" style="max-width: 800px; margin-top: 20px;">
		<h2>📤 Export Templates to Files</h2>
		<p>Select templates to export from the database to theme files.</p>

		<form method="post" style="margin-top: 20px;">
			<?php wp_nonce_field( 'custom_theme_sync_templates', 'custom_theme_sync_nonce' ); ?>
			<input type="hidden" name="custom_theme_sync_action" value="export_to_files">

			<table class="widefat" style="margin-bottom: 15px;">
				<thead>
					<tr>
						<th style="width: 40px;">
							<input type="checkbox" id="select-all-export-templates" title="Select all">
						</th>
						<th>Template</th>
						<th>Export Destination</th>
						<th>Type</th>
						<th>DB Status</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $templates as $slug => $info ) : ?>
						<?php
						$post_id   = custom_theme_get_block_template_id_by_slug( $slug );
						$has_post  = $post_id > 0;
						$row_style = ! $has_post ? ' style="opacity:0.5;"' : '';
						?>
						<tr<?php echo $row_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<td>
								<input type="checkbox" name="template_slugs[]" value="<?php echo esc_attr( $slug ); ?>"
									<?php checked( $has_post ); ?> <?php disabled( ! $has_post ); ?>>
							</td>
							<td><strong><?php echo esc_html( $info['title'] ); ?></strong></td>
							<td><code><?php echo esc_html( $info['export_dir'] . '/' . $info['export_file'] . '.php' ); ?></code></td>
							<td><?php echo esc_html( ucfirst( $info['type'] ) ); ?></td>
							<td>
								<?php if ( $has_post ) : ?>
									<span style="color:#46b450;">&#10003; Exists</span>
								<?php else : ?>
									<span style="color:#d63638;">&#10005; Not found</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<script>
			document.getElementById( 'select-all-export-templates' ).addEventListener( 'change', function () {
				document.querySelectorAll( 'input[name="template_slugs[]"]:not(:disabled)' ).forEach( function ( cb ) {
					cb.checked = this.checked;
				}, this );
			} );
			</script>

			<button type="submit" class="button button-secondary">
				📤 Export Selected to Files
			</button>
		</form>
	</div>
	<?php
}

/**
 * Render workflow instructions card.
 */
function custom_theme_render_workflow_card() {
  ?>
	<div class="card" style="max-width: 800px; margin-top: 20px; background: #f0f6fc; border-left: 4px solid #0073aa;">
		<h2>ℹ️ Development Workflow</h2>
		<h3>Local Development:</h3>
		<ol>
			<li>Edit Block Templates in WordPress admin (Block Templates menu)</li>
			<li>Click <strong>"📤 Export to Files"</strong> button above</li>
			<li>Commit updated files to Git:
				<ul>
					<li><code>template-parts/*.php</code> (header/footer)</li>
					<li><code>template-parts/layouts/*.php</code> (page template blocks)</li>
				</ul>
			</li>
			<li>Push to GitHub</li>
		</ol>

		<h3>Staging/Production Deployment:</h3>
		<ol>
			<li>Pull latest code from Git</li>
			<li>Go to <strong>Block Templates → Sync Tools</strong></li>
			<li>Click <strong>"📥 Import from Files"</strong> button</li>
			<li>Templates are now updated!</li>
		</ol>

		<h3>For Page Content (Home, About, etc):</h3>
		<p>
			Use <strong>Block Patterns</strong> instead of building pages in the editor.<br>
			Patterns are defined in <code>inc/includes-block-patterns.php</code> and ship via Git automatically.
		</p>
		<p>
			To use a pattern:
		</p>
		<ol>
			<li>Edit a page in WordPress</li>
			<li>Click the <strong>"+"</strong> button to add a block</li>
			<li>Go to the <strong>"Patterns"</strong> tab</li>
			<li>Select <strong>"DA Motorsports"</strong> category</li>
			<li>Insert your pattern (e.g., "Complete Home Page")</li>
		</ol>
	</div>
	<?php
}

/**
 * Render Template Sync Tools page.
 */
function custom_theme_render_template_tools_page() {
  ?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Template Sync Tools', 'mbn-theme' ); ?></h1>
		
		<?php settings_errors( 'custom_theme_sync' ); ?>

		<?php custom_theme_render_import_card(); ?>
		<?php custom_theme_render_export_card(); ?>
	</div>
	<?php
}
