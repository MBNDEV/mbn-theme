<?php
/**
 * Preset Options (Appearance): typography presets, Google Fonts, CSS variables (--cbb-font-*, --cbb-accent-*), and enqueue hooks.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Registered font presets (slug => label, full CSS stack, optional Google Fonts spec).
 *
 * Stacks pair each face with a very close generic fallback (same class: humanist sans,
 * neo-grotesque, transitional serif, etc.).
 *
 * @return array<string, array{label: string, stack: string, google: null|array{name: string, axis: string}}>
 */
function custom_theme_get_font_presets(): array {
  return array(
	  'system_sans'      => array(
		  'label'  => __( 'System UI (sans-serif)', 'mbn-theme' ),
		  'stack'  => 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
		  'google' => null,
	  ),
	  'system_serif'     => array(
		  'label'  => __( 'System UI (serif)', 'mbn-theme' ),
		  'stack'  => 'ui-serif, Georgia, Cambria, "Times New Roman", Times, serif',
		  'google' => null,
	  ),
	  'inter'            => array(
		  'label'  => __( 'Inter', 'mbn-theme' ),
		  'stack'  => '"Inter", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
		  'google' => array(
			  'name' => 'Inter',
			  'axis' => 'wght@300;400;500;600;700',
		  ),
	  ),
	  'open_sans'        => array(
		  'label'  => __( 'Open Sans', 'mbn-theme' ),
		  'stack'  => '"Open Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
		  'google' => array(
			  'name' => 'Open Sans',
			  'axis' => 'wght@300;400;600;700',
		  ),
	  ),
	  'source_sans_3'    => array(
		  'label'  => __( 'Source Sans 3', 'mbn-theme' ),
		  'stack'  => '"Source Sans 3", "Source Sans Pro", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif',
		  'google' => array(
			  'name' => 'Source Sans 3',
			  'axis' => 'wght@300;400;600;700',
		  ),
	  ),
	  'roboto'           => array(
		  'label'  => __( 'Roboto', 'mbn-theme' ),
		  'stack'  => 'Roboto, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", Arial, sans-serif',
		  'google' => array(
			  'name' => 'Roboto',
			  'axis' => 'wght@300;400;500;700',
		  ),
	  ),
	  'work_sans'        => array(
		  'label'  => __( 'Work Sans', 'mbn-theme' ),
		  'stack'  => '"Work Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif',
		  'google' => array(
			  'name' => 'Work Sans',
			  'axis' => 'wght@300;400;600;700',
		  ),
	  ),
	  'titillium_web'    => array(
		  'label'  => __( 'Titillium Web', 'mbn-theme' ),
		  'stack'  => '"Titillium Web", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif',
		  'google' => array(
			  'name' => 'Titillium Web',
			  'axis' => 'wght@300;400;600;700',
		  ),
	  ),
	  'merriweather'     => array(
		  'label'  => __( 'Merriweather', 'mbn-theme' ),
		  'stack'  => 'Merriweather, ui-serif, Georgia, Cambria, "Times New Roman", Times, serif',
		  'google' => array(
			  'name' => 'Merriweather',
			  'axis' => 'wght@300;400;700',
		  ),
	  ),
	  'lora'             => array(
		  'label'  => __( 'Lora', 'mbn-theme' ),
		  'stack'  => 'Lora, ui-serif, Georgia, Cambria, "Times New Roman", Times, serif',
		  'google' => array(
			  'name' => 'Lora',
			  'axis' => 'wght@400;500;600;700',
		  ),
	  ),
	  'playfair_display' => array(
		  'label'  => __( 'Playfair Display', 'mbn-theme' ),
		  'stack'  => '"Playfair Display", ui-serif, Georgia, "Times New Roman", Times, serif',
		  'google' => array(
			  'name' => 'Playfair Display',
			  'axis' => 'wght@400;600;700',
		  ),
	  ),
  );
}

/**
 * Alias for compatibility with new theme options page
 *
 * @return array
 */
function blgf_get_font_presets(): array {
	return custom_theme_get_font_presets();
}

/**
 * Normalize a stored slug to a valid preset key.
 *
 * @param mixed  $slug Raw option value.
 * @param string $fallback Default slug when missing or invalid.
 * @return string
 */
