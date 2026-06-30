<?php
/**
 * Theme setup: supports, editor styles, navigation menus, and upload support.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Register theme supports, editor styles, and navigation menus.
 *
 * @return void
 */
function custom_theme_theme_setup() {
	// Let WordPress manage the document <title>.
	add_theme_support( 'title-tag' );

	// Block + alignment + embed support.
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-line-height' );
	add_theme_support( 'custom-spacing' );
	add_theme_support( 'custom-units' );

	// Editor styles: compiled Tailwind + MBN block layout parity.
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/build/tailwind.css' );
	add_editor_style( 'assets/css/mbn-blocks-editor.css' );

	// Navigation menus.
	register_nav_menus(
      array(
		  'primary-menu' => __( 'Primary Menu', 'mbn-theme' ),
		  'footer-menu'  => __( 'Footer Menu', 'mbn-theme' ),
		  'mobile-menu'  => __( 'Mobile Menu', 'mbn-theme' ),
	  )
	);
}
add_action( 'after_setup_theme', 'custom_theme_theme_setup' );

/**
 * Enqueue the front-end lazy video loader.
 *
 * (The tabs block ships its own front-end script via block.json `viewScript`,
 * auto-enqueued only when the block is on the page.)
 *
 * @return void
 */
function custom_theme_enqueue_frontend_scripts(): void {
	// jQuery (core handle) on the front end for blocks/plugins that expect it.
	wp_enqueue_script( 'jquery' );

	$scripts = array(
		'mbn-video'  => 'assets/js/mbn-video.js',
		'mbn-reveal' => 'assets/js/mbn-reveal.js',
	);

    foreach ( $scripts as $handle => $rel ) {
      $path = get_theme_file_path( $rel );
      if ( file_exists( $path ) ) {
        wp_enqueue_script( $handle, get_theme_file_uri( $rel ), array(), (string) filemtime( $path ), true );
      }
    }
}
add_action( 'wp_enqueue_scripts', 'custom_theme_enqueue_frontend_scripts' );
