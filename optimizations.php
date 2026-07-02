<?php
/**
 * Front-end performance optimizations.
 *
 * On anonymous front-end requests the whole page is buffered and rewritten to:
 *   1. Defer the Fonts Library inline CSS (wp-fonts-local) — re-applied on window
 *      load so fonts load late (the metric-matched fallback shows first, minimal
 *      shift).
 *   2. Defer scripts — external scripts get `defer`; inline scripts with no type
 *      (or text/javascript) are turned into `type="lazyload"` and re-run after the
 *      page is interactive, in order, as fresh inline scripts (CSP-safe, no Blob
 *      URLs) by assets/js/mbn-lazyload.js.
 *   3. Defer non-theme stylesheets — third-party/plugin stylesheets load
 *      non-blocking (`media="print"` → `all` on load); the theme's own styles
 *      (inline and its stylesheet) are left as-is to avoid a flash of unstyled
 *      content.
 *   4. Minify inline `<style>` CSS — strip comments + collapse whitespace
 *      (calc()/combinators preserved).
 *
 * It also sends security headers (site-wide) and Cache-Control — public/cacheable
 * for anonymous visitors, private/no-cache for logged-in users.
 *
 * The page rewrite is skipped in the admin, editor, customizer, AJAX/REST/feeds and
 * for logged-in users (so the toolbar and editor are untouched). Toggle with the
 * `mbn_enable_optimizations` filter.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Send security headers on the front end (filterable via `mbn_security_headers`).
 * HSTS is only sent over HTTPS. The CSP is the minimal `upgrade-insecure-requests`
 * — it does NOT restrict `script-src`, so the (now Blob-free) inline deferred
 * scripts run fine.
 *
 * @return void
 */
function mbn_send_security_headers(): void {
  if ( is_admin() || headers_sent() ) {
    return;
  }

  $headers = array(
	  'X-XSS-Protection'        => '1; mode=block',
	  'X-Frame-Options'         => 'SAMEORIGIN',
	  'X-Content-Type-Options'  => 'nosniff',
	  'Referrer-Policy'         => 'strict-origin-when-cross-origin',
	  'Content-Security-Policy' => 'upgrade-insecure-requests;',
	  'Permissions-Policy'      => 'camera=self, microphone=self',
  );
  if ( is_ssl() ) {
    $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
  }

  foreach ( (array) apply_filters( 'mbn_security_headers', $headers ) as $name => $value ) {
    if ( '' !== (string) $value ) {
      header( $name . ': ' . $value );
    }
  }
}
add_action( 'send_headers', 'mbn_send_security_headers' );

/**
 * Cache-Control: public/cacheable for anonymous GET requests, private/no-store for
 * logged-in users (and POST, previews, 404, search, feeds). The anonymous TTL is
 * filterable via `mbn_cache_max_age` (seconds; 0 disables public caching).
 *
 * @return void
 */
function mbn_send_cache_headers(): void {
  if ( is_admin() || headers_sent() ) {
    return;
  }

  // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only request-method check, not form processing.
  $method   = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
  $no_cache = defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE;
  $is_anon  = ! is_user_logged_in() && in_array( $method, array( 'GET', 'HEAD' ), true ) && ! $no_cache
    && ! is_preview() && ! is_404() && ! is_search() && ! is_feed();

  if ( $is_anon ) {
    $max_age = (int) apply_filters( 'mbn_cache_max_age', 600 );
    if ( $max_age > 0 ) {
      header( 'Cache-Control: public, max-age=' . $max_age . ', stale-while-revalidate=60' );
      return;
    }
  }

  header( 'Cache-Control: no-cache, no-store, must-revalidate, private' );
  header( 'Pragma: no-cache' );
}
add_action( 'send_headers', 'mbn_send_cache_headers', 11 );

/**
 * Auto-WebP: generate the intermediate image sizes as WebP for new JPEG/PNG
 * uploads, so wp_get_attachment_image() serves WebP (with a smaller payload)
 * automatically. The original file keeps its format. Run `wp media regenerate`
 * to convert existing images.
 *
 * @param array<string, string> $formats Mime => output mime map.
 * @return array<string, string>
 */
