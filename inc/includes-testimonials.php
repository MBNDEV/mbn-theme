<?php
/**
 * Testimonials custom post type (rider feedback).
 *
 * A non-public, non-searchable CPT that stores reviewer testimonials shown by the
 * mbn-ai-testimonials slider block. Title = reviewer name, content = quote, with
 * a role/location string and a 1–5 star rating in post meta, and the avatar as the
 * featured image. Edited in wp-admin (Testimonials menu); never queryable on the
 * front end directly.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Register the mbn_testimonial post type and its meta.
 *
 * @return void
 */
function mbn_register_testimonials() {
  register_post_type(
    'mbn_testimonial',
    array(
		'labels'              => array(
			'name'          => __( 'Testimonials', 'mbn-theme' ),
			'singular_name' => __( 'Testimonial', 'mbn-theme' ),
			'add_new_item'  => __( 'Add New Testimonial', 'mbn-theme' ),
			'edit_item'     => __( 'Edit Testimonial', 'mbn-theme' ),
			'menu_name'     => __( 'Testimonials', 'mbn-theme' ),
		),
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-format-quote',
		'menu_position'       => 26,
		'has_archive'         => false,
		'rewrite'             => false,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
    )
  );

  register_post_meta(
    'mbn_testimonial',
    '_mbn_testimonial_role',
    array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'sanitize_callback' => 'sanitize_text_field',
		'auth_callback'     => static function () {
			return current_user_can( 'edit_posts' );
		},
    )
  );

  register_post_meta(
    'mbn_testimonial',
    '_mbn_testimonial_rating',
    array(
		'type'              => 'integer',
		'single'            => true,
		'default'           => 5,
		'show_in_rest'      => true,
		'sanitize_callback' => 'mbn_sanitize_rating',
		'auth_callback'     => static function () {
			return current_user_can( 'edit_posts' );
		},
    )
  );
}
add_action( 'init', 'mbn_register_testimonials' );

/**
 * Clamp a star rating to 1–5.
 *
 * @param mixed $value Raw value.
 * @return int
 */
function mbn_sanitize_rating( $value ): int {
  return (int) max( 1, min( 5, (int) $value ) );
}

/**
 * Register the testimonial details meta box (role + rating).
 *
 * @return void
 */
function mbn_testimonial_meta_box() {
  add_meta_box(
    'mbn_testimonial_details',
    __( 'Testimonial details', 'mbn-theme' ),
    'mbn_render_testimonial_meta_box',
    'mbn_testimonial',
    'side',
    'default'
  );
}
add_action( 'add_meta_boxes', 'mbn_testimonial_meta_box' );

/**
 * Render the testimonial meta box.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function mbn_render_testimonial_meta_box( $post ) {
  wp_nonce_field( 'mbn_testimonial_meta', 'mbn_testimonial_meta_nonce' );
  $person_role = (string) get_post_meta( $post->ID, '_mbn_testimonial_role', true );
  $rating      = (int) get_post_meta( $post->ID, '_mbn_testimonial_rating', true );
  $rating      = $rating > 0 ? $rating : 5;
  ?>
  <p>
    <label for="mbn-testimonial-role"><strong><?php esc_html_e( 'Role / location', 'mbn-theme' ); ?></strong></label>
    <input type="text" id="mbn-testimonial-role" name="mbn_testimonial_role" class="widefat" value="<?php echo esc_attr( $person_role ); ?>" placeholder="<?php esc_attr_e( 'Motocross racer, Arizona', 'mbn-theme' ); ?>" />
  </p>
  <p>
    <label for="mbn-testimonial-rating"><strong><?php esc_html_e( 'Rating (stars)', 'mbn-theme' ); ?></strong></label>
    <select id="mbn-testimonial-rating" name="mbn_testimonial_rating" class="widefat">
      <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
        <option value="<?php echo esc_attr( (string) $i ); ?>" <?php selected( $rating, $i ); ?>><?php echo esc_html( (string) $i ); ?></option>
      <?php endfor; ?>
    </select>
  </p>
  <p class="description"><?php esc_html_e( 'The quote goes in the main content area; the avatar is the featured image.', 'mbn-theme' ); ?></p>
  <?php
}

/**
 * Save the testimonial meta box fields.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function mbn_save_testimonial_meta( $post_id ) {
  if ( ! isset( $_POST['mbn_testimonial_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mbn_testimonial_meta_nonce'] ) ), 'mbn_testimonial_meta' ) ) {
    return;
  }
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }
  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return;
  }

  $person_role = isset( $_POST['mbn_testimonial_role'] ) ? sanitize_text_field( wp_unslash( $_POST['mbn_testimonial_role'] ) ) : '';
  update_post_meta( $post_id, '_mbn_testimonial_role', $person_role );

  $rating = isset( $_POST['mbn_testimonial_rating'] ) ? mbn_sanitize_rating( wp_unslash( $_POST['mbn_testimonial_rating'] ) ) : 5;
  update_post_meta( $post_id, '_mbn_testimonial_rating', $rating );
}
add_action( 'save_post_mbn_testimonial', 'mbn_save_testimonial_meta' );
