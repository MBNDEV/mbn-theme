<?php
/**
 * Remote Block Template reuse — JWT sign-in (no stored credentials).
 *
 * The editor signs in to a remote site (URL + username + password), the server
 * proxies that to the remote JWT auth endpoint, and the resulting token is
 * returned to the browser and held in memory for the session only. Nothing is
 * persisted: no credentials and no token are stored in the database. Subsequent
 * template requests send the token back as a Bearer credential through a
 * stateless proxy.
 *
 * Requires the remote site to (a) run this theme (for the block-templates
 * endpoint) and (b) expose a JWT token endpoint at wp-json/jwt-auth/v1/token.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
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
 * Validate and normalize a remote site URL (blocks SSRF to internal hosts).
 *
 * @param mixed $url Raw URL.
 * @return string|WP_Error Clean URL, or error.
 */
function custom_theme_template_reuse_clean_remote_url( $url ) {
  $url = esc_url_raw( trim( (string) $url ) );

  if ( '' === $url ) {
    return new \WP_Error( 'mbn_remote_url', __( 'A remote site URL is required.', 'mbn-theme' ), array( 'status' => 400 ) );
  }

  $scheme = wp_parse_url( $url, PHP_URL_SCHEME );
  if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
    return new \WP_Error( 'mbn_remote_url', __( 'The remote site URL must start with http:// or https://.', 'mbn-theme' ), array( 'status' => 400 ) );
  }

  if ( ! wp_http_validate_url( $url ) ) {
    return new \WP_Error( 'mbn_remote_url', __( 'The remote site URL is not allowed.', 'mbn-theme' ), array( 'status' => 400 ) );
  }

  return $url;
}

/**
 * Decode the `exp` claim from a JWT payload (no signature verification; the
 * remote validates the token — this is for display only).
 *
 * @param string $token JWT.
 * @return int Unix timestamp, or 0.
 */
function custom_theme_template_reuse_jwt_exp( string $token ): int {
  $parts = explode( '.', $token );
  if ( count( $parts ) < 2 ) {
    return 0;
  }

  // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- decoding a JWT payload, not obfuscation.
  $decoded = base64_decode( strtr( $parts[1], '-_', '+/' ), true );
  if ( false === $decoded ) {
    return 0;
  }

  $data = json_decode( $decoded, true );

  return ( is_array( $data ) && isset( $data['exp'] ) ) ? (int) $data['exp'] : 0;
}

/**
 * Extract a readable remote error message from a response payload.
 *
 * @param int    $status_code Remote HTTP status code.
 * @param string $body        Remote response body.
 * @return string
 */
function custom_theme_template_reuse_remote_error_message( int $status_code, string $body ): string {
  $payload = json_decode( $body, true );

  if ( is_array( $payload ) && isset( $payload['message'] ) && is_string( $payload['message'] ) ) {
    return sprintf(
      /* translators: 1: HTTP status code, 2: remote error message. */
      __( 'Remote site returned HTTP %1$d: %2$s', 'mbn-theme' ),
      $status_code,
      wp_strip_all_tags( $payload['message'] )
    );
  }

  return sprintf(
    /* translators: %d is the remote HTTP status code. */
    __( 'Remote site returned HTTP %d without a valid response.', 'mbn-theme' ),
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
    '/template-reuse/signin',
    array(
		'methods'             => \WP_REST_Server::CREATABLE,
		'callback'            => 'custom_theme_rest_template_reuse_signin',
		'permission_callback' => 'custom_theme_template_reuse_rest_permission',
		'args'                => array(
			'home_url' => array( 'required' => true ),
			'username' => array( 'required' => true ),
			'password' => array( 'required' => true ),
		),
    )
  );

  register_rest_route(
    'mbn-theme/v1',
    '/template-reuse/templates',
    array(
		'methods'             => \WP_REST_Server::CREATABLE,
		'callback'            => 'custom_theme_rest_template_reuse_remote_templates',
		'permission_callback' => 'custom_theme_template_reuse_rest_permission',
		'args'                => array(
			'home_url' => array( 'required' => true ),
			'token'    => array( 'required' => true ),
		),
    )
  );
}
add_action( 'rest_api_init', 'custom_theme_register_template_reuse_rest_routes' );

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
 * REST callback: sign in to a remote site and return a JWT (never stored).
 *
 * Exchanges credentials for a token via the remote JWT endpoint. The username
 * and password are used only for this request and are not persisted or logged.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response|WP_Error
 */
