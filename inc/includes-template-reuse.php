<?php
/**
 * Remote Block Template reuse tools.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get configured remote template sites.
 *
 * @return array<int, array{site_name:string, home_url:string, application_password:string}>
 */
function custom_theme_get_template_reuse_sites(): array {
	$sites = get_option( 'blgf_template_reuse_sites', array() );

  if ( ! is_array( $sites ) ) {
      return array();
  }

	$normalized_sites = array();
  foreach ( $sites as $site ) {
    if ( ! is_array( $site ) ) {
        continue;
    }

      $site_name            = sanitize_text_field( $site['site_name'] ?? '' );
      $home_url             = esc_url_raw( $site['home_url'] ?? '' );
      $application_password = sanitize_text_field( $site['application_password'] ?? '' );

    if ( '' === $site_name || '' === $home_url || '' === $application_password ) {
        continue;
    }

      $normalized_sites[] = array(
          'site_name'            => $site_name,
          'home_url'             => $home_url,
          'application_password' => $application_password,
      );
  }

	return $normalized_sites;
}

/**
 * Sanitize remote template site settings.
 *
 * @param mixed $value Submitted option value.
 * @return array<int, array{site_name:string, home_url:string, application_password:string}>
 */
function custom_theme_sanitize_template_reuse_sites( $value ): array {
  if ( ! is_array( $value ) ) {
      return array();
  }

	$sites = array();
  foreach ( $value as $site ) {
    if ( ! is_array( $site ) ) {
        continue;
    }

      $site_name            = sanitize_text_field( $site['site_name'] ?? '' );
      $home_url             = esc_url_raw( $site['home_url'] ?? '' );
      $application_password = sanitize_text_field( $site['application_password'] ?? '' );

    if ( '' === $site_name && '' === $home_url && '' === $application_password ) {
        continue;
    }

      $sites[] = array(
          'site_name'            => $site_name,
          'home_url'             => $home_url,
          'application_password' => $application_password,
      );
  }

	return $sites;
}

/**
 * Build an Authorization header value from the configured credential.
 *
 * Accepts either a full "Basic ..." or "Bearer ..." value, or a raw
 * "username:application-password" value for WordPress Application Passwords.
 *
 * @param string $application_password Stored credential value.
 * @return string
 */
