<?php
/**
 * Animation Helper Functions
 *
 * Centralized helpers for Elementor-style entrance animations.
 * Used by block render.php files to output data-animate attributes.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get animation data attributes from block attributes.
 *
 * Reads animationType, animationDuration, animationDelay from the $attributes
 * array and returns a sanitized array of data-* attributes ready to merge into
 * get_block_wrapper_attributes().
 *
 * Usage in render.php:
 *
 *   $anim_attrs = mbn_get_animation_attrs( $attributes );
 *
 *   $wrapper_attrs = get_block_wrapper_attributes(
 *     array_merge(
 *       array(
 *         'class' => 'your-block-classes',
 *       ),
 *       $anim_attrs
 *     )
 *   );
 *
 * @param array $attributes Block attributes from render context.
 * @return array Associative array of data-* attributes (e.g., ['data-animate' => 'fadeInUp']).
 */
function mbn_get_animation_attrs( $attributes ) {
	$animation_type     = $attributes['animationType'] ?? '';
	$animation_duration = $attributes['animationDuration'] ?? '';
	$animation_delay    = (int) ( $attributes['animationDelay'] ?? 0 );

	$anim_attrs = array();

  if ( $animation_type ) {
      $anim_attrs['data-animate'] = sanitize_html_class( $animation_type );

    if ( $animation_duration && in_array( $animation_duration, array( 'slow', 'fast' ), true ) ) {
        $anim_attrs['data-animate-duration'] = $animation_duration;
    }

    if ( $animation_delay > 0 ) {
        $anim_attrs['data-animate-delay'] = (string) $animation_delay;
    }
  }

	return $anim_attrs;
}
