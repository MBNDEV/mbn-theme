<?php
/**
 * Child Theme Block Registration
 *
 * Auto-discovers and registers Gutenberg blocks from the child theme ONLY.
 *
 * @package MBN_Child_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auto-discover and register all native Gutenberg blocks from CHILD theme only.
 *
 * Scans the child theme's build/blocks/ directory for subdirectories containing block.json files
 * and registers them with WordPress.
 *
 * @return void
 */
function mbn_child_register_blocks() {
	// Use get_stylesheet_directory() to always get CHILD theme path.
	$blocks_dir = get_stylesheet_directory() . '/build/blocks';

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
add_action( 'init', 'mbn_child_register_blocks' );

/**
 * Register custom block category for child theme blocks.
 *
 * @param array $categories Array of block categories.
 * @return array Modified array of block categories.
 */
function mbn_child_register_block_category( $categories ) {
	// Check if category already exists.
	foreach ( $categories as $category ) {
		if ( 'child-theme-blocks' === $category['slug'] ) {
			return $categories;
		}
	}

	// Add custom category for child theme blocks.
	return array_merge(
		array(
			array(
				'slug'  => 'child-theme-blocks',
				'title' => __( 'Child Theme Blocks', 'mbn-child-theme' ),
				'icon'  => 'admin-customizer',
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'mbn_child_register_block_category' );
