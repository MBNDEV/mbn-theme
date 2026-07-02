<?php
/**
 * Theme settings storage + CSS variable output.
 *
 * Preset values (color schemes, typography, fallback fonts, layout) are stored
 * in a single `mbn_settings` option and edited on the Appearance > MBN Theme
 * page (see inc/includes-admin-page.php). This file owns the defaults, readers,
 * sanitizers and the `--mbn-*` CSS variable + `@font-face` output used on the
 * front end and in the block editor.
 *
 * Font families come from the WordPress Fonts Library (custom-installed fonts
 * only), never from theme.json/theme-registered families.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Default fallback font src list (local() lookups across common platforms).
 *
 * @return string
 */
function mbn_default_fallback_src(): string {
  return "local('Segoe UI'), local('Roboto'), local('Ubuntu'), local('DejaVu Sans'), local('Helvetica Neue'), local('Arial')";
}

/**
 * Default color scheme hex values (in order).
 *
 * @return array<int, string>
 */
function mbn_default_color_schemes(): array {
  return array( '#2563EB', '#1E293B', '#64748B', '#F8FAFC', '#0EA5E9', '#111827' );
}

/**
 * Default heading/body font sizes (key => CSS size). Standard modular scale.
 *
 * @return array<string, string>
 */
function mbn_default_font_sizes(): array {
  return array(
	  'h1'   => '2.75rem',
	  'h2'   => '2.25rem',
	  'h3'   => '1.75rem',
	  'h4'   => '1.5rem',
	  'h5'   => '1.25rem',
	  'h6'   => '1.125rem',
	  'body' => '1rem',
  );
}

/**
 * Default font weights (key => CSS font-weight). Headings bold, body regular.
 *
 * @return array<string, string>
 */
function mbn_default_font_weights(): array {
  return array(
	  'h1'   => '700',
	  'h2'   => '700',
	  'h3'   => '700',
	  'h4'   => '700',
	  'h5'   => '700',
	  'h6'   => '700',
	  'body' => '400',
  );
}

/**
 * All theme-settings keys and their defaults (stored under the mbn_settings option).
 *
 * @return array<string, mixed>
 */
function mbn_settings_defaults(): array {
  $defaults = array(
	  'font_primary'    => '',
	  'font_secondary'  => '',
	  'color_schemes'   => mbn_default_color_schemes(),
	  'container_width' => '1280px',
	  'border_radius'   => '0.5rem',
	  'maps_api_key'    => '',
  );

  // Desktop sizes default to the scale; tablet/mobile default to '' (auto-reduced).
  foreach ( mbn_default_font_sizes() as $key => $size ) {
    $defaults[ 'size_' . $key ]             = $size;
    $defaults[ 'size_' . $key . '_tablet' ] = '';
    $defaults[ 'size_' . $key . '_mobile' ] = '';
  }

  foreach ( mbn_default_font_weights() as $key => $weight ) {
    $defaults[ 'weight_' . $key ] = $weight;
  }

  foreach ( array( 'primary', 'secondary' ) as $which ) {
    $defaults[ "fallback_{$which}_src" ]               = mbn_default_fallback_src();
    $defaults[ "fallback_{$which}_size_adjust" ]       = '';
    $defaults[ "fallback_{$which}_ascent_override" ]   = '';
    $defaults[ "fallback_{$which}_descent_override" ]  = '';
    $defaults[ "fallback_{$which}_line_gap_override" ] = '';
  }

  return $defaults;
}

/**
 * Read all theme settings merged over defaults.
 *
 * @return array<string, mixed>
 */
function mbn_get_settings(): array {
  $saved = get_option( 'mbn_settings', array() );
  if ( ! is_array( $saved ) ) {
    $saved = array();
  }

  return array_merge( mbn_settings_defaults(), $saved );
}

/**
 * Read a single theme setting.
 *
 * @param string $key Setting key.
 * @return mixed
 */
function mbn_setting( string $key ) {
  $settings = mbn_get_settings();

  return $settings[ $key ] ?? '';
}

/**
 * Resolve the available font families from the Fonts Library (custom origin only).
 *
 * @return array<string, array{name:string, stack:string}>
 */