function custom_theme_template_reuse_authorization_header( string $application_password ): string {
	$application_password = trim( $application_password );

  if ( 0 === stripos( $application_password, 'Basic ' ) || 0 === stripos( $application_password, 'Bearer ' ) ) {
      return $application_password;
  }

	return 'Basic ' . base64_encode( $application_password ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
}

/**
 * Determine whether a stored credential can be sent as an authorization value.
 *
 * @param string $application_password Stored credential value.
 * @return bool
 */
function custom_theme_template_reuse_has_valid_authorization_value( string $application_password ): bool {
	$application_password = trim( $application_password );

  if ( '' === $application_password ) {
      return false;
  }

  if ( 0 === stripos( $application_password, 'Basic ' ) || 0 === stripos( $application_password, 'Bearer ' ) ) {
      return true;
  }

	return false !== strpos( $application_password, ':' );
}

/**
 * Check whether the local Docker nginx service is resolvable.
 *
 * @return bool
 */
function custom_theme_template_reuse_has_local_nginx_proxy(): bool {
	return 'infra-nginx' !== gethostbyname( 'infra-nginx' );
}

/**
 * Check whether a host should be routed through the local Docker nginx service.
 *
 * @param string $host Remote URL host.
 * @return bool
 */
function custom_theme_template_reuse_should_proxy_local_dev_host( string $host ): bool {
	$suffix = '.dev.local';

	return strlen( $host ) > strlen( $suffix )
		&& substr( $host, -strlen( $suffix ) ) === $suffix
		&& custom_theme_template_reuse_has_local_nginx_proxy();
}

/**
 * Build request data for a remote block template endpoint.
 *
 * @param array{home_url:string,application_password:string} $site Remote site config.
 * @return array{endpoint:string,args:array<string,mixed>}
 */
function custom_theme_template_reuse_build_remote_request( array $site ): array {
	$home_url = $site['home_url'];
	$headers  = array(
		'Authorization' => custom_theme_template_reuse_authorization_header( $site['application_password'] ),
		'Accept'        => 'application/json',
	);
	$endpoint = trailingslashit( $home_url ) . 'wp-json/mbn-theme/v1/block-templates';
	$parsed   = wp_parse_url( $home_url );
	$host     = is_array( $parsed ) && isset( $parsed['host'] ) ? $parsed['host'] : '';

	if ( '' !== $host && custom_theme_template_reuse_should_proxy_local_dev_host( $host ) ) {
		$path       = isset( $parsed['path'] ) ? untrailingslashit( $parsed['path'] ) : '';
		$host_value = $host;

      if ( isset( $parsed['port'] ) ) {
          $host_value .= ':' . absint( $parsed['port'] );
      }

		$headers['Host'] = $host_value;
		$endpoint        = 'https://infra-nginx' . $path . '/wp-json/mbn-theme/v1/block-templates';
	}

	$args = array(
		'timeout' => 15,
		'headers' => $headers,
	);

	if ( isset( $headers['Host'] ) ) {
		$args['sslverify'] = false;
	}

	return array(
		'endpoint' => $endpoint,
		'args'     => $args,
	);
}

/**
 * Extract a readable remote error message from a response payload.
 *
 * @param int    $status_code Remote HTTP status code.
 * @param string $body Remote response body.
 * @return string
 */
function custom_theme_template_reuse_remote_error_message( int $status_code, string $body ): string {
	$payload = json_decode( $body, true );

  if ( is_array( $payload ) && isset( $payload['message'] ) && is_string( $payload['message'] ) ) {
      return sprintf(
          /* translators: 1: HTTP status code, 2: remote error message. */
        __( 'Remote site returned HTTP %1$d: %2$s', 'mbn-theme' ),
        $status_code,
        $payload['message']
      );
  }

	$text = trim( wp_strip_all_tags( $body ) );

  if ( '' !== $text ) {
      return sprintf(
          /* translators: 1: HTTP status code, 2: remote response text. */
        __( 'Remote site returned HTTP %1$d: %2$s', 'mbn-theme' ),
        $status_code,
        wp_html_excerpt( $text, 180, '...' )
      );
  }

	return sprintf(
		/* translators: %d is the remote HTTP status code. */
      __( 'Remote site returned HTTP %d without a valid template response.', 'mbn-theme' ),
      $status_code
	);
}

/**
 * Get local block templates for REST responses.
 *
 * @return array<int, array{id:int,title:string,slug:string,status:string,content:string}>
 */
function custom_theme_get_reusable_block_templates_for_rest(): array {
	$posts = get_posts(
      array(
		  'post_type'              => 'mbn_block_template',
		  'post_status'            => array( 'publish', 'draft', 'private' ),
		  'posts_per_page'         => 100,
		  'orderby'                => 'title',
		  'order'                  => 'ASC',
		  'update_post_meta_cache' => false,
		  'update_post_term_cache' => false,
		  'no_found_rows'          => true,
	  )
	);

	$templates = array();
  foreach ( $posts as $post ) {
    if ( ! $post instanceof \WP_Post ) {
        continue;
    }

      $templates[] = array(
          'id'      => (int) $post->ID,
          'title'   => get_the_title( $post ),
          'slug'    => $post->post_name,
          'status'  => $post->post_status,
          'content' => $post->post_content,
      );
  }

	return $templates;
}

/**
 * Register template reuse REST routes.
 *
 * @return void
 */
function custom_theme_register_template_reuse_rest_routes(): void {
	register_rest_route(
      'mbn-theme/v1',
      '/block-templates',
      array(
		  'methods'             => \WP_REST_Server::READABLE,
		  'callback'            => 'custom_theme_rest_get_local_block_templates',
		  'permission_callback' => 'custom_theme_template_reuse_rest_permission',
	  )
	);

	register_rest_route(
      'mbn-theme/v1',
      '/template-reuse/sites',
      array(
		  'methods'             => \WP_REST_Server::READABLE,
		  'callback'            => 'custom_theme_rest_get_template_reuse_sites',
		  'permission_callback' => 'custom_theme_template_reuse_rest_permission',
	  )
	);

	register_rest_route(
      'mbn-theme/v1',
      '/template-reuse/sites/(?P<site_index>\d+)/templates',
      array(
		  'methods'             => \WP_REST_Server::READABLE,
		  'callback'            => 'custom_theme_rest_get_remote_block_templates',
		  'permission_callback' => 'custom_theme_template_reuse_rest_permission',
		  'args'                => array(
			  'site_index' => array(
				  'required'          => true,
				  'validate_callback' => 'custom_theme_template_reuse_validate_numeric_param',
			  ),
		  ),
	  )
	);
}
add_action( 'rest_api_init', 'custom_theme_register_template_reuse_rest_routes' );

/**
 * Validate numeric REST route parameters.
 *
 * WordPress passes the value, request, and parameter name to validation
 * callbacks, so native single-argument PHP functions cannot be used directly.
 *
 * @param mixed $value Parameter value.
 * @return bool
 */
function custom_theme_template_reuse_validate_numeric_param( $value ): bool {
	return is_numeric( $value );
}

/**
 * Permission callback for template reuse REST routes.
 *
 * @return bool
 */
function custom_theme_template_reuse_rest_permission(): bool {
	return current_user_can( 'edit_posts' );
}

/**
 * REST callback for local block templates.
 *
 * @return WP_REST_Response
 */
function custom_theme_rest_get_local_block_templates(): \WP_REST_Response {
	return rest_ensure_response(
      array(
		  'templates' => custom_theme_get_reusable_block_templates_for_rest(),
	  )
	);
}

/**
 * REST callback for configured remote sites.
 *
 * @return WP_REST_Response
 */
function custom_theme_rest_get_template_reuse_sites(): \WP_REST_Response {
	$sites          = custom_theme_get_template_reuse_sites();
	$response_sites = array();

  foreach ( $sites as $index => $site ) {
      $response_sites[] = array(
          'index'     => (int) $index,
          'site_name' => $site['site_name'],
          'home_url'  => $site['home_url'],
      );
  }

	return rest_ensure_response(
      array(
		  'sites' => $response_sites,
	  )
	);
}

/**
 * REST callback for templates from one configured remote site.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response|WP_Error
 */
function custom_theme_rest_get_remote_block_templates( WP_REST_Request $request ) {
	$sites      = custom_theme_get_template_reuse_sites();
	$site_index = absint( $request->get_param( 'site_index' ) );

  if ( ! isset( $sites[ $site_index ] ) ) {
      return new \WP_Error(
        'custom_theme_template_site_not_found',
        __( 'Remote template site was not found.', 'mbn-theme' ),
        array( 'status' => 404 )
      );
  }

	$site = $sites[ $site_index ];

  if ( ! custom_theme_template_reuse_has_valid_authorization_value( $site['application_password'] ) ) {
      return new \WP_Error(
        'custom_theme_template_remote_invalid_credentials',
        __( 'Remote template credentials must be entered as username:application-password, Basic ..., or Bearer ...', 'mbn-theme' ),
        array( 'status' => 400 )
      );
  }

	$request  = custom_theme_template_reuse_build_remote_request( $site );
	$response = wp_remote_get( $request['endpoint'], $request['args'] );

  if ( is_wp_error( $response ) ) {
      return new \WP_Error(
        'custom_theme_template_remote_request_failed',
        $response->get_error_message(),
        array( 'status' => 502 )
      );
  }

	$status_code = wp_remote_retrieve_response_code( $response );
	$body        = wp_remote_retrieve_body( $response );
	$payload     = json_decode( $body, true );

  if ( 200 !== $status_code || ! is_array( $payload ) || ! isset( $payload['templates'] ) || ! is_array( $payload['templates'] ) ) {
      return new \WP_Error(
        'custom_theme_template_remote_invalid_response',
        custom_theme_template_reuse_remote_error_message( $status_code, $body ),
        array( 'status' => 502 )
      );
  }

	return rest_ensure_response(
      array(
		  'site'      => array(
			  'site_name' => $site['site_name'],
			  'home_url'  => $site['home_url'],
		  ),
		  'templates' => $payload['templates'],
	  )
	);
}

/**
 * Enqueue editor UI for loading remote block templates.
 *
 * @return void
 */
function custom_theme_enqueue_template_reuse_editor_assets(): void {
	$asset_path = get_theme_file_path( 'assets/js/template-reuse-editor.js' );

  if ( ! file_exists( $asset_path ) ) {
      return;
  }

	wp_enqueue_script(
      'custom-theme-template-reuse-editor',
      get_theme_file_uri( 'assets/js/template-reuse-editor.js' ),
      array( 'wp-api-fetch', 'wp-blocks', 'wp-components', 'wp-data', 'wp-edit-post', 'wp-element', 'wp-i18n', 'wp-plugins' ),
      filemtime( $asset_path ),
      true
	);

	wp_add_inline_script(
      'custom-theme-template-reuse-editor',
      'window.customThemeTemplateReuse = ' . wp_json_encode(
        array(
			'restBase' => rest_url( 'mbn-theme/v1' ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
		)
      ) . ';',
      'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'custom_theme_enqueue_template_reuse_editor_assets' );
