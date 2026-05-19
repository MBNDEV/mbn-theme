<?php
/**
 * Custom Theme functions and setup.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! defined( 'CUSTOM_THEME_SECTION_BG_TABLET_IMAGE_SIZE' ) ) {
	define( 'CUSTOM_THEME_SECTION_BG_TABLET_IMAGE_SIZE', 'section-bg-tablet' );
}
if ( ! defined( 'CUSTOM_THEME_SECTION_BG_MOBILE_IMAGE_SIZE' ) ) {
	define( 'CUSTOM_THEME_SECTION_BG_MOBILE_IMAGE_SIZE', 'section-bg-mobile' );
}

if ( ! class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
  require_once get_theme_file_path( 'vendor/autoload.php' );
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Load global button component
 */
require_once get_theme_file_path( 'template-parts/button.php' );

/**
 * Theme setup
 */
function mbn_theme_setup() {
	// Add support for block styles.
	add_theme_support( 'wp-block-styles' );

	// Add support for full and wide align images.
	add_theme_support( 'align-wide' );

	// Add support for editor styles.
	add_theme_support( 'editor-styles' );

	// Inject compiled Tailwind CSS intro the iframed block editor canvas.
	add_editor_style( 'assets/build/tailwind.css' );

	// Add support for responsive embedded content.
	add_theme_support( 'responsive-embeds' );

	// Add support for custom line height.
	add_theme_support( 'custom-line-height' );

	// Add support for custom spacing.
	add_theme_support( 'custom-spacing' );

	// Add support for custom units.
  add_theme_support( 'custom-units' );

  // Register navigation menus
  register_nav_menus(
    array(
		'primary-menu'  => __( 'Primary Menu', 'mbn-theme' ),
		'footer-menu'   => __( 'Footer Menu', 'mbn-theme' ),
		'footer-menu-1' => __( 'Footer Menu Column 1', 'mbn-theme' ),
		'footer-menu-2' => __( 'Footer Menu Column 2', 'mbn-theme' ),
		'footer-legal'  => __( 'Footer Legal Links', 'mbn-theme' ),
		'mobile-menu'   => __( 'Mobile Menu', 'mbn-theme' ),
	)
  );
}

add_action( 'after_setup_theme', 'mbn_theme_setup' );

// Load theme components.
require_once get_theme_file_path( 'block-registry.php' );
require_once get_theme_file_path( 'tailwind-loader.php' );
require_once get_theme_file_path( 'optimize.php' );

// Load integrated inc/ files.
require_once get_theme_file_path( 'inc/includes-theme-options.php' );          // Native theme options page.
require_once get_theme_file_path( 'inc/includes-post-meta.php' );              // Native post meta boxes.
require_once get_theme_file_path( 'inc/includes-theme-preset-options-render.php' ); // Font presets & CSS variables.
require_once get_theme_file_path( 'inc/includes-html-injection.php' );         // Custom HTML injection.
require_once get_theme_file_path( 'inc/includes-widget-loader.php' );          // Widget area auto-loader.
require_once get_theme_file_path( 'inc/includes-block-templates.php' );        // Block Templates (Header/Footer) system.
require_once get_theme_file_path( 'inc/includes-template-page-sync.php' );     // Page template sync.
require_once get_theme_file_path( 'inc/includes-theme-block-section.php' );    // Section background utilities.
require_once get_theme_file_path( 'inc/includes-block-patterns.php' );         // Reusable block patterns.
require_once get_theme_file_path( 'inc/includes-template-sync-tools.php' );    // Template import/export tools.
require_once get_theme_file_path( 'inc/includes-page-sync.php' );              // Page content sync (optional).
require_once get_theme_file_path( 'inc/includes-nav-menu-sync.php' );          // Nav menu export/import via Git.
require_once get_theme_file_path( 'inc/includes-animation-helpers.php' );      // Animation data-attribute helpers.

/**
 * Enqueue scroll animation assets (frontend only).
 */
