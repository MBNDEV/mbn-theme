<?php
/**
 * Native WordPress Post Meta Boxes (replaces Carbon Fields PostHtmlInjectionContainer)
 * Custom HTML injection for individual posts and pages
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Custom HTML meta box for posts and pages
 */
function blgf_register_custom_html_meta_box() {
	add_meta_box(
      'blgf_custom_html_metabox',
      __( 'Custom HTML', 'mbn-theme' ),
      'blgf_render_custom_html_meta_box',
      array( 'post', 'page' ),
      'normal',
      'default'
	);
}
add_action( 'add_meta_boxes', 'blgf_register_custom_html_meta_box' );

/**
 * Render Custom HTML meta box.
 *
 * @param WP_Post $post Current post object.
 */
function blgf_render_custom_html_meta_box( $post ) {
	wp_nonce_field( 'blgf_custom_html_metabox', 'blgf_custom_html_nonce' );

	$head        = get_post_meta( $post->ID, '_blgf_post_html_head', true );
	$before_body = get_post_meta( $post->ID, '_blgf_post_html_before_body', true );
	$after_body  = get_post_meta( $post->ID, '_blgf_post_html_after_body', true );
	$footer      = get_post_meta( $post->ID, '_blgf_post_html_footer', true );

  ?>
	<p><strong><?php esc_html_e( 'Custom HTML (this entry)', 'mbn-theme' ); ?></strong></p>
	<p class="description"><?php esc_html_e( 'Overrides global Theme Options for this page or post when a field is not empty.', 'mbn-theme' ); ?></p>
	
	<table class="form-table">
		<tr>
			<th scope="row">
				<label for="blgf_post_html_head"><?php esc_html_e( 'Head', 'mbn-theme' ); ?></label>
			</th>
			<td>
				<textarea name="blgf_post_html_head" id="blgf_post_html_head" rows="10" class="large-text code"><?php echo esc_textarea( $head ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Printed inside the document head (e.g. meta tags, styles).', 'mbn-theme' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="blgf_post_html_before_body"><?php esc_html_e( 'Before Body', 'mbn-theme' ); ?></label>
			</th>
			<td>
				<textarea name="blgf_post_html_before_body" id="blgf_post_html_before_body" rows="10" class="large-text code"><?php echo esc_textarea( $before_body ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Printed right after the opening body tag.', 'mbn-theme' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="blgf_post_html_after_body"><?php esc_html_e( 'After Body', 'mbn-theme' ); ?></label>
			</th>
			<td>
				<textarea name="blgf_post_html_after_body" id="blgf_post_html_after_body" rows="10" class="large-text code"><?php echo esc_textarea( $after_body ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Printed after the main page wrapper, before footer scripts.', 'mbn-theme' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="blgf_post_html_footer"><?php esc_html_e( 'Footer', 'mbn-theme' ); ?></label>
			</th>
			<td>
				<textarea name="blgf_post_html_footer" id="blgf_post_html_footer" rows="10" class="large-text code"><?php echo esc_textarea( $footer ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Printed at the start of wp_footer (before most scripts).', 'mbn-theme' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Save Custom HTML meta box data.
 *
 * @param int $post_id Post ID.
 */
function blgf_save_custom_html_meta_box( $post_id ) {
	// Check if nonce is set
  if ( ! isset( $_POST['blgf_custom_html_nonce'] ) ) {
      return;
  }

	// Verify nonce
  if ( ! wp_verify_nonce( $_POST['blgf_custom_html_nonce'], 'blgf_custom_html_metabox' ) ) {
      return;
  }

	// Check if this is an autosave
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
      return;
  }

	// Check user permissions
  if ( ! current_user_can( 'edit_post', $post_id ) ) {
      return;
  }

	// Save each field
	$fields = array(
		'blgf_post_html_head'        => '_blgf_post_html_head',
		'blgf_post_html_before_body' => '_blgf_post_html_before_body',
		'blgf_post_html_after_body'  => '_blgf_post_html_after_body',
		'blgf_post_html_footer'      => '_blgf_post_html_footer',
	);

	foreach ( $fields as $field_name => $meta_key ) {
      if ( isset( $_POST[ $field_name ] ) ) {
          update_post_meta( $post_id, $meta_key, wp_kses_post( $_POST[ $field_name ] ) );
      } else {
          delete_post_meta( $post_id, $meta_key );
      }
	}
}
add_action( 'save_post', 'blgf_save_custom_html_meta_box' );

/**
 * Get post meta (wrapper function compatible with old Carbon Fields calls).
 *
 * @param int    $post_id Post ID.
 * @param string $key Meta key.
 * @param mixed  $default_value Default value.
 * @return mixed
 */
function blgf_get_post_meta( $post_id, $key, $default_value = '' ) {
	// Map old Carbon Fields keys to new keys
	$key_map = array(
		'crb_post_html_head'        => '_blgf_post_html_head',
		'crb_post_html_before_body' => '_blgf_post_html_before_body',
		'crb_post_html_after_body'  => '_blgf_post_html_after_body',
		'crb_post_html_footer'      => '_blgf_post_html_footer',
	);

	$mapped_key = isset( $key_map[ $key ] ) ? $key_map[ $key ] : $key;

	$value = get_post_meta( $post_id, $mapped_key, true );

	return '' !== $value ? $value : $default_value;
}