function mbn_get_font_families(): array {
  static $cache = null;
  if ( null !== $cache ) {
    return $cache;
  }

  $families = array();

  if ( post_type_exists( 'wp_font_family' ) ) {
    $posts = get_posts(
      array(
		  'post_type'        => 'wp_font_family',
		  'post_status'      => 'any',
		  'numberposts'      => -1,
		  'orderby'          => 'title',
		  'order'            => 'ASC',
		  'suppress_filters' => false,
      )
    );

    foreach ( $posts as $post ) {
      $family = mbn_font_family_from_post( $post );
      if ( ! empty( $family ) ) {
        $families[ $family['slug'] ] = array(
			'name'  => $family['name'],
			'stack' => $family['stack'],
        );
      }
    }
  }

  $cache = $families;

  return $families;
}

/**
 * Extract a font family (slug/name/stack) from a wp_font_family post.
 *
 * Core stores the slug in post_name and the name in post_title; the
 * post_content JSON holds the CSS stack (fontFamily). Older formats may embed
 * slug/name in the JSON, so fall back to those.
 *
 * @param WP_Post $post Font family post.
 * @return array{slug:string, name:string, stack:string} Empty array when invalid.
 */
function mbn_font_family_from_post( $post ): array {
  $data  = json_decode( (string) $post->post_content, true );
  $data  = is_array( $data ) ? $data : array();
  $slug  = '' !== (string) $post->post_name ? (string) $post->post_name : (string) ( $data['slug'] ?? '' );
  $name  = '' !== (string) $post->post_title ? (string) $post->post_title : (string) ( $data['name'] ?? $slug );
  $stack = (string) ( $data['fontFamily'] ?? '' );

  if ( '' === $slug || '' === $stack ) {
    return array();
  }

  return array(
	  'slug'  => $slug,
	  'name'  => '' !== $name ? $name : $slug,
	  'stack' => $stack,
  );
}

/**
 * Normalize a stored font-family slug to one that currently exists ('' if none).
 *
 * @param mixed $slug Raw value.
 * @return string
 */
function mbn_normalize_font_family( $slug ): string {
  $families = mbn_get_font_families();

  return ( is_string( $slug ) && isset( $families[ $slug ] ) ) ? $slug : '';
}

/**
 * Build a full font-family value with the metric-matched fallback face as the
 * FIRST fallback — placed right after the chosen webfont, before the stack's own
 * generic fallbacks (e.g. system-ui). This way, while the deferred webfont loads,
 * the browser uses the metric-matched face (minimal layout shift) rather than
 * system-ui.
 *
 * @param string $slug      Family slug ('' for none).
 * @param string $face_name Fallback @font-face family name.
 * @return string
 */
function mbn_build_font_family_value( string $slug, string $face_name ): string {
  $families = mbn_get_font_families();
  $stack    = ( '' !== $slug && isset( $families[ $slug ] ) ) ? trim( $families[ $slug ]['stack'] ) : '';
  $fallback = "'" . $face_name . "'";

  if ( '' === $stack ) {
    return $fallback . ', sans-serif';
  }

  // Split the selected stack into the webfont (first) and its generic fallbacks.
  $parts = array_filter( array_map( 'trim', explode( ',', $stack ) ), 'strlen' );
  $first = array_shift( $parts );

  // webfont, metric-matched fallback, …rest, sans-serif.
  $ordered = array_merge( array( $first, $fallback ), $parts );
  if ( ! in_array( 'sans-serif', $ordered, true ) ) {
    $ordered[] = 'sans-serif';
  }

  return implode( ', ', $ordered );
}

/**
 * Convert a hex color to an rgba() string.
 *
 * @param string $hex   Hex color (#rgb or #rrggbb).
 * @param float  $alpha Alpha 0..1.
 * @return string Empty string when hex is invalid.
 */
function mbn_hex_to_rgba( string $hex, float $alpha = 1.0 ): string {
  $hex = ltrim( trim( $hex ), '#' );

  if ( 3 === strlen( $hex ) ) {
    $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
  }

  if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
    return '';
  }

  $r = hexdec( substr( $hex, 0, 2 ) );
  $g = hexdec( substr( $hex, 2, 2 ) );
  $b = hexdec( substr( $hex, 4, 2 ) );

  return sprintf( 'rgba(%d, %d, %d, %s)', $r, $g, $b, rtrim( rtrim( number_format( $alpha, 2, '.', '' ), '0' ), '.' ) );
}

/**
 * Resolve current color schemes (slot index 1..N => hex).
 *
 * @return array<int, string>
 */