function mbn_theme_enqueue_scroll_animations() {
	wp_enqueue_script( 'jquery' );

	wp_enqueue_style(
      'mbn-theme-scroll-animations',
      get_theme_file_uri( 'assets/css/scroll-animations.css' ),
      array(),
      filemtime( get_theme_file_path( 'assets/css/scroll-animations.css' ) )
	);

	// Override CSS file animation-name rules (fix for specificity issue)
	$animation_css  = '[data-animate].is-visible{animation-duration:var(--animation-duration,1.25s)!important;animation-delay:var(--animation-delay,0s)!important;animation-fill-mode:both!important;animation-timing-function:ease!important}';
	$animation_css .= '[data-animate][data-animate-duration="slow"].is-visible{--animation-duration:2s}';
	$animation_css .= '[data-animate][data-animate-duration="fast"].is-visible{--animation-duration:0.75s}';
	$animation_css .= '[data-animate="fadeIn"].is-visible{animation-name:fadeIn!important}';
	$animation_css .= '[data-animate="fadeInDown"].is-visible{animation-name:fadeInDown!important}';
	$animation_css .= '[data-animate="fadeInLeft"].is-visible{animation-name:fadeInLeft!important}';
	$animation_css .= '[data-animate="fadeInRight"].is-visible{animation-name:fadeInRight!important}';
	$animation_css .= '[data-animate="fadeInUp"].is-visible{animation-name:fadeInUp!important}';
	$animation_css .= '[data-animate="zoomIn"].is-visible{animation-name:zoomIn!important}';
	$animation_css .= '[data-animate="zoomInDown"].is-visible{animation-name:zoomInDown!important}';
	$animation_css .= '[data-animate="zoomInLeft"].is-visible{animation-name:zoomInLeft!important}';
	$animation_css .= '[data-animate="zoomInRight"].is-visible{animation-name:zoomInRight!important}';
	$animation_css .= '[data-animate="zoomInUp"].is-visible{animation-name:zoomInUp!important}';
	$animation_css .= '[data-animate="bounceIn"].is-visible{animation-name:bounceIn!important}';
	$animation_css .= '[data-animate="bounceInDown"].is-visible{animation-name:bounceInDown!important}';
	$animation_css .= '[data-animate="bounceInLeft"].is-visible{animation-name:bounceInLeft!important}';
	$animation_css .= '[data-animate="bounceInRight"].is-visible{animation-name:bounceInRight!important}';
	$animation_css .= '[data-animate="bounceInUp"].is-visible{animation-name:bounceInUp!important}';
	$animation_css .= '[data-animate="slideInDown"].is-visible{animation-name:slideInDown!important}';
	$animation_css .= '[data-animate="slideInLeft"].is-visible{animation-name:slideInLeft!important}';
	$animation_css .= '[data-animate="slideInRight"].is-visible{animation-name:slideInRight!important}';
	$animation_css .= '[data-animate="slideInUp"].is-visible{animation-name:slideInUp!important}';
	$animation_css .= '[data-animate="rotateIn"].is-visible{animation-name:rotateIn!important}';
	$animation_css .= '[data-animate="rotateInDownLeft"].is-visible{animation-name:rotateInDownLeft!important}';
	$animation_css .= '[data-animate="rotateInDownRight"].is-visible{animation-name:rotateInDownRight!important}';
	$animation_css .= '[data-animate="rotateInUpLeft"].is-visible{animation-name:rotateInUpLeft!important}';
	$animation_css .= '[data-animate="rotateInUpRight"].is-visible{animation-name:rotateInUpRight!important}';
	$animation_css .= '[data-animate="lightSpeedIn"].is-visible{animation-name:lightSpeedIn!important}';
	$animation_css .= '[data-animate="rollIn"].is-visible{animation-name:rollIn!important}';

	wp_add_inline_style( 'mbn-theme-scroll-animations', $animation_css );

	wp_enqueue_script(
      'mbn-theme-scroll-animations',
      get_theme_file_uri( 'assets/js/scroll-animations.js' ),
      array( 'jquery' ),
      filemtime( get_theme_file_path( 'assets/js/scroll-animations.js' ) ),
      true
	);
}
add_action( 'wp_enqueue_scripts', 'mbn_theme_enqueue_scroll_animations' );

PucFactory::buildUpdateChecker(
  'https://github.com/MBNDEV/mbn-theme',
  get_theme_file_path( 'style.css' ),
  'mbn-theme'
);

/**
 * Conditionally disable Gravity Forms CSS only when certain blocks are present.
 * This prevents breaking other forms that may rely on default Gravity Forms styling.
 *
 * @return bool Returns true to disable CSS only if the block is detected.
 */
function mbn_theme_conditional_gform_css() {
  if ( is_admin() ) {
      return false; // Always load in admin.
  }

	global $post;

	// Check if the current post has the contact-form-section or column-sections block.
  if ( $post && ( has_block( 'mbn-theme/contact-form-section', $post ) || has_block( 'mbn-theme/column-sections', $post ) ) ) {
      return true; // Disable CSS for pages with our custom blocks.
  }

	return false; // Keep default CSS for other forms.
}
add_filter( 'gform_disable_css', 'mbn_theme_conditional_gform_css' );

/**
 * Custom Gravity Forms validation for donation amount field.
 * Validates that donation amount is numeric and meets minimum requirement.
 *
 * @param array $result The validation result array.
 * @param mixed $value The field value.
 * @param array $form The form object.
 * @param array $field The field object.
 * @return array Modified validation result.
 */
function mbn_theme_validate_donation_amount( $result, $value, $form, $field ) {
	// Check if this field has the donation-amount-field CSS class.
	// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Gravity Forms core property.
  if ( ! empty( $field->cssClass ) && strpos( $field->cssClass, 'donation-amount-field' ) !== false ) {
      // Convert value to float for validation.
      $amount = floatval( str_replace( ',', '', $value ) );

      // Validate minimum donation amount.
    if ( empty( $value ) || $amount < 1 ) {
        $result['is_valid'] = false;
        $result['message']  = __( 'Please enter a valid donation amount (minimum $1.00)', 'mbn-theme' );
    }
  }

	return $result;
}
add_filter( 'gform_field_validation', 'mbn_theme_validate_donation_amount', 10, 4 );
