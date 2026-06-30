<?php
/**
 * Custom Theme functions: load theme components.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// Theme setup, helpers, block registry, and asset loaders.
require_once get_theme_file_path( 'inc/includes-theme-setup.php' );            // Supports, editor styles, menus, uploads.
require_once get_theme_file_path( 'inc/block-layout-helpers.php' );            // Block render layout helpers.
require_once get_theme_file_path( 'block-registry.php' );                      // Register blocks from source.
require_once get_theme_file_path( 'tailwind-loader.php' );                     // Inline Tailwind (front + editor).

// Integrated inc/ files.
require_once get_theme_file_path( 'inc/includes-media-uploads.php' );          // Secure SVG + video uploads.
require_once get_theme_file_path( 'inc/includes-theme-settings.php' );         // Presets storage + CSS variable output.
require_once get_theme_file_path( 'inc/includes-admin-page.php' );             // Appearance > MBN Theme settings page.
require_once get_theme_file_path( 'inc/includes-custom-html.php' );            // Global + per-post HTML injection.
require_once get_theme_file_path( 'inc/includes-block-templates.php' );        // Block Templates post type.
require_once get_theme_file_path( 'inc/includes-template-reuse.php' );         // Remote Block Template reuse tools.
require_once get_theme_file_path( 'inc/includes-testimonials.php' );           // Testimonials (rider feedback) post type.
require_once get_theme_file_path( 'inc/includes-content-io.php' );             // Post import/export upsert (CLI + REST).
require_once get_theme_file_path( 'optimizations.php' );                       // Front-end defer of fonts/scripts/styles.
