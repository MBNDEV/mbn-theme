<?php
/**
 * Inline built Tailwind CSS (single bundle; block sources in block-assets subfolders).
 *
 * Inlining avoids an extra request and prevents CLS/FOUC from deferred stylesheet tricks.
 * The bundle is still produced by `npm run build:css` (purge scans theme templates).
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Path to the compiled Tailwind stylesheet.
 *
 * @return string
 */
function custom_theme_get_tailwind_build_path(): string {
  return get_theme_file_path( 'assets/build/tailwind.css' );
}

/**
 * Read compiled Tailwind CSS for inlining.
 *
 * @return string Empty string if missing or unreadable.
 */
function custom_theme_get_tailwind_build_css(): string {
  $path = custom_theme_get_tailwind_build_path();
  if ( ! is_readable( $path ) ) {
    return '';
  }
  // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme build artifact, not a remote URL.
  $css = file_get_contents( $path );
  if ( false === $css ) {
    return '';
  }

  return $css;
}

/**
 * Register inline-only style handle (no external URL).
 *
 * @return void
 */
function custom_theme_register_tailwind_styles(): void {
  $path = custom_theme_get_tailwind_build_path();
  if ( ! is_readable( $path ) ) {
    return;
  }

  wp_register_style(
    'custom-theme-tailwind',
    false,
    array(),
    (string) filemtime( $path )
  );
}
add_action( 'init', 'custom_theme_register_tailwind_styles', 4 );

/**
 * Enqueue Tailwind on the front as inline CSS in the head.
 *
 * @return void
 */
function custom_theme_enqueue_tailwind_front(): void {
  if ( is_admin() ) {
    return;
  }
  if ( ! wp_style_is( 'custom-theme-tailwind', 'registered' ) ) {
    return;
  }

  wp_enqueue_style( 'custom-theme-tailwind' );
  $css = custom_theme_get_tailwind_build_css();
  if ( '' !== $css ) {
    wp_add_inline_style( 'custom-theme-tailwind', $css );
  }
}
add_action( 'wp_enqueue_scripts', 'custom_theme_enqueue_tailwind_front', 8 );

/**
 * Enqueue the same inline Tailwind bundle in the block editor.
 *
 * @return void
 */
function custom_theme_enqueue_tailwind_editor(): void {
  // Re-register if needed (in case init hook hasn't run yet in some contexts)
  custom_theme_register_tailwind_styles();

  if ( ! wp_style_is( 'custom-theme-tailwind', 'registered' ) ) {
    return;
  }

  wp_enqueue_style( 'custom-theme-tailwind' );
  $css = custom_theme_get_tailwind_build_css();
  if ( '' !== $css ) {
    wp_add_inline_style( 'custom-theme-tailwind', $css );
  }
}
add_action( 'enqueue_block_editor_assets', 'custom_theme_enqueue_tailwind_editor', 5 );

/**
 * Enqueue theme style.css for custom CSS overrides.
 *
 * @return void
 */
function custom_theme_enqueue_main_stylesheet(): void {
  if ( is_admin() ) {
      return;
  }

	wp_enqueue_style(
      'custom-theme-style',
      get_stylesheet_uri(),
      array( 'custom-theme-tailwind' ),
      filemtime( get_stylesheet_directory() . '/style.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'custom_theme_enqueue_main_stylesheet', 10 );

/**
 * Enqueue theme style.css in the block editor.
 *
 * @return void
 */
function custom_theme_enqueue_main_stylesheet_editor(): void {
	wp_enqueue_style(
      'custom-theme-style-editor',
      get_stylesheet_uri(),
      array( 'custom-theme-tailwind' ),
      filemtime( get_stylesheet_directory() . '/style.css' )
	);
}
add_action( 'enqueue_block_editor_assets', 'custom_theme_enqueue_main_stylesheet_editor', 10 );