function mbn_get_color_schemes(): array {
  $raw     = mbn_setting( 'color_schemes' );
  $schemes = array();
  $index   = 1;

  foreach ( (array) $raw as $value ) {
    $hex = sanitize_hex_color( trim( (string) $value ) );
    if ( '' !== (string) $hex ) {
      $schemes[ $index ] = $hex;
      ++$index;
    }
  }

  if ( empty( $schemes ) ) {
    foreach ( mbn_default_color_schemes() as $i => $hex ) {
      $schemes[ $i + 1 ] = $hex;
    }
  }

  return $schemes;
}

/**
 * Build the `@font-face` rules for the primary and secondary fallback fonts.
 *
 * @return string
 */
function mbn_build_font_faces_css(): string {
  $faces = array(
	  'primary'   => 'MBN Fallback Primary',
	  'secondary' => 'MBN Fallback Secondary',
  );

  $descriptors = array(
	  'size-adjust'       => 'size_adjust',
	  'ascent-override'   => 'ascent_override',
	  'descent-override'  => 'descent_override',
	  'line-gap-override' => 'line_gap_override',
  );

  $css = '';
  foreach ( $faces as $which => $face_name ) {
    $src = trim( (string) mbn_setting( "fallback_{$which}_src" ) );
    if ( '' === $src ) {
      $src = mbn_default_fallback_src();
    }

    $rules = "font-family:'" . $face_name . "';src:" . $src . ';font-display:swap;';
    foreach ( $descriptors as $prop => $key ) {
      $value = trim( (string) mbn_setting( "fallback_{$which}_{$key}" ) );
      if ( '' !== $value ) {
        $rules .= $prop . ':' . $value . ';';
      }
    }

    $css .= '@font-face{' . $rules . '}';
  }

  return $css;
}

/**
 * Default tablet/mobile scale factors per typography level. Headings shrink on
 * smaller screens; the paragraph stays the same size by default.
 *
 * @return array<string, array{tablet:float, mobile:float}>
 */
function mbn_typography_scale_factors(): array {
  $heading = array(
	  'tablet' => 0.85,
	  'mobile' => 0.72,
  );
  return array(
	  'h1'   => $heading,
	  'h2'   => $heading,
	  'h3'   => $heading,
	  'h4'   => $heading,
	  'h5'   => $heading,
	  'h6'   => $heading,
	  'body' => array(
		  'tablet' => 1.0,
		  'mobile' => 1.0,
	  ),
  );
}

/**
 * The desktop (base) size for a typography level.
 *
 * @param string $key Level key (h1..h6, body).
 * @return string
 */
function mbn_typography_desktop_size( string $key ): string {
  $size     = (string) mbn_setting( 'size_' . $key );
  $defaults = mbn_default_font_sizes();
  return '' !== trim( $size ) ? $size : ( $defaults[ $key ] ?? '1rem' );
}

/**
 * Scale a CSS size by a factor, never going below a floor of the same unit.
 *
 * @param string $size   e.g. "2.75rem".
 * @param float  $factor Multiplier (<1 reduces).
 * @param string $floor  Minimum size (same unit) — usually the body size.
 * @return string
 */
function mbn_scale_typography_size( string $size, float $factor, string $floor ): string {
  if ( $factor >= 1.0 || ! preg_match( '/^([\d.]+)(rem|em|px|%)?$/', trim( $size ), $m ) ) {
    return $size;
  }

  $unit   = $m[2] ?? '';
  $scaled = round( (float) $m[1] * $factor, 3 );

  if ( preg_match( '/^([\d.]+)' . preg_quote( $unit, '/' ) . '$/', trim( $floor ), $fm ) && $scaled < (float) $fm[1] ) {
    $scaled = (float) $fm[1];
  }

  return $scaled . $unit;
}

/**
 * The size for a typography level at a breakpoint. Tablet/mobile use an explicit
 * value when set, otherwise auto-reduce from the desktop size (floored at body).
 *
 * @param string $key        Level key.
 * @param string $breakpoint desktop | tablet | mobile.
 * @return string
 */
