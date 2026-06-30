<?php
/**
 * Media uploads: allow video + SVG, with SVG security validation + sanitization.
 *
 * SVG is an XML format that can carry scripts and external references, so it is
 * never trusted as-is: uploads are restricted to users who can already upload,
 * each file is parsed and sanitized (scripts, event handlers and external/JS
 * references stripped), and anything that cannot be parsed is rejected.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Allowed extra MIME types: video formats + SVG.
 *
 * @param array<string,string> $mime_types Allowed MIME types.
 * @return array<string,string>
 */
function mbn_allowed_upload_mimes( $mime_types ) {
  $mime_types['mp4']  = 'video/mp4';
  $mime_types['m4v']  = 'video/mp4';
  $mime_types['mov']  = 'video/quicktime';
  $mime_types['webm'] = 'video/webm';
  $mime_types['ogv']  = 'video/ogg';

  // SVG only for users allowed to upload (sanitized on the prefilter below).
  if ( current_user_can( 'upload_files' ) ) {
    $mime_types['svg']  = 'image/svg+xml';
    $mime_types['svgz'] = 'image/svg+xml';
  }

  return $mime_types;
}
add_filter( 'upload_mimes', 'mbn_allowed_upload_mimes' );

/**
 * Let SVG pass WordPress's real-MIME / extension check.
 *
 * Core sniffs file contents and rejects SVG when the detected type does not match;
 * map the SVG extension to the correct type/ext explicitly (the contents are
 * sanitized in the prefilter, so this does not weaken security on its own).
 *
 * @param array<string,mixed> $data     ext/type/proper_filename result.
 * @param string              $file     Full path to the uploaded file.
 * @param string              $filename Original filename.
 * @return array<string,mixed>
 */
function mbn_check_svg_filetype( $data, $file, $filename ) {
  unset( $file );
  $ext = strtolower( (string) pathinfo( $filename, PATHINFO_EXTENSION ) );

  if ( 'svg' === $ext || 'svgz' === $ext ) {
    $data['ext']  = 'svg';
    $data['type'] = 'image/svg+xml';
  }

  return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'mbn_check_svg_filetype', 10, 3 );

/**
 * Validate + sanitize an SVG file on upload; reject unsafe/unparseable files.
 *
 * @param array<string,string> $file Pre-filter upload array (name, type, tmp_name…).
 * @return array<string,string>
 */
function mbn_sanitize_svg_upload( $file ) {
  $is_svg = ( isset( $file['type'] ) && 'image/svg+xml' === $file['type'] )
    || ( isset( $file['name'] ) && preg_match( '/\.svgz?$/i', (string) $file['name'] ) );

  if ( ! $is_svg ) {
    return $file;
  }

  if ( ! current_user_can( 'upload_files' ) ) {
    $file['error'] = __( 'You are not allowed to upload SVG files.', 'mbn-theme' );
    return $file;
  }

  $tmp = $file['tmp_name'] ?? '';
  if ( '' === $tmp || ! file_exists( $tmp ) ) {
    return $file;
  }

  $svg = (string) file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local temp upload file.

  // Gzipped SVGZ: inflate for sanitizing, re-store as plain SVG.
  if ( "\x1f\x8b" === substr( $svg, 0, 2 ) && function_exists( 'gzdecode' ) ) {
    $svg = (string) gzdecode( $svg );
  }

  $clean = mbn_sanitize_svg_markup( $svg );

  if ( '' === $clean ) {
    $file['error'] = __( 'This SVG could not be validated and was rejected.', 'mbn-theme' );
    return $file;
  }

  file_put_contents( $tmp, $clean ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- sanitizing a local temp upload file before WP stores it.

  return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'mbn_sanitize_svg_upload' );
add_filter( 'wp_handle_sideload_prefilter', 'mbn_sanitize_svg_upload' );

/**
 * Whether an SVG attribute is unsafe and should be removed.
 *
 * @param string $name  Lower-cased attribute name.
 * @param string $value Trimmed attribute value.
 * @return bool
 */
function mbn_svg_attr_is_disallowed( string $name, string $value ): bool {
  if ( 0 === strpos( $name, 'on' ) ) {
    return true;
  }

  if ( in_array( $name, array( 'href', 'xlink:href' ), true ) ) {
    // Allow only same-document fragments and safe image data URIs.
    $is_fragment = '' !== $value && '#' === $value[0];
    $is_safe_img = (bool) preg_match( '#^data:image/(png|jpe?g|gif|webp);base64,#i', $value );
    return ! $is_fragment && ! $is_safe_img;
  }

  if ( 'style' === $name && preg_match( '/expression\s*\(|javascript:|url\s*\(\s*[\'"]?\s*javascript:/i', $value ) ) {
    return true;
  }

  return (bool) preg_match( '/^(javascript:|data:text\/html)/i', $value );
}

/**
 * Sanitize raw SVG markup: strip scripts, event handlers and JS/external refs.
 *
 * @param string $svg Raw SVG XML.
 * @return string Sanitized SVG, or '' when it cannot be parsed / has no <svg>.
 */
function mbn_sanitize_svg_markup( $svg ) {
  // phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- native PHP DOM API properties.
  $svg = trim( (string) $svg );
  if ( '' === $svg || false === stripos( $svg, '<svg' ) ) {
    return '';
  }

  // Drop DOCTYPE/ENTITY (XXE / billion-laughs vectors) before parsing.
  if ( preg_match( '/<!DOCTYPE|<!ENTITY/i', $svg ) ) {
    return '';
  }

  // DOCTYPE/ENTITY already rejected above; LIBXML_NONET blocks network entities.
  $previous = libxml_use_internal_errors( true );

  $dom                     = new DOMDocument();
  $dom->preserveWhiteSpace = false;
  $loaded                  = $dom->loadXML( $svg, LIBXML_NONET | LIBXML_NOENT | LIBXML_NOERROR | LIBXML_NOWARNING );
  libxml_clear_errors();
  libxml_use_internal_errors( $previous );

  if ( ! $loaded || ! $dom->documentElement || 'svg' !== strtolower( $dom->documentElement->localName ) ) {
    return '';
  }

  $disallowed_tags = array( 'script', 'foreignobject', 'iframe', 'embed', 'object', 'audio', 'video', 'animatescript', 'handler', 'listener' );

  $xpath = new DOMXPath( $dom );

  // Remove disallowed elements.
  foreach ( iterator_to_array( $xpath->query( '//*' ) ) as $node ) {
    if ( in_array( strtolower( $node->localName ), $disallowed_tags, true ) ) {
      $node->parentNode->removeChild( $node );
    }
  }

  // Remove disallowed attributes (event handlers + JS/external refs).
  foreach ( iterator_to_array( $xpath->query( '//@*' ) ) as $attr ) {
    if ( mbn_svg_attr_is_disallowed( strtolower( $attr->nodeName ), trim( (string) $attr->nodeValue ) ) ) {
      $attr->ownerElement->removeAttributeNode( $attr );
    }
  }

  $out = $dom->saveXML( $dom->documentElement );

  return is_string( $out ) ? $out : '';
}
