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

/**
 * Inline an SVG attachment from the media library so it inherits `currentColor`
 * (recolour on hover), with explicit width/height + class injected. The file was
 * sanitized on upload; it is re-sanitized here as defence in depth. Falls back to a
 * plain <img> when the markup can't be read. Results are cached per request.
 *
 * @param int    $attachment_id SVG attachment ID.
 * @param int    $size          Square pixel size for width/height.
 * @param string $classes       CSS classes for the <svg> element.
 * @return string Inline <svg> (or <img> fallback), '' when the attachment is missing.
 */
function mbn_inline_svg_attachment( int $attachment_id, int $size = 24, string $classes = '' ): string {
  static $cache = array();

  $attachment_id = absint( $attachment_id );
  $size          = $size > 0 ? $size : 24;
  if ( ! $attachment_id ) {
    return '';
  }

  $key = $attachment_id . '|' . $size . '|' . $classes;
  if ( isset( $cache[ $key ] ) ) {
    return $cache[ $key ];
  }

  $path = get_attached_file( $attachment_id );
  $svg  = ( $path && file_exists( $path ) ) ? (string) file_get_contents( $path ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local media file.

  if ( function_exists( 'mbn_sanitize_svg_markup' ) ) {
    $svg = mbn_sanitize_svg_markup( $svg );
  }

  if ( '' === $svg || false === stripos( $svg, '<svg' ) ) {
    $cache[ $key ] = wp_get_attachment_image(
      $attachment_id,
      'full',
      false,
      array(
		  'class' => trim( 'mbn-ai-icon ' . $classes ),
		  'alt'   => '',
		  'width' => $size,
      )
    );
    return $cache[ $key ];
  }

  // Rewrite the opening <svg> tag: strip any existing width/height/class, then add
  // our class and (only when $size > 0) explicit square dimensions. With $size = 0
  // the SVG scales responsively from its viewBox (e.g. a full-width accent line).
  if ( preg_match( '/<svg\b[^>]*>/i', $svg, $open_match ) ) {
    $open = $open_match[0];
    $new  = preg_replace( '/\s(?:width|height|class)\s*=\s*("[^"]*"|\'[^\']*\')/i', '', $open );
    $attr = ' class="' . esc_attr( trim( $classes ) ) . '"';
    if ( $size > 0 ) {
      $attr .= ' width="' . $size . '" height="' . $size . '"';
    }
    $new = preg_replace( '/<svg\b/i', '<svg' . $attr, $new, 1 );
    $svg = str_replace( $open, $new, $svg );
  }

  $cache[ $key ] = (string) $svg;
  return $cache[ $key ];
}

/**
 * Responsive CSS background-image for a block wrapper.
 *
 * Emits a scoped `<style>` that sets `background-image` on `#$element_id`, swapping the
 * image by breakpoint (mobile → `medium_large`, ≥768px → `large`, ≥1280px → `full`) so
 * the browser only downloads the size it needs (non-matching media-query backgrounds are
 * never fetched). When `$lcp` is true it also prints media-scoped
 * `<link rel="preload" as="image" fetchpriority="high">` tags so the above-the-fold
 * background paints sooner. Use this instead of a background `<img>` for section
 * backgrounds; SVGs are inlined via mbn_inline_svg_attachment(), not used here.
 *
 * @param string $element_id    Wrapper element id (without `#`).
 * @param int    $attachment_id Background image attachment ID (raster).
 * @param bool   $lcp           Preload with high priority (one block per page).
 * @param string $position      Background position: center|top|bottom.
 * @return string `<style>` (+ optional preload `<link>`s), '' when unavailable.
 */
function mbn_responsive_bg( string $element_id, int $attachment_id, bool $lcp = false, string $position = 'center' ): string {
  $element_id    = sanitize_html_class( $element_id );
  $attachment_id = absint( $attachment_id );
  if ( '' === $element_id || ! $attachment_id ) {
    return '';
  }

  $desktop = wp_get_attachment_image_url( $attachment_id, 'full' );
  if ( ! $desktop ) {
    return '';
  }
  $mobile = wp_get_attachment_image_url( $attachment_id, 'medium_large' ) ?: $desktop;
  $tablet = wp_get_attachment_image_url( $attachment_id, 'large' ) ?: $desktop;
  $pos    = in_array( $position, array( 'center', 'top', 'bottom' ), true ) ? $position : 'center';
  $sel    = '#' . $element_id;

  $css  = $sel . '{background-image:url(' . esc_url( $mobile ) . ');background-size:cover;background-position:' . $pos . ';background-repeat:no-repeat;}';
  $css .= '@media (min-width:768px){' . $sel . '{background-image:url(' . esc_url( $tablet ) . ');}}';
  $css .= '@media (min-width:1280px){' . $sel . '{background-image:url(' . esc_url( $desktop ) . ');}}';

  $out = '<style>' . $css . '</style>';

  if ( $lcp ) {
    $out .= '<link rel="preload" as="image" href="' . esc_url( $mobile ) . '" media="(max-width:767px)" fetchpriority="high" />';
    $out .= '<link rel="preload" as="image" href="' . esc_url( $tablet ) . '" media="(min-width:768px) and (max-width:1279px)" fetchpriority="high" />';
    $out .= '<link rel="preload" as="image" href="' . esc_url( $desktop ) . '" media="(min-width:1280px)" fetchpriority="high" />';
  }

  return $out;
}

/**
 * Resolve a media attachment ID by its slug (post_name), e.g. an uploaded icon
 * `icon-facebook`. Lets blocks map a label to a media asset without hardcoding
 * install-specific IDs. Cached per request.
 *
 * @param string $slug Attachment slug.
 * @return int Attachment ID, or 0 when not found.
 */
function mbn_attachment_id_by_slug( string $slug ): int {
  static $cache = array();

  $slug = sanitize_title( $slug );
  if ( '' === $slug ) {
    return 0;
  }
  if ( isset( $cache[ $slug ] ) ) {
    return $cache[ $slug ];
  }

  $posts = get_posts(
    array(
		'name'             => $slug,
		'post_type'        => 'attachment',
		'post_status'      => 'inherit',
		'numberposts'      => 1,
		'fields'           => 'ids',
		'suppress_filters' => false,
    )
  );

  $cache[ $slug ] = ! empty( $posts ) ? (int) $posts[0] : 0;
  return $cache[ $slug ];
}
