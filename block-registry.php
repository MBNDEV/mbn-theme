<?php
/**
 * Native Gutenberg Block Registry
 *
 * Auto-discovers and registers all native Gutenberg blocks from the blocks/ directory.
 * Blocks are identified by the presence of a block.json file.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auto-discover and register all native Gutenberg blocks.
 *
 * Scans the build/blocks/ directory for subdirectories containing block.json files
 * and registers them with WordPress.
 *
 * @return void
 */
function blacklineguardianfund_register_blocks() {
	$blocks_dir = get_theme_file_path( 'build/blocks' );

	// Check if blocks directory exists.
  if ( ! is_dir( $blocks_dir ) ) {
      return;
  }

	// Get all subdirectories in the blocks folder.
	$block_folders = glob( $blocks_dir . '/*', GLOB_ONLYDIR );

  if ( empty( $block_folders ) ) {
      return;
  }

	// Register each block that has a block.json file.
  foreach ( $block_folders as $block_folder ) {
      $block_json = $block_folder . '/block.json';

    if ( file_exists( $block_json ) ) {
        register_block_type( $block_folder );
    }
  }
}
add_action( 'init', 'blacklineguardianfund_register_blocks' );

/**
 * Show admin notice with registered blocks (for debugging).
 * Remove this after confirming blocks are working.
 */
function blacklineguardianfund_show_blocks_notice() {
  if ( ! current_user_can( 'manage_options' ) ) {
      return;
  }

	$registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
	$theme_blocks      = array_filter(
      array_keys( $registered_blocks ),
      function ( $block_name ) {
		return strpos( $block_name, 'mbn-theme/' ) === 0;
      }
    );

  if ( ! empty( $theme_blocks ) ) {
      echo '<div class="notice notice-success is-dismissible">';
      echo '<p><strong>Theme Blocks Registered:</strong> ' . count( $theme_blocks ) . '</p>';
      echo '<ul>';
    foreach ( $theme_blocks as $block_name ) {
        echo '<li>' . esc_html( $block_name ) . '</li>';
    }
      echo '</ul>';
      echo '</div>';
  } else {
      echo '<div class="notice notice-warning is-dismissible">';
      echo '<p><strong>Warning:</strong> No theme blocks found. Check debug log.</p>';
      echo '</div>';
  }
}
add_action( 'admin_notices', 'blacklineguardianfund_show_blocks_notice' );

/**
 * Register custom block category for theme blocks.
 *
 * @param array $categories Array of block categories.
 * @return array Modified array of block categories.
 */
function blacklineguardianfund_register_block_category( $categories ) {
	// Check if category already exists.
  foreach ( $categories as $category ) {
    if ( 'mbn-blocks' === $category['slug'] ) {
        return $categories;
    }
  }

	// Add custom category at the beginning.
	return array_merge(
      array(
		  array(
			  'slug'  => 'mbn-blocks',
			  'title' => __( 'MBN Blocks', 'mbn-theme' ),
			  'icon'  => 'wordpress',
		  ),
	  ),
      $categories
	);
}
add_filter( 'block_categories_all', 'blacklineguardianfund_register_block_category' );

/**
 * Enqueue block editor assets.
 *
 * @return void
 */
function blacklineguardianfund_enqueue_block_editor_assets() {
	// Enqueue editor styles if needed.
	// This is where you can add global editor styles that apply to all blocks.
	$editor_css = get_theme_file_uri( 'assets/css/editor.css' );

  if ( file_exists( get_theme_file_path( 'assets/css/editor.css' ) ) ) {
      wp_enqueue_style(
        'blacklineguardianfund-editor-styles',
        $editor_css,
        array(),
        wp_get_theme()->get( 'Version' )
      );
  }
}
add_action( 'enqueue_block_editor_assets', 'blacklineguardianfund_enqueue_block_editor_assets' );
