<?php
/**
 * Custom HTML injection — global (Customizer) and per-post (meta box).
 *
 * Three slots, injected on standard WordPress hooks:
 *   - Header HTML      -> wp_head
 *   - After Body HTML  -> wp_body_open
 *   - Footer HTML      -> wp_footer
 *
 * Global values come from the Customizer (MBN Theme > Custom HTML). Per-post
 * values come from the "Custom HTML" meta box and are appended after the global
 * output on singular views. Values are stored capability-aware: users with the
 * `unfiltered_html` capability may store raw markup (scripts allowed); everyone
 * else is filtered through wp_kses_post.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Slot definitions: key => array{ option, meta, label, description }.
 *
 * @return array<string, array{option:string, meta:string, label:string, description:string}>
 */
function mbn_custom_html_slots(): array {
  return array(
	  'header'     => array(
		  'option'      => 'mbn_html_header',
		  'meta'        => '_mbn_html_header',
		  'label'       => __( 'Header HTML', 'mbn-theme' ),
		  'description' => __( 'Printed in the document <head> (wp_head). Meta tags, styles, analytics.', 'mbn-theme' ),
	  ),
	  'after_body' => array(
		  'option'      => 'mbn_html_after_body',
		  'meta'        => '_mbn_html_after_body',
		  'label'       => __( 'After Body HTML', 'mbn-theme' ),
		  'description' => __( 'Printed right after the opening <body> tag (wp_body_open).', 'mbn-theme' ),
	  ),
	  'footer'     => array(
		  'option'      => 'mbn_html_footer',
		  'meta'        => '_mbn_html_footer',
		  'label'       => __( 'Footer HTML', 'mbn-theme' ),
		  'description' => __( 'Printed near the end of the page (wp_footer).', 'mbn-theme' ),
	  ),
  );
}

/**
 * Capability-aware sanitization for custom HTML.
 *
 * @param string $value Raw value.
 * @return string
 */
function mbn_sanitize_custom_html( $value ): string {
  $value = is_string( $value ) ? $value : '';

  if ( current_user_can( 'unfiltered_html' ) ) {
    return $value;
  }

  return wp_kses_post( $value );
}

/**
 * Output the combined global + per-post HTML for a slot.
 *
 * @param string $slot Slot key (header|after_body|footer).
 * @return void
 */
function mbn_print_custom_html_slot( string $slot ): void {
  $slots = mbn_custom_html_slots();
  if ( ! isset( $slots[ $slot ] ) ) {
    return;
  }

  $html = (string) get_option( $slots[ $slot ]['option'], '' );

  if ( is_singular() ) {
    $post_id   = get_queried_object_id();
    $post_html = $post_id ? (string) get_post_meta( $post_id, $slots[ $slot ]['meta'], true ) : '';
    if ( '' !== trim( $post_html ) ) {
      $html .= "\n" . $post_html;
    }
  }

  if ( '' === trim( $html ) ) {
    return;
  }

  // Stored capability-aware (raw only for unfiltered_html users), so output as-is.
  echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional custom HTML from trusted editors.
}

/**
 * Inject the header slot.
 *
 * @return void
 */
function mbn_inject_header_html(): void {
  mbn_print_custom_html_slot( 'header' );
}
add_action( 'wp_head', 'mbn_inject_header_html', 100 );

/**
 * Inject the after-body slot.
 *
 * @return void
 */
function mbn_inject_after_body_html(): void {
  mbn_print_custom_html_slot( 'after_body' );
}
add_action( 'wp_body_open', 'mbn_inject_after_body_html', 10 );

/**
 * Inject the footer slot.
 *
 * @return void
 */
function mbn_inject_footer_html(): void {
  mbn_print_custom_html_slot( 'footer' );
}
add_action( 'wp_footer', 'mbn_inject_footer_html', 100 );

/**
 * Register the per-post Custom HTML meta box on public post types.
 *
 * @return void
 */
function mbn_register_custom_html_meta_box(): void {
  foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
    if ( 'attachment' === $post_type ) {
      continue;
    }

    add_meta_box(
      'mbn_custom_html',
      __( 'Custom HTML', 'mbn-theme' ),
      'mbn_render_custom_html_meta_box',
      $post_type,
      'normal',
      'low'
    );
  }
}
add_action( 'add_meta_boxes', 'mbn_register_custom_html_meta_box' );

/**
 * Render the per-post Custom HTML meta box (styled with Tailwind utilities).
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function mbn_render_custom_html_meta_box( $post ): void {
  wp_nonce_field( 'mbn_custom_html_save', 'mbn_custom_html_nonce' );
  ?>
  <div class="mbn-custom-html-meta flex flex-col gap-4">
    <p class="mbn-custom-html-help m-0 text-sm opacity-70">
      <?php esc_html_e( 'Injected only on this post, after the global Customizer HTML. You may use Tailwind classes.', 'mbn-theme' ); ?>
    </p>
    <?php foreach ( mbn_custom_html_slots() as $key => $slot ) : ?>
      <?php $value = get_post_meta( $post->ID, $slot['meta'], true ); ?>
      <div class="mbn-custom-html-field flex flex-col gap-1">
        <label class="mbn-custom-html-label block font-semibold" for="mbn-html-<?php echo esc_attr( $key ); ?>">
          <?php echo esc_html( $slot['label'] ); ?>
        </label>
        <textarea
          class="mbn-custom-html-input widefat code"
          id="mbn-html-<?php echo esc_attr( $key ); ?>"
          name="mbn_html[<?php echo esc_attr( $key ); ?>]"
          rows="5"
        ><?php echo esc_textarea( is_string( $value ) ? $value : '' ); ?></textarea>
        <span class="mbn-custom-html-desc block text-xs opacity-60"><?php echo esc_html( $slot['description'] ); ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  <?php
}

/**
 * Save the per-post Custom HTML meta box.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function mbn_save_custom_html_meta_box( $post_id ): void {
  if ( ! isset( $_POST['mbn_custom_html_nonce'] ) ) {
    return;
  }

  $nonce = sanitize_text_field( wp_unslash( $_POST['mbn_custom_html_nonce'] ) );
  if ( ! wp_verify_nonce( $nonce, 'mbn_custom_html_save' ) ) {
    return;
  }

  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }

  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return;
  }

  $submitted = isset( $_POST['mbn_html'] ) && is_array( $_POST['mbn_html'] )
    ? wp_unslash( $_POST['mbn_html'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per slot below.
    : array();

  foreach ( mbn_custom_html_slots() as $key => $slot ) {
    $raw   = isset( $submitted[ $key ] ) ? (string) $submitted[ $key ] : '';
    $clean = mbn_sanitize_custom_html( $raw );

    if ( '' === trim( $clean ) ) {
      delete_post_meta( $post_id, $slot['meta'] );
      continue;
    }

    update_post_meta( $post_id, $slot['meta'], $clean );
  }
}
add_action( 'save_post', 'mbn_save_custom_html_meta_box' );