function mbn_typography_size( string $key, string $breakpoint ): string {
  $desktop = mbn_typography_desktop_size( $key );
  if ( 'desktop' === $breakpoint ) {
    return $desktop;
  }

  $override = (string) mbn_setting( 'size_' . $key . '_' . $breakpoint );
  if ( '' !== trim( $override ) ) {
    return $override;
  }

  $factors = mbn_typography_scale_factors();
  $factor  = $factors[ $key ][ $breakpoint ] ?? 1.0;
  return mbn_scale_typography_size( $desktop, $factor, mbn_typography_desktop_size( 'body' ) );
}

/**
 * Build the `--mbn-size-*` (mobile base) and `--mbn-weight-*` CSS variables.
 * Tablet/desktop overrides are emitted as media queries by
 * mbn_build_typography_media().
 *
 * @return string
 */
function mbn_build_typography_vars(): string {
  $css = '';

  foreach ( mbn_default_font_sizes() as $key => $default_size ) {
    $css .= '--mbn-size-' . $key . ':' . mbn_typography_size( $key, 'mobile' ) . ';';
  }

  foreach ( mbn_default_font_weights() as $key => $default_weight ) {
    $weight = (string) mbn_setting( 'weight_' . $key );
    $css   .= '--mbn-weight-' . $key . ':' . ( '' !== trim( $weight ) ? $weight : $default_weight ) . ';';
  }

  return $css;
}

/**
 * Tablet (≥768px) and desktop (≥1024px) `--mbn-size-*` overrides. Base (mobile)
 * sizes live in :root via mbn_build_typography_vars().
 *
 * @return string
 */
function mbn_build_typography_media(): string {
  $tablet  = '';
  $desktop = '';

  foreach ( array_keys( mbn_default_font_sizes() ) as $key ) {
    $tablet  .= '--mbn-size-' . $key . ':' . mbn_typography_size( $key, 'tablet' ) . ';';
    $desktop .= '--mbn-size-' . $key . ':' . mbn_typography_size( $key, 'desktop' ) . ';';
  }

  return '@media(min-width:768px){:root{' . $tablet . '}}@media(min-width:1024px){:root{' . $desktop . '}}';
}

/**
 * Build the fallback `@font-face` rules + `:root` CSS variables + base typography.
 *
 * @return string
 */
function mbn_build_css_variables(): string {
  $primary_slug     = mbn_normalize_font_family( mbn_setting( 'font_primary' ) );
  $secondary_slug   = mbn_normalize_font_family( mbn_setting( 'font_secondary' ) );
  $primary_family   = mbn_build_font_family_value( $primary_slug, 'MBN Fallback Primary' );
  $secondary_family = mbn_build_font_family_value( $secondary_slug, 'MBN Fallback Secondary' );

  $container_width = (string) mbn_setting( 'container_width' );
  $border_radius   = (string) mbn_setting( 'border_radius' );

  // Fallback @font-face rules first, then the variable map.
  $css = mbn_build_font_faces_css();

  $css .= ':root{';
  $css .= '--mbn-font-primary:' . $primary_family . ';';
  $css .= '--mbn-font-secondary:' . $secondary_family . ';';
  $css .= "--mbn-font-fallback:'MBN Fallback Primary', sans-serif;";

  $css .= mbn_build_typography_vars();

  $schemes = mbn_get_color_schemes();
  foreach ( $schemes as $index => $hex ) {
    $rgba = mbn_hex_to_rgba( $hex, 1.0 );
    if ( '' !== $rgba ) {
      $css .= '--mbn-color-scheme-' . $index . ':' . $rgba . ';';
    }
  }

  // Semantic aliases: scheme-1 is primary, scheme-2 is secondary (falls back to scheme-1).
  if ( isset( $schemes[1] ) ) {
    $css .= '--mbn-color-primary:var(--mbn-color-scheme-1);';
    $css .= '--mbn-color-secondary:var(--mbn-color-scheme-2, var(--mbn-color-scheme-1));';
  }

  if ( '' !== trim( $container_width ) ) {
    $css .= '--mbn-container-width:' . $container_width . ';';
  }
  if ( '' !== trim( $border_radius ) ) {
    $css .= '--mbn-radius:' . $border_radius . ';';
  }

  $css .= '}';

  // Responsive heading sizes: tablet/desktop overrides of the mobile-base vars.
  $css .= mbn_build_typography_media();

  // Base typography application.
  $css .= 'body{font-family:var(--mbn-font-secondary);font-weight:var(--mbn-weight-body);}';
  $css .= 'h1,h2,h3,h4,h5,h6{font-family:var(--mbn-font-primary);}';
  $css .= 'h1{font-size:var(--mbn-size-h1);font-weight:var(--mbn-weight-h1);}';
  $css .= 'h2{font-size:var(--mbn-size-h2);font-weight:var(--mbn-weight-h2);}';
  $css .= 'h3{font-size:var(--mbn-size-h3);font-weight:var(--mbn-weight-h3);}';
  $css .= 'h4{font-size:var(--mbn-size-h4);font-weight:var(--mbn-weight-h4);}';
  $css .= 'h5{font-size:var(--mbn-size-h5);font-weight:var(--mbn-weight-h5);}';
  $css .= 'h6{font-size:var(--mbn-size-h6);font-weight:var(--mbn-weight-h6);}';
  $css .= 'p{font-size:var(--mbn-size-body);font-weight:var(--mbn-weight-body);}';

  // Override Tailwind container width and expose a radius utility.
  if ( '' !== trim( $container_width ) ) {
    $css .= '.container{max-width:var(--mbn-container-width);}';
  }
  $css .= '.mbn-radius{border-radius:var(--mbn-radius);}';

  return $css;
}