function custom_theme_rest_template_reuse_signin( WP_REST_Request $request ) {
  $home_url = custom_theme_template_reuse_clean_remote_url( $request->get_param( 'home_url' ) );
  if ( is_wp_error( $home_url ) ) {
    return $home_url;
  }

  $username = sanitize_text_field( (string) $request->get_param( 'username' ) );
  $password = (string) $request->get_param( 'password' );

  if ( '' === $username || '' === $password ) {
    return new \WP_Error( 'mbn_remote_creds', __( 'Username and password are required.', 'mbn-theme' ), array( 'status' => 400 ) );
  }

  $response = wp_remote_post(
    trailingslashit( $home_url ) . 'wp-json/jwt-auth/v1/token',
    array(
		'timeout' => 15,
		'headers' => array( 'Accept' => 'application/json' ),
		'body'    => array(
			'username' => $username,
			'password' => $password,
		),
    )
  );

  if ( is_wp_error( $response ) ) {
    return new \WP_Error( 'mbn_remote_failed', $response->get_error_message(), array( 'status' => 502 ) );
  }

  $status  = (int) wp_remote_retrieve_response_code( $response );
  $body    = wp_remote_retrieve_body( $response );
  $payload = json_decode( $body, true );

  if ( 200 !== $status || ! is_array( $payload ) || empty( $payload['token'] ) ) {
    return new \WP_Error( 'mbn_remote_signin_failed', custom_theme_template_reuse_remote_error_message( $status, $body ), array( 'status' => 502 ) );
  }

  $token = (string) $payload['token'];

  return rest_ensure_response(
    array(
		'token'    => $token,
		'home_url' => $home_url,
		'user'     => (string) ( $payload['user_display_name'] ?? $payload['user_nicename'] ?? $username ),
		'exp'      => custom_theme_template_reuse_jwt_exp( $token ),
    )
  );
}

/**
 * REST callback: fetch remote block templates using a Bearer token (stateless).
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response|WP_Error
 */
function custom_theme_rest_template_reuse_remote_templates( WP_REST_Request $request ) {
  $home_url = custom_theme_template_reuse_clean_remote_url( $request->get_param( 'home_url' ) );
  if ( is_wp_error( $home_url ) ) {
    return $home_url;
  }

  $token = trim( (string) $request->get_param( 'token' ) );
  if ( '' === $token ) {
    return new \WP_Error( 'mbn_remote_token', __( 'Sign in to the remote site first.', 'mbn-theme' ), array( 'status' => 401 ) );
  }

  $response = wp_remote_get(
    trailingslashit( $home_url ) . 'wp-json/mbn-theme/v1/block-templates',
    array(
		'timeout' => 15,
		'headers' => array(
			'Authorization' => 'Bearer ' . $token,
			'Accept'        => 'application/json',
		),
    )
  );

  if ( is_wp_error( $response ) ) {
    return new \WP_Error( 'mbn_remote_failed', $response->get_error_message(), array( 'status' => 502 ) );
  }

  $status  = (int) wp_remote_retrieve_response_code( $response );
  $body    = wp_remote_retrieve_body( $response );
  $payload = json_decode( $body, true );

  if ( 200 !== $status || ! is_array( $payload ) || ! isset( $payload['templates'] ) || ! is_array( $payload['templates'] ) ) {
    return new \WP_Error( 'mbn_remote_invalid', custom_theme_template_reuse_remote_error_message( $status, $body ), array( 'status' => 502 ) );
  }

  return rest_ensure_response( array( 'templates' => $payload['templates'] ) );
}

/**
 * Enqueue editor UI for signing in to and importing remote block templates.
 *
 * @return void
 */
function custom_theme_enqueue_template_reuse_editor_assets(): void {
  $script_path = get_theme_file_path( 'build/template-reuse.js' );
  $asset_path  = get_theme_file_path( 'build/template-reuse.asset.php' );

  if ( ! file_exists( $script_path ) || ! file_exists( $asset_path ) ) {
    return;
  }

  $asset = require $asset_path;

  wp_enqueue_script(
    'custom-theme-template-reuse-editor',
    get_theme_file_uri( 'build/template-reuse.js' ),
    $asset['dependencies'],
    $asset['version'],
    true
  );

  // Fullscreen preview modal: only the WordPress component frame internals that
  // Tailwind utilities cannot target. All custom UI is styled with Tailwind in JS.
  wp_register_style( 'custom-theme-template-reuse-editor', false, array(), $asset['version'] );
  wp_enqueue_style( 'custom-theme-template-reuse-editor' );
  wp_add_inline_style(
    'custom-theme-template-reuse-editor',
    '.mbn-template-reuse-preview-modal.is-fullscreen .components-modal__frame{width:100vw!important;max-width:100vw!important;height:100vh!important;max-height:100vh!important;margin:0!important;top:0!important;left:0!important;border-radius:0!important;transform:none!important}.mbn-template-reuse-preview-modal.is-fullscreen .components-modal__header{display:none!important}.mbn-template-reuse-preview-modal.is-fullscreen .components-modal__content{display:flex;flex-direction:column;height:100vh;max-height:100vh;margin:0;padding:0}'
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