function custom_theme_normalize_font_slug( $slug, string $fallback = 'inter' ): string {
  $presets = custom_theme_get_font_presets();
  if ( is_string( $slug ) && isset( $presets[ $slug ] ) ) {
    return $slug;
  }

  return isset( $presets[ $fallback ] ) ? $fallback : 'system_sans';
}

/**
 * Build Google Fonts CSS2 URL for all distinct webfonts needed by the given slugs.
 *
 * @param array<int, string> $slugs Preset slugs (e.g. primary + secondary).
 * @return string Empty string when no webfonts required.
 */
function custom_theme_build_google_fonts_url( array $slugs ): string {
  $presets = custom_theme_get_font_presets();
  $needed  = array();

  foreach ( $slugs as $slug ) {
    if ( ! is_string( $slug ) || ! isset( $presets[ $slug ]['google'] ) || ! is_array( $presets[ $slug ]['google'] ) ) {
      continue;
    }
    $name = $presets[ $slug ]['google']['name'] ?? '';
    $axis = $presets[ $slug ]['google']['axis'] ?? '';
    if ( '' === $name || '' === $axis ) {
      continue;
    }
    if ( isset( $needed[ $name ] ) ) {
      continue;
    }
    $needed[ $name ] = $axis;
  }

  if ( empty( $needed ) ) {
    return '';
  }

  $pairs = array();
  foreach ( $needed as $name => $axis ) {
    $family  = str_replace( ' ', '+', $name );
    $pairs[] = 'family=' . $family . ':' . $axis;
  }

  return 'https://fonts.googleapis.com/css2?' . implode( '&', $pairs ) . '&display=swap';
}

/**
 * Sanitize a hex color from theme options; fall back when empty or invalid.
 *
 * @param mixed  $color    Raw option value.
 * @param string $fallback Default #rrggbb or #rgb.
 * @return string
 */
function custom_theme_sanitize_hex_color_or_default( $color, string $fallback ): string {
  $color = is_string( $color ) ? trim( $color ) : '';
  $san   = sanitize_hex_color( $color );
  if ( '' !== $san ) {
    return $san;
  }
  $fb = sanitize_hex_color( $fallback );
  return '' !== $fb ? $fb : '#2563EB';
}

/**
 * Inline CSS: variables on :root (fonts + accent colors) and application to headings / body.
 * Updated to work with native WordPress options
 *
 * @param string $heading_slug Preset slug for headings.
 * @param string $body_slug Preset slug for body.
 * @return string
 */
function custom_theme_get_font_css_rules( string $heading_slug, string $body_slug ): string {
  $presets       = custom_theme_get_font_presets();
  $heading_slug  = custom_theme_normalize_font_slug( $heading_slug, 'inter' );
  $body_slug     = custom_theme_normalize_font_slug( $body_slug, 'system_sans' );
  $heading_stack = $presets[ $heading_slug ]['stack'] ?? $presets['system_sans']['stack'];
  $body_stack    = $presets[ $body_slug ]['stack'] ?? $presets['system_sans']['stack'];

  $raw_primary      = get_option( 'blgf_primary_accent_color', '#2563EB' );
  $primary_accent   = custom_theme_sanitize_hex_color_or_default( $raw_primary, '#2563EB' );
  $secondary_accent = custom_theme_sanitize_hex_color_or_default( get_option( 'blgf_secondary_accent_color', '#64748B' ), '#64748B' );

  $css  = ':root{';
  $css .= '--cbb-font-heading:' . $heading_stack . ';';
  $css .= '--cbb-font-body:' . $body_stack . ';';
  $css .= '--cbb-accent-primary:' . $primary_accent . ';';
  $css .= '--cbb-accent-secondary:' . $secondary_accent . ';';
  $css .= '}';
  $css .= 'body{font-family:var(--cbb-font-body);}';
  $css .= 'h1,h2,h3,h4,h5,h6{font-family:var(--cbb-font-heading);}';

  return $css;
}

/**
 * Current heading/body font slugs from theme options.
 * Updated to work with native WordPress options
 *
 * @return array{0: string, 1: string}
 */