/**
 * Attach CSS variables + fallback @font-face to the Tailwind handle (front + editor).
 *
 * Webfont @font-face for installed Fonts Library families is printed by core.
 *
 * @return void
 */
function mbn_enqueue_preset_styles(): void {
  if ( wp_style_is( 'custom-theme-tailwind', 'enqueued' ) || wp_style_is( 'custom-theme-tailwind', 'registered' ) ) {
    wp_add_inline_style( 'custom-theme-tailwind', mbn_build_css_variables() );
  }
}
add_action( 'wp_enqueue_scripts', 'mbn_enqueue_preset_styles', 12 );
add_action( 'enqueue_block_editor_assets', 'mbn_enqueue_preset_styles', 12 );

/**
 * Collect a selected family's font-face settings for wp_print_font_faces().
 *
 * @param string $slug Family slug (the wp_font_family post_name).
 * @return array<int, array<string, mixed>> Empty when not found / no faces.
 */
function mbn_get_family_font_faces( string $slug ): array {
  if ( '' === $slug || ! post_type_exists( 'wp_font_face' ) ) {
    return array();
  }

  $family = get_page_by_path( $slug, OBJECT, 'wp_font_family' );
  if ( ! $family instanceof WP_Post ) {
    return array();
  }

  $faces = get_posts(
    array(
		'post_type'        => 'wp_font_face',
		'post_parent'      => $family->ID,
		'post_status'      => 'any',
		'numberposts'      => -1,
		'suppress_filters' => false,
    )
  );

  $list = array();
  foreach ( $faces as $face ) {
    $settings = json_decode( (string) $face->post_content, true );
    if ( is_array( $settings ) && ! empty( $settings['src'] ) ) {
      $list[] = $settings;
    }
  }

  return $list;
}

/**
 * Convert a stored (camelCase) font-face settings array to the kebab-case
 * declarations wp_print_font_faces() expects (font-family, font-weight, src…).
 *
 * @param array<string, mixed> $face Stored font-face settings.
 * @return array<string, mixed>
 */
function mbn_font_face_to_declarations( array $face ): array {
  $declarations = array();
  foreach ( $face as $key => $value ) {
    $property                  = strtolower( (string) preg_replace( '/([a-z0-9])([A-Z])/', '$1-$2', $key ) );
    $declarations[ $property ] = $value;
  }

  if ( empty( $declarations['font-display'] ) ) {
    $declarations['font-display'] = 'swap';
  }

  return $declarations;
}

/**
 * Build the wp_print_font_faces() font definitions for the selected fonts.
 *
 * Returns an array of font-family groups, each an array of kebab-case
 * font-face declaration arrays — the shape WP_Font_Face expects.
 *
 * @return array<int, array<int, array<string, mixed>>>
 */
function mbn_get_selected_font_definitions(): array {
  $slugs = array_unique(
    array_filter(
      array(
		  mbn_normalize_font_family( mbn_setting( 'font_primary' ) ),
		  mbn_normalize_font_family( mbn_setting( 'font_secondary' ) ),
      )
    )
  );

  $fonts = array();
  foreach ( $slugs as $slug ) {
    $declarations = array();
    foreach ( mbn_get_family_font_faces( $slug ) as $face ) {
      $declaration = mbn_font_face_to_declarations( $face );
      if ( ! empty( $declaration['font-family'] ) && ! empty( $declaration['src'] ) ) {
        $declarations[] = $declaration;
      }
    }
    if ( ! empty( $declarations ) ) {
      $fonts[] = $declarations;
    }
  }

  return $fonts;
}