function mbn_webp_output_format( array $formats ): array {
  $formats['image/jpeg'] = 'image/webp';
  $formats['image/png']  = 'image/webp';
  return $formats;
}
add_filter( 'image_editor_output_format', 'mbn_webp_output_format' );

/**
 * Swap uploads JPEG/PNG image URLs for a WebP sibling when one exists on disk
 * (covers images referenced by raw URL / older content). Cached per file.
 *
 * @param string $html Page HTML.
 * @return string
 */
function mbn_optimize_webp( string $html ): string {
  $uploads  = wp_get_upload_dir();
  $base_url = $uploads['baseurl'];
  $base_dir = $uploads['basedir'];
  if ( empty( $base_url ) || empty( $base_dir ) ) {
    return $html;
  }

  return (string) preg_replace_callback(
    '#' . preg_quote( $base_url, '#' ) . '/[^"\'\s]+?\.(?:jpe?g|png)\b#i',
    static function ( $matches ) use ( $base_url, $base_dir ) {
      static $cache = array();
      $url          = $matches[0];
      $webp_url     = preg_replace( '#\.(?:jpe?g|png)$#i', '.webp', $url );
      $file         = $base_dir . substr( $webp_url, strlen( $base_url ) );
      if ( ! isset( $cache[ $file ] ) ) {
        $cache[ $file ] = file_exists( $file );
      }
      return $cache[ $file ] ? $webp_url : $url;
    },
    $html
  );
}

/**
 * Whether to optimize the current request.
 *
 * @return bool
 */
function mbn_should_optimize(): bool {
  if ( is_admin() || wp_doing_ajax() || is_feed() ) {
    return false;
  }
  if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
    return false;
  }
  if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {
    return false;
  }
  if ( is_user_logged_in() ) {
    return false;
  }
  return (bool) apply_filters( 'mbn_enable_optimizations', true );
}

/**
 * Start buffering the page so it can be rewritten on flush.
 *
 * @return void
 */
function mbn_start_optimization_buffer(): void {
  if ( mbn_should_optimize() ) {
    ob_start( 'mbn_optimize_html' );
  }
}
add_action( 'template_redirect', 'mbn_start_optimization_buffer', 1 );

/**
 * Enqueue the small loader that runs the deferred styles/scripts.
 *
 * @return void
 */
function mbn_enqueue_lazyloader(): void {
  if ( ! mbn_should_optimize() ) {
    return;
  }
  $rel  = 'assets/js/mbn-lazyload.js';
  $path = get_theme_file_path( $rel );
  if ( file_exists( $path ) ) {
    wp_enqueue_script( 'mbn-lazyload', get_theme_file_uri( $rel ), array(), (string) filemtime( $path ), true );
  }
}
add_action( 'wp_enqueue_scripts', 'mbn_enqueue_lazyloader' );

/**
 * Defer the Fonts Library inline CSS (and similar webfont inline styles).
 *
 * @param string $html Page HTML.
 * @return string
 */
function mbn_optimize_fonts( string $html ): string {
  return (string) preg_replace_callback(
    '#<style\b([^>]*\b(?:id|class)=["\'][^"\']*(?:fonts-local|webfonts)[^"\']*["\'][^>]*)>(.*?)</style>#is',
    static function ( $matches ) {
      $attrs = preg_replace( '#\btype=["\'][^"\']*["\']#i', '', $matches[1] );
      return '<style' . $attrs . ' type="text/lazystyle" data-mbn-lazystyle="font">' . $matches[2] . '</style>';
    },
    $html
  );
}

/**
 * Add `defer` to external scripts that aren't already async/defer.
 *
 * @param string $html Page HTML.
 * @return string
 */
function mbn_optimize_external_scripts( string $html ): string {
  return (string) preg_replace_callback(
    '#<script\b([^>]*\bsrc=["\'][^"\']+["\'][^>]*)>#is',
    static function ( $matches ) {
      if ( preg_match( '#\b(?:defer|async)\b#i', $matches[1] ) ) {
        return $matches[0];
      }
      // wp-i18n / wp-hooks must stay synchronous: WordPress prints inline
      // `wp.i18n.*` after-scripts that run immediately (e.g. Gravity Forms).
      if ( preg_match( '#wp-includes/js/dist/(?:i18n|hooks)\.min\.js#i', $matches[1] ) ) {
        return $matches[0];
      }
      return '<script' . $matches[1] . ' defer>';
    },
    $html
  );
}

