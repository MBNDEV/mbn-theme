<?php
/**
 * Page Sync Configuration - Domain Mapping
 *
 * Configure known domain URLs for automatic replacement during import.
 * Add all your environment URLs here.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get list of known source domains to replace during import.
 *
 * Reads from WordPress settings and builds list from local + deployment URLs.
 *
 * @return array List of source domain URLs (without trailing slash).
 */
function custom_theme_get_source_domains() {
	$local_url      = get_option( 'custom_theme_local_url', '' );
	$deployment_url = get_option( 'custom_theme_deployment_url', '' );

	$domains = array();

	// Add local URL (both http and https)
  if ( ! empty( $local_url ) ) {
      $local_url = untrailingslashit( trim( $local_url ) );
      $domains[] = $local_url;

      // Add alternate protocol
    if ( 0 === strpos( $local_url, 'https://' ) ) {
        $domains[] = str_replace( 'https://', 'http://', $local_url );
    } elseif ( 0 === strpos( $local_url, 'http://' ) ) {
        $domains[] = str_replace( 'http://', 'https://', $local_url );
    }
  }

	// Add deployment URL (both http and https)
  if ( ! empty( $deployment_url ) ) {
      $deployment_url = untrailingslashit( trim( $deployment_url ) );
      $domains[]      = $deployment_url;

      // Add alternate protocol
    if ( 0 === strpos( $deployment_url, 'https://' ) ) {
        $domains[] = str_replace( 'https://', 'http://', $deployment_url );
    } elseif ( 0 === strpos( $deployment_url, 'http://' ) ) {
        $domains[] = str_replace( 'http://', 'https://', $deployment_url );
    }
  }

	// If no custom URLs set, return default fallback
  if ( empty( $domains ) ) {
      return array(
          'https://mysite.dev.local',
          'http://mysite.dev.local',
      );
  }

	return array_unique( $domains );
}

/**
 * Get file path patterns to exclude from domain replacement.
 *
 * These patterns will NOT be replaced even if they contain a matching domain.
 * Useful for preserving external links or specific URLs.
 *
 * @return array List of URL patterns to exclude (regex patterns).
 */
function custom_theme_get_domain_replacement_exclusions() {
	return array(
		// Example: Don't replace external API URLs
		// '/api\.example\.com/i',

		// Example: Don't replace specific external links
		// '/external\.com\/specific-path/i',
	);
}