/**
 * Print @font-face for the selected primary/secondary fonts (front end).
 *
 * Installed Fonts Library families are not part of theme.json global settings,
 * so core's automatic wp_print_font_faces() does not emit them. Emit the
 * selected families' faces here so the chosen webfonts actually load.
 *
 * @return void
 */
function mbn_print_selected_font_faces(): void {
  if ( ! function_exists( 'wp_print_font_faces' ) ) {
    return;
  }

  $fonts = mbn_get_selected_font_definitions();
  if ( ! empty( $fonts ) ) {
    wp_print_font_faces( $fonts );
  }
}
add_action( 'wp_head', 'mbn_print_selected_font_faces', 51 );

/**
 * Selected webfont @font-face rules as a CSS string (for the editor canvas).
 *
 * @return string
 */
function mbn_get_selected_font_faces_css(): string {
  if ( ! function_exists( 'wp_print_font_faces' ) ) {
    return '';
  }

  $fonts = mbn_get_selected_font_definitions();
  if ( empty( $fonts ) ) {
    return '';
  }

  ob_start();
  wp_print_font_faces( $fonts );
  $html = (string) ob_get_clean();

  // Strip the <style> wrapper that wp_print_font_faces emits.
  return trim( (string) preg_replace( '#</?style[^>]*>#i', '', $html ) );
}

/**
 * Inject the design system (CSS variables, base typography, fallback +
 * selected webfont @font-face) into the block editor canvas iframe, so the
 * editor renders text, colors, spacing and fonts like the published page.
 *
 * Enqueued block-editor styles land in the outer editor document, not the
 * iframe; settings['styles'] entries are injected into the canvas and scoped
 * to .editor-styles-wrapper by the editor.
 *
 * @param array<string, mixed> $settings Block editor settings.
 * @return array<string, mixed>
 */
function mbn_editor_canvas_styles( $settings ) {
  if ( ! isset( $settings['styles'] ) || ! is_array( $settings['styles'] ) ) {
    $settings['styles'] = array();
  }

  $settings['styles'][] = array(
	  'css' => mbn_get_selected_font_faces_css() . mbn_build_css_variables(),
  );

  return $settings;
}
add_filter( 'block_editor_settings_all', 'mbn_editor_canvas_styles' );

/**
 * Register color schemes as the block editor color palette so blocks can reuse them.
 *
 * @return void
 */
function mbn_register_editor_color_palette(): void {
  $palette = array();
  foreach ( mbn_get_color_schemes() as $index => $hex ) {
    $palette[] = array(
		/* translators: %d: color scheme number. */
		'name'  => sprintf( __( 'Color Scheme %d', 'mbn-theme' ), $index ),
		'slug'  => 'mbn-color-scheme-' . $index,
		'color' => $hex,
    );
  }

  if ( ! empty( $palette ) ) {
    add_theme_support( 'editor-color-palette', $palette );
  }
}
add_action( 'after_setup_theme', 'mbn_register_editor_color_palette', 11 );

/**
 * Sanitize a CSS length value (size / width / radius / override percentage).
 *
 * @param string $value Raw value.
 * @return string
 */
function mbn_sanitize_css_length( $value ): string {
  $value = is_string( $value ) ? trim( $value ) : '';

  return (string) preg_replace( '/[^0-9a-zA-Z%.\-\s()_,]/', '', $value );
}

/**
 * Sanitize a font src list (allows local()/url() entries, blocks CSS injection).
 *
 * @param string $value Raw value.
 * @return string
 */
function mbn_sanitize_font_src( $value ): string {
  $value = is_string( $value ) ? trim( $value ) : '';

  return (string) preg_replace( '/[^0-9a-zA-Z\s\'"(),.\-_:\/]/', '', $value );
}

/**
 * Sanitize the submitted font-family choices against installed families.
 *
 * @param array<string, mixed> $input Raw submitted settings.
 * @return array<string, string>
 */