function custom_theme_get_current_font_slugs(): array {
  $heading = custom_theme_normalize_font_slug( get_option( 'blgf_font_primary', 'inter' ), 'inter' );
  $body    = custom_theme_normalize_font_slug( get_option( 'blgf_font_secondary', 'system_sans' ), 'system_sans' );

  return array( $heading, $body );
}

/**
 * Enqueue Google Fonts stylesheet before the main theme CSS when webfonts are selected.
 *
 * @return void
 */
function custom_theme_enqueue_google_fonts_stylesheet(): void {
  if ( ! wp_style_is( 'custom-theme-tailwind', 'registered' ) ) {
    return;
  }

  list( $heading_slug, $body_slug ) = custom_theme_get_current_font_slugs();
  $font_url                         = custom_theme_build_google_fonts_url( array( $heading_slug, $body_slug ) );

  if ( '' === $font_url ) {
    return;
  }

  wp_enqueue_style(
    'custom-theme-google-fonts',
    esc_url( $font_url ),
    array(),
    md5( $font_url )
  );
}

/**
 * Attach heading/body font-family rules to the Tailwind handle (must run after Tailwind is enqueued).
 *
 * @return void
 */
function custom_theme_add_font_family_inline_style(): void {
  if ( ! wp_style_is( 'custom-theme-tailwind', 'enqueued' ) ) {
    return;
  }

  list( $heading_slug, $body_slug ) = custom_theme_get_current_font_slugs();

  wp_add_inline_style(
    'custom-theme-tailwind',
    custom_theme_get_font_css_rules( $heading_slug, $body_slug )
  );
}

/**
 * Front end: load webfonts before Tailwind (priority 7 vs 8).
 *
 * @return void
 */
function custom_theme_enqueue_google_fonts_front(): void {
  if ( is_admin() ) {
    return;
  }
  custom_theme_enqueue_google_fonts_stylesheet();
}
add_action( 'wp_enqueue_scripts', 'custom_theme_enqueue_google_fonts_front', 7 );

/**
 * Front end: typography variables after Tailwind is enqueued (priority 8).
 *
 * @return void
 */
function custom_theme_add_font_inline_front(): void {
  if ( is_admin() ) {
    return;
  }
  custom_theme_add_font_family_inline_style();
}
add_action( 'wp_enqueue_scripts', 'custom_theme_add_font_inline_front', 9 );

/**
 * Block editor: webfonts before Tailwind (priority 9 vs 10).
 *
 * @return void
 */
function custom_theme_enqueue_google_fonts_editor(): void {
  custom_theme_enqueue_google_fonts_stylesheet();
}
add_action( 'enqueue_block_editor_assets', 'custom_theme_enqueue_google_fonts_editor', 9 );

/**
 * Block editor: typography after Tailwind editor enqueue (priority 11).
 *
 * @return void
 */
function custom_theme_add_font_inline_editor(): void {
  custom_theme_add_font_family_inline_style();
}
add_action( 'enqueue_block_editor_assets', 'custom_theme_add_font_inline_editor', 11 );

/**
 * Preconnect to Google Fonts hosts when a webfont preset is active.
 *
 * @param array<int, string|array<string, string>> $urls          URLs to print for resource hints.
 * @param string                                   $relation_type The relation type the URLs are printed for.
 * @return array<int, string|array<string, string>>
 */
function custom_theme_font_google_resource_hints( $urls, $relation_type ) {
  if ( 'preconnect' !== $relation_type || ! is_array( $urls ) ) {
    return $urls;
  }

  $heading_slug = custom_theme_normalize_font_slug( get_option( 'blgf_font_primary', 'inter' ), 'inter' );
  $body_slug    = custom_theme_normalize_font_slug( get_option( 'blgf_font_secondary', 'system_sans' ), 'system_sans' );
  $font_url     = custom_theme_build_google_fonts_url( array( $heading_slug, $body_slug ) );

  if ( '' === $font_url ) {
    return $urls;
  }

  $urls[] = 'https://fonts.googleapis.com';
  $urls[] = array(
	  'href'        => 'https://fonts.gstatic.com',
	  'crossorigin' => 'anonymous',
  );

  return $urls;
}
add_filter( 'wp_resource_hints', 'custom_theme_font_google_resource_hints', 10, 2 );
