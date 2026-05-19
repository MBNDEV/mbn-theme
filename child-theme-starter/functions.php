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
    // Get parent theme version for cache busting
    $parent_theme = wp_get_theme( get_template() );
    $parent_version = $parent_theme->get( 'Version' );
    
    // Enqueue parent theme stylesheet
    wp_enqueue_style( 
        'mbn-parent-style', 
        get_template_directory_uri() . '/style.css',
        array(),
        $parent_version
    );
    
    // Enqueue parent theme's built Tailwind CSS
    wp_enqueue_style(
        'mbn-parent-tailwind',
        get_template_directory_uri() . '/assets/build/tailwind.css',
        array(),
        $parent_version
    );
    
    // Enqueue child theme stylesheet (loads last to override parent styles)
    wp_enqueue_style( 
        'mbn-child-style',
        get_stylesheet_uri(),
        array( 'mbn-parent-style', 'mbn-parent-tailwind' ),
        wp_get_theme()->get( 'Version' )
    );
}
add_action( 'wp_enqueue_scripts', 'mbn_child_enqueue_styles', 15 );

/**
 * Add child theme support for custom blocks (optional)
 * Uncomment if you want to add custom blocks to your child theme
 */
/*
function mbn_child_register_blocks() {
    // Register child theme blocks here
    // Example:
    // register_block_type( get_stylesheet_directory() . '/blocks/custom-block' );
}
add_action( 'init', 'mbn_child_register_blocks' );
*/

/**
 * Child theme setup
 */
function mbn_child_setup() {
    // Load child theme text domain for translations
    load_child_theme_textdomain( 
        'mbn-child-theme', 
        get_stylesheet_directory() . '/languages' 
    );
    
    // Add child theme specific image sizes (optional)
    // add_image_size( 'child-custom-size', 800, 600, true );
}
add_action( 'after_setup_theme', 'mbn_child_setup', 11 );

/**
 * Example: Modify parent theme functionality
 * You can override parent theme functions here
 */

// Example: Add custom navigation menu location
/*
function mbn_child_register_menus() {
    register_nav_menus( array(
        'child-custom-menu' => __( 'Child Custom Menu', 'mbn-child-theme' ),
    ) );
}
add_action( 'init', 'mbn_child_register_menus' );
*/

// Example: Customize theme options
/*
function mbn_child_customize_register( $wp_customize ) {
    // Add child theme customizer options
}
add_action( 'customize_register', 'mbn_child_customize_register' );
*/

/**
 * Add your custom functions below this line
 */