function mbn_sanitize_font_choices( array $input ): array {
  $families  = mbn_get_font_families();
  $primary   = (string) ( $input['font_primary'] ?? '' );
  $secondary = (string) ( $input['font_secondary'] ?? '' );

  return array(
	  'font_primary'   => isset( $families[ $primary ] ) ? $primary : '',
	  'font_secondary' => isset( $families[ $secondary ] ) ? $secondary : '',
  );
}

/**
 * Sanitize a submitted list of color-scheme hex values.
 *
 * @param mixed $colors Raw color list.
 * @return array<int, string>
 */
function mbn_sanitize_scheme_list( $colors ): array {
  $schemes = array();
  foreach ( (array) $colors as $color ) {
    $hex = sanitize_hex_color( trim( (string) $color ) );
    if ( '' !== (string) $hex ) {
      $schemes[] = $hex;
    }
  }

  return ! empty( $schemes ) ? $schemes : mbn_default_color_schemes();
}

/**
 * Sanitize the font-size settings.
 *
 * @param array<string, mixed> $input Raw submitted settings.
 * @return array<string, string>
 */
function mbn_sanitize_size_settings( array $input ): array {
  $out = array();
  foreach ( array_keys( mbn_default_font_sizes() ) as $key ) {
    $out[ 'size_' . $key ]             = mbn_sanitize_css_length( $input[ 'size_' . $key ] ?? '' );
    $out[ 'size_' . $key . '_tablet' ] = mbn_sanitize_css_length( $input[ 'size_' . $key . '_tablet' ] ?? '' );
    $out[ 'size_' . $key . '_mobile' ] = mbn_sanitize_css_length( $input[ 'size_' . $key . '_mobile' ] ?? '' );
  }

  return $out;
}

/**
 * Sanitize a single font-weight value (100–900 in 100s, or normal/bold/lighter/bolder).
 *
 * @param string $value Raw value.
 * @return string
 */
function mbn_sanitize_font_weight( $value ): string {
  $value = is_string( $value ) ? strtolower( trim( $value ) ) : '';

  if ( in_array( $value, array( 'normal', 'bold', 'lighter', 'bolder' ), true ) ) {
    return $value;
  }

  if ( preg_match( '/^[1-9]00$/', $value ) ) {
    return $value;
  }

  return '';
}

/**
 * Sanitize the font-weight settings (h1–h6 + body).
 *
 * @param array<string, mixed> $input Raw submitted settings.
 * @return array<string, string>
 */
function mbn_sanitize_weight_settings( array $input ): array {
  $out = array();
  foreach ( array_keys( mbn_default_font_weights() ) as $key ) {
    $out[ 'weight_' . $key ] = mbn_sanitize_font_weight( $input[ 'weight_' . $key ] ?? '' );
  }

  return $out;
}

/**
 * Sanitize the fallback-font settings (primary + secondary).
 *
 * @param array<string, mixed> $input Raw submitted settings.
 * @return array<string, string>
 */
function mbn_sanitize_fallback_settings( array $input ): array {
  $out = array();
  foreach ( array( 'primary', 'secondary' ) as $which ) {
    $out[ "fallback_{$which}_src" ] = mbn_sanitize_font_src( $input[ "fallback_{$which}_src" ] ?? '' );
    foreach ( array( 'size_adjust', 'ascent_override', 'descent_override', 'line_gap_override' ) as $descriptor ) {
      $out[ "fallback_{$which}_{$descriptor}" ] = mbn_sanitize_css_length( $input[ "fallback_{$which}_{$descriptor}" ] ?? '' );
    }
  }

  return $out;
}

/**
 * Sanitize the full mbn_settings array on save.
 *
 * @param mixed $input Raw submitted value.
 * @return array<string, mixed>
 */
function mbn_sanitize_settings( $input ): array {
  $input = is_array( $input ) ? $input : array();

  return array_merge(
    mbn_sanitize_font_choices( $input ),
    array( 'color_schemes' => mbn_sanitize_scheme_list( $input['color_schemes'] ?? array() ) ),
    mbn_sanitize_size_settings( $input ),
    mbn_sanitize_weight_settings( $input ),
    array(
		'container_width' => mbn_sanitize_css_length( $input['container_width'] ?? '' ),
		'border_radius'   => mbn_sanitize_css_length( $input['border_radius'] ?? '' ),
		'maps_api_key'    => sanitize_text_field( $input['maps_api_key'] ?? '' ),
    ),
    mbn_sanitize_fallback_settings( $input )
  );
}