/**
 * Turn executable inline scripts into `type="lazyload"` so the loader can run
 * them after the page is interactive.
 *
 * @param string $html Page HTML.
 * @return string
 */
function mbn_optimize_inline_scripts( string $html ): string {
  return (string) preg_replace_callback(
    '#<script\b([^>]*)>(.*?)</script>#is',
    static function ( $matches ) {
      $attrs   = $matches[1];
      $content = $matches[2];
      if ( preg_match( '#\bsrc=#i', $attrs ) || '' === trim( $content ) ) {
        return $matches[0];
      }
      // Keep WordPress inline data (localized vars / before / after / translations)
      // synchronous — the deferred external scripts read it at run time.
      if ( preg_match( '#\bid=["\'][^"\']*-js-(?:before|after|extra|translations)["\']#i', $attrs ) ) {
        return $matches[0];
      }
      // Only no-type or (text|application)/javascript inline scripts.
      if ( preg_match( '#\btype=["\']([^"\']+)["\']#i', $attrs, $type_match ) ) {
        $type = strtolower( trim( $type_match[1] ) );
        if ( 'text/javascript' !== $type && 'application/javascript' !== $type ) {
          return $matches[0];
        }
        $attrs = preg_replace( '#\btype=["\'][^"\']*["\']#i', '', $attrs );
      }
      return '<script' . $attrs . ' type="lazyload">' . $content . '</script>';
    },
    $html
  );
}

/**
 * Defer non-theme stylesheets (load them non-blocking, apply on load).
 *
 * @param string $html Page HTML.
 * @return string
 */
function mbn_optimize_styles( string $html ): string {
  $theme_uri = preg_quote( get_template_directory_uri(), '#' );
  return (string) preg_replace_callback(
    '#<link\b([^>]*\brel=["\']stylesheet["\'][^>]*)>#is',
    static function ( $matches ) use ( $theme_uri ) {
      $attrs = $matches[1];
      // Keep the theme's own stylesheets and anything already print-scoped.
      if ( preg_match( '#href=["\']' . $theme_uri . '#i', $attrs ) || preg_match( '#\bmedia=["\']print["\']#i', $attrs ) ) {
        return $matches[0];
      }
      $attrs = preg_replace( '#\bmedia=["\'][^"\']*["\']#i', '', $attrs );
      return '<link' . $attrs . ' media="print" onload="this.media=\'all\'">';
    },
    $html
  );
}

/**
 * Minify a block of CSS — safely. Strips comments and collapses whitespace runs to
 * a single space (so descendant combinators like `div p` survive), then tightens
 * the space around `{ } ; ,`. It deliberately does NOT touch spacing around `:`,
 * `+`, `-`, `>` or `~`, so `calc(100% - 1rem)` and combinators are not broken.
 *
 * @param string $css CSS source.
 * @return string
 */
function mbn_minify_css( string $css ): string {
  $css = (string) preg_replace( '#/\*(?!!)[\s\S]*?\*/#', '', $css ); // drop comments (keep /*! */).
  $css = (string) preg_replace( '/\s+/', ' ', $css );                // collapse whitespace.
  $css = (string) preg_replace( '/\s*([{};,])\s*/', '$1', $css );    // tighten around structural tokens.
  $css = str_replace( ';}', '}', $css );                             // drop the final semicolon in a rule.
  return trim( $css );
}

/**
 * Rewrite the buffered page HTML with the optimizations.
 *
 * @param string $html Page HTML.
 * @return string
 */
function mbn_optimize_html( $html ): string {
  $html = (string) $html;
  if ( false === stripos( $html, '</body>' ) ) {
    return $html;
  }
  $html = mbn_optimize_fonts( $html );
  $html = mbn_optimize_external_scripts( $html );
  $html = mbn_optimize_inline_scripts( $html );
  $html = mbn_optimize_styles( $html );
  $html = mbn_optimize_webp( $html );
  return $html;
}
