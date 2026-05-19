<?php
/**
 * MBN Child Theme Functions
 *
 * @package MBN_Child_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Enqueue parent and child theme styles
 */
function mbn_child_enqueue_styles() {
	// Get parent theme version for cache busting.
	$parent_theme   = wp_get_theme( get_template() );
	$parent_version = $parent_theme->get( 'Version' );

	// Enqueue parent theme stylesheet.
	wp_enqueue_style(
      'mbn-parent-style',
      get_template_directory_uri() . '/style.css',
      array(),
      $parent_version
	);

	// Enqueue parent theme's built Tailwind CSS.
	wp_enqueue_style(
      'mbn-parent-tailwind',
      get_template_directory_uri() . '/assets/build/tailwind.css',
      array(),
      $parent_version
	);

	// Enqueue child theme's built Tailwind CSS (if exists).
	$child_tailwind_path = get_stylesheet_directory() . '/assets/build/tailwind.css';
  if ( file_exists( $child_tailwind_path ) ) {
      wp_enqueue_style(
        'mbn-child-tailwind',
        get_stylesheet_directory_uri() . '/assets/build/tailwind.css',
        array( 'mbn-parent-tailwind' ),
        filemtime( $child_tailwind_path )
      );
  }

	// Enqueue child theme stylesheet (loads last to override parent styles).
	wp_enqueue_style(
      'mbn-child-style',
      get_stylesheet_uri(),
      array( 'mbn-parent-style', 'mbn-parent-tailwind' ),
      wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'mbn_child_enqueue_styles', 15 );

/**
 * Child theme setup
 */
function mbn_child_setup() {
	// Load child theme text domain for translations.
	load_child_theme_textdomain(
      'mbn-child-theme',
      get_stylesheet_directory() . '/languages'
	);

	/**
	 * Register custom navigation menus for child theme.
	 *
	 * Uncomment and customize as needed.
	 *
	 * register_nav_menus(
	 *     array(
	 *         'child-custom-menu' => __( 'Child Custom Menu', 'mbn-child-theme' ),
	 *         'child-footer-menu' => __( 'Child Footer Menu', 'mbn-child-theme' ),
	 *     )
	 * );
	 */

	/**
	 * Add custom image sizes for child theme.
	 *
	 * Uncomment and customize as needed.
	 *
	 * add_image_size( 'child-custom-large', 1200, 800, true );
	 * add_image_size( 'child-custom-thumbnail', 400, 300, true );
	 */
}
add_action( 'after_setup_theme', 'mbn_child_setup', 11 );

/**
 * Register custom Gutenberg blocks for child theme
 *
 * Auto-registers blocks from the build/blocks/ directory.
 * Create your custom blocks in: /blocks/your-block-name/
 * Each block should have: block.json, index.js, edit.js, save.js
 * Build with: npm run build
 */
function mbn_child_register_blocks() {
	// Auto-register blocks from build directory.
	$build_dir = get_stylesheet_directory() . '/build/blocks';

  if ( is_dir( $build_dir ) ) {
      $blocks = glob( $build_dir . '/*', GLOB_ONLYDIR );

    foreach ( $blocks as $block_path ) {
        $block_json = $block_path . '/block.json';
      if ( file_exists( $block_json ) ) {
        register_block_type( $block_path );
      }
    }
  }

	/**
	 * Manual block registration (if needed).
	 *
	 * Uncomment and customize for specific blocks.
	 *
	 * if ( file_exists( get_stylesheet_directory() . '/blocks/custom-cta-block' ) ) {
	 *     register_block_type( get_stylesheet_directory() . '/blocks/custom-cta-block' );
	 * }
	 */
}
add_action( 'init', 'mbn_child_register_blocks' );

/**
 * Register custom post types for child theme
 *
 * Add project-specific post types here.
 */
function mbn_child_register_post_types() {
	/**
	 * Example: Register a Portfolio post type.
	 *
	 * Uncomment and customize as needed.
	 *
	 * register_post_type(
	 *     'portfolio',
	 *     array(
	 *         'labels'       => array(
	 *             'name'          => __( 'Portfolio', 'mbn-child-theme' ),
	 *             'singular_name' => __( 'Portfolio Item', 'mbn-child-theme' ),
	 *         ),
	 *         'public'       => true,
	 *         'has_archive'  => true,
	 *         'menu_icon'    => 'dashicons-portfolio',
	 *         'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
	 *         'rewrite'      => array( 'slug' => 'portfolio' ),
	 *     )
	 * );
	 */
}
add_action( 'init', 'mbn_child_register_post_types' );

/**
 * Register custom taxonomies for child theme
 */
function mbn_child_register_taxonomies() {
	/**
	 * Example: Register a custom taxonomy.
	 *
	 * Uncomment and customize as needed.
	 *
	 * register_taxonomy(
	 *     'portfolio_category',
	 *     'portfolio',
	 *     array(
	 *         'labels'       => array(
	 *             'name'          => __( 'Portfolio Categories', 'mbn-child-theme' ),
	 *             'singular_name' => __( 'Portfolio Category', 'mbn-child-theme' ),
	 *         ),
	 *         'hierarchical' => true,
	 *         'public'       => true,
	 *         'rewrite'      => array( 'slug' => 'portfolio-category' ),
	 *     )
	 * );
	 */
}
add_action( 'init', 'mbn_child_register_taxonomies' );

/**
 * Customizer settings for child theme
 *
 * Add theme customizer options specific to this project.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function mbn_child_customize_register( $wp_customize ) {
	// Prevent unused parameter warning.
	unset( $wp_customize );

	/**
	 * Example: Add a custom color setting.
	 *
	 * Uncomment and customize as needed.
	 *
	 * $wp_customize->add_setting(
	 *     'child_primary_color',
	 *     array(
	 *         'default'           => '#0066cc',
	 *         'sanitize_callback' => 'sanitize_hex_color',
	 *     )
	 * );
	 *
	 * $wp_customize->add_control(
	 *     new WP_Customize_Color_Control(
	 *         $wp_customize,
	 *         'child_primary_color',
	 *         array(
	 *             'label'    => __( 'Primary Color', 'mbn-child-theme' ),
	 *             'section'  => 'colors',
	 *             'settings' => 'child_primary_color',
	 *         )
	 *     )
	 * );
	 */
}
add_action( 'customize_register', 'mbn_child_customize_register' );

/**
 * Enable parent theme's sync tools in child theme
 *
 * Child theme can use parent's import/export tools for:
 * - Block templates (header/footer)
 * - Page templates and content
 * - Navigation menus
 *
 * Access at: WordPress Admin → Block Templates → Sync Tools
 */
function mbn_child_enable_sync_tools() {
	// Parent theme's sync tools are automatically available.
	// No additional code needed - they work with child themes.

	/**
	 * Optional: Add child-theme-specific sync hooks.
	 *
	 * Uncomment and customize as needed.
	 *
	 * add_filter( 'mbn_sync_additional_paths', 'mbn_child_add_sync_paths' );
	 * function mbn_child_add_sync_paths( $paths ) {
	 *     $paths['child-templates'] = get_stylesheet_directory() . '/template-parts/custom';
	 *     return $paths;
	 * }
	 */
}
add_action( 'after_setup_theme', 'mbn_child_enable_sync_tools', 12 );

/**
 * Add your custom functions below this line
 */
