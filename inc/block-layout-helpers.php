<?php
/**
 * Layout helpers shared by MBN block render templates.
 *
 * Pure helpers used by blocks/*\/render.php. The previous shared render shell
 * (mbn_theme_render_layout_shell + Mbn_Theme_Block_Layout) was removed; each
 * render.php now outputs its own markup and uses these helpers for the small
 * id/style/scoped-css logic that mirrors the editor.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a stable element id for block wrapper scoping.
 *
 * @param array  $attributes Block attributes.
 * @param string $block_slug Block slug without namespace.
 * @return string
 */
function mbn_theme_get_block_element_id( $attributes, $block_slug ) {
  if ( ! empty( $attributes['anchor'] ) ) {
      return sanitize_title( $attributes['anchor'] );
  }

  if ( ! empty( $attributes['blockInstanceId'] ) ) {
      return sanitize_html_class( $attributes['blockInstanceId'] );
  }

	return 'mbn-' . sanitize_html_class( $block_slug );
}

/**
 * Build inline style string from layout attributes.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function mbn_theme_get_layout_inline_styles( $attributes ) {
	$spacing_map = array(
		'marginTop'     => 'margin-top',
		'marginRight'   => 'margin-right',
		'marginBottom'  => 'margin-bottom',
		'marginLeft'    => 'margin-left',
		'paddingTop'    => 'padding-top',
		'paddingRight'  => 'padding-right',
		'paddingBottom' => 'padding-bottom',
		'paddingLeft'   => 'padding-left',
	);

	$styles = array();

	foreach ( $spacing_map as $attribute_key => $property ) {
      if ( ! empty( $attributes[ $attribute_key ] ) ) {
          $styles[] = $property . ':' . esc_attr( $attributes[ $attribute_key ] );
      }
	}

	if ( ! empty( $attributes['backgroundColor'] ) ) {
		$styles[] = 'background-color:' . esc_attr( $attributes['backgroundColor'] );
	}

	if ( ! empty( $attributes['textColor'] ) ) {
		$styles[] = 'color:' . esc_attr( $attributes['textColor'] );
	}

	if ( ! empty( $attributes['accentColor'] ) ) {
		$styles[] = '--mbn-accent-color:' . esc_attr( $attributes['accentColor'] );
	}

	return implode( ';', $styles );
}

/**
 * Scope custom CSS declarations to a block wrapper id.
 *
 * @param string $element_id Block wrapper id.
 * @param string $custom_css Raw CSS declarations or rules.
 * @return string
 */
function mbn_theme_get_scoped_custom_css( $element_id, $custom_css ) {
	$custom_css = trim( (string) $custom_css );

  if ( '' === $custom_css || '' === $element_id ) {
      return '';
  }

	$custom_css = wp_strip_all_tags( $custom_css );

  if ( false === strpos( $custom_css, '{' ) ) {
      return '#' . esc_attr( $element_id ) . '{' . $custom_css . '}';
  }

	return preg_replace( '/([^{}]+)\{/', '#' . esc_attr( $element_id ) . ' $1{', $custom_css );
}

/**
 * Image attributes for an LCP (above-the-fold) image: load it eagerly with high
 * fetch priority so the Largest Contentful Paint element paints sooner. Returns
 * an empty array when the block is not the LCP, so callers can always merge it.
 *
 * @param bool $is_lcp Whether this block holds the LCP image.
 * @return array<string, string>
 */
function mbn_lcp_img_attrs( bool $is_lcp ): array {
  if ( ! $is_lcp ) {
    return array();
  }
  return array(
	  'fetchpriority' => 'high',
	  'loading'       => 'eager',
  );
}

/**
 * Background image as a responsive <img> (not a CSS background) so WordPress emits
 * a srcset/sizes and the browser can pick the right size. Uses the stored
 * attachment id (preferred) or resolves the legacy URL to one; falls back to a
 * plain <img> for an unresolved URL. The image is decorative and click-through.
 *
 * @param array $attributes Block attributes (backgroundImageId/Url/Size).
 * @return string <img> HTML, or '' when there is no background image.
 */
function mbn_layout_bg_image_html( array $attributes ): string {
  $id    = (int) ( $attributes['backgroundImageId'] ?? 0 );
  $url   = (string) ( $attributes['backgroundImageUrl'] ?? '' );
  $size  = isset( $attributes['backgroundImageSize'] ) && '' !== $attributes['backgroundImageSize']
    ? sanitize_key( (string) $attributes['backgroundImageSize'] )
    : 'full';
  $class = 'mbn-layout-image pointer-events-none absolute inset-0 z-0 h-full w-full object-cover object-center';

  if ( ! $id && '' !== $url ) {
    $id = (int) attachment_url_to_postid( $url );
  }

  if ( $id ) {
    return wp_get_attachment_image(
      $id,
      $size,
      false,
      array(
		  'class'       => $class,
		  'alt'         => '',
		  'sizes'       => '100vw',
		  'aria-hidden' => 'true',
      )
    );
  }

  if ( '' !== $url ) {
    return sprintf(
      '<img src="%s" alt="" aria-hidden="true" loading="lazy" class="%s" />',
      esc_url( $url ),
      esc_attr( $class )
    );
  }

  return '';
}
