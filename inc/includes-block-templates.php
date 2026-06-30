<?php
/**
 * Custom post type: Block Templates (reusable layouts inserted via the editor / Remote Template Reuse).
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Register the Block Templates post type.
 *
 * @return void
 */
function custom_theme_register_block_template_post_type(): void {
  $labels = array(
	  'name'               => __( 'Block Templates', 'mbn-theme' ),
	  'singular_name'      => __( 'Block Template', 'mbn-theme' ),
	  'add_new'            => __( 'Add New', 'mbn-theme' ),
	  'add_new_item'       => __( 'Add New Block Template', 'mbn-theme' ),
	  'edit_item'          => __( 'Edit Block Template', 'mbn-theme' ),
	  'new_item'           => __( 'New Block Template', 'mbn-theme' ),
	  'view_item'          => __( 'View Block Template', 'mbn-theme' ),
	  'search_items'       => __( 'Search Block Templates', 'mbn-theme' ),
	  'not_found'          => __( 'No block templates found.', 'mbn-theme' ),
	  'not_found_in_trash' => __( 'No block templates found in Trash.', 'mbn-theme' ),
	  'all_items'          => __( 'Block Templates', 'mbn-theme' ),
  );

  register_post_type(
    'mbn_block_template',
    array(
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'query_var'           => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'has_archive'         => false,
		'hierarchical'        => false,
		'show_in_rest'        => true,
		'menu_position'       => 21,
		'menu_icon'           => 'dashicons-layout',
		'supports'            => array( 'title', 'editor', 'revisions' ),
    )
  );
}
add_action( 'init', 'custom_theme_register_block_template_post_type', 5 );

/**
 * Exclude block templates from the WordPress core sitemap.
 *
 * @param array<string, WP_Post_Type> $post_types Post types included in the sitemap.
 * @return array<string, WP_Post_Type>
 */
function custom_theme_block_template_exclude_from_sitemap( $post_types ) {
  unset( $post_types['mbn_block_template'] );

  return $post_types;
}

/**
 * Prevent block template singles from being indexed by search engines.
 *
 * @param array<string, bool|string> $robots Robots directives.
 * @return array<string, bool|string>
 */
function custom_theme_block_template_robots_noindex( $robots ) {
  if ( is_singular( 'mbn_block_template' ) ) {
    $robots['noindex']   = true;
    $robots['nofollow']  = true;
    $robots['noarchive'] = true;
  }

  return $robots;
}

/**
 * Block anonymous front-end access to block template singles.
 */
function custom_theme_block_template_block_public_view() {
  if ( ! is_singular( 'mbn_block_template' ) ) {
    return;
  }

  if ( is_preview() && current_user_can( 'edit_posts' ) ) {
    return;
  }

  if ( current_user_can( 'edit_posts' ) ) {
    return;
  }

  global $wp_query;

  $wp_query->set_404();
  status_header( 404 );
  nocache_headers();
}

/**
 * Exclude block templates from Yoast SEO sitemaps when the plugin is active.
 *
 * @param bool   $excluded   Whether the post type is excluded.
 * @param string $post_type  Post type slug.
 * @return bool
 */
function custom_theme_block_template_yoast_exclude_sitemap( $excluded, $post_type ) {
  if ( 'mbn_block_template' === $post_type ) {
    return true;
  }

  return $excluded;
}

/**
 * Exclude block templates from Rank Math sitemaps when the plugin is active.
 *
 * @param bool   $exclude   Whether the post type is excluded.
 * @param string $post_type Post type slug.
 * @return bool
 */
function custom_theme_block_template_rank_math_exclude_sitemap( $exclude, $post_type ) {
  if ( 'mbn_block_template' === $post_type ) {
    return true;
  }

  return $exclude;
}

add_filter( 'wp_sitemaps_post_types', 'custom_theme_block_template_exclude_from_sitemap' );
add_filter( 'wp_robots', 'custom_theme_block_template_robots_noindex' );
add_action( 'template_redirect', 'custom_theme_block_template_block_public_view' );
add_filter( 'wpseo_sitemap_exclude_post_type', 'custom_theme_block_template_yoast_exclude_sitemap', 10, 2 );
add_filter( 'rank_math/sitemap/exclude_post_type', 'custom_theme_block_template_rank_math_exclude_sitemap', 10, 2 );

/* ───────────────────────── Header / Footer part templates ───────────────────────── */

/**
 * Slugs of the default part templates rendered into the header/footer tags.
 *
 * @return array<int, string>
 */
function mbn_part_template_slugs(): array {
  return array( 'header', 'footer' );
}

/**
 * Get a part template post by slug (header|footer).
 *
 * @param string $slug Template slug.
 * @return WP_Post|null
 */
function mbn_get_part_template( string $slug ) {
  $posts = get_posts(
    array(
		'post_type'        => 'mbn_block_template',
		'name'             => $slug,
		'post_status'      => 'publish',
		'numberposts'      => 1,
		'suppress_filters' => false,
    )
  );

  return ! empty( $posts ) ? $posts[0] : null;
}

/**
 * Render a part template's blocks, exposing its ID so mbn-logo/mbn-menu can
 * read the template's logo/menu meta.
 *
 * @param string $slug Template slug (header|footer).
 * @return string Rendered HTML ('' when the template is missing/empty).
 */
function mbn_render_part_template( string $slug ): string {
  $post = mbn_get_part_template( $slug );
  if ( ! $post instanceof WP_Post || '' === trim( (string) $post->post_content ) ) {
    return '';
  }

  $GLOBALS['mbn_current_template_id'] = (int) $post->ID;
  $html                               = do_blocks( $post->post_content );
  unset( $GLOBALS['mbn_current_template_id'] );

  return $html;
}

/**
 * The chosen logo attachment ID for a part template.
 *
 * @param int $template_id Template post ID.
 * @return int
 */
function mbn_get_template_logo_id( int $template_id ): int {
  return (int) get_post_meta( $template_id, '_mbn_template_logo_id', true );
}

/**
 * The ordered menu IDs selected for a part template.
 *
 * @param int $template_id Template post ID.
 * @return array<int, int>
 */
function mbn_get_template_menus( int $template_id ): array {
  $menus = get_post_meta( $template_id, '_mbn_template_menus', true );

  return is_array( $menus ) ? array_values( array_map( 'intval', $menus ) ) : array();
}

/**
 * Default block markup for the seeded part templates.
 *
 * @return array<string, array{title:string, content:string}>
 */
function mbn_default_part_templates(): array {
  $header = "<!-- wp:mbn-theme/mbn-section {\"align\":\"full\",\"paddingTop\":\"1rem\",\"paddingBottom\":\"1rem\"} -->\n"
    . "<!-- wp:mbn-theme/mbn-columns {\"columnCount\":2,\"align\":\"full\"} -->\n"
    . "<!-- wp:mbn-theme/mbn-column -->\n<!-- wp:mbn-theme/mbn-logo /-->\n<!-- /wp:mbn-theme/mbn-column -->\n"
    . "<!-- wp:mbn-theme/mbn-column -->\n<!-- wp:mbn-theme/mbn-menu {\"slot\":1} /-->\n<!-- /wp:mbn-theme/mbn-column -->\n"
    . "<!-- /wp:mbn-theme/mbn-columns -->\n"
    . '<!-- /wp:mbn-theme/mbn-section -->';

  $footer = "<!-- wp:mbn-theme/mbn-section {\"align\":\"full\",\"paddingTop\":\"2rem\",\"paddingBottom\":\"2rem\"} -->\n"
    . "<!-- wp:mbn-theme/mbn-menu {\"slot\":1} /-->\n"
    . '<!-- /wp:mbn-theme/mbn-section -->';

  return array(
	  'header' => array(
		  'title'   => __( 'Header', 'mbn-theme' ),
		  'content' => $header,
	  ),
	  'footer' => array(
		  'title'   => __( 'Footer', 'mbn-theme' ),
		  'content' => $footer,
	  ),
  );
}

/**
 * Seed the default Header/Footer block templates once, if missing.
 *
 * @return void
 */
function mbn_seed_default_part_templates(): void {
  if ( get_option( 'mbn_default_parts_seeded' ) ) {
    return;
  }

  foreach ( mbn_default_part_templates() as $slug => $data ) {
    if ( mbn_get_part_template( $slug ) instanceof WP_Post ) {
      continue;
    }

    wp_insert_post(
      array(
		  'post_type'    => 'mbn_block_template',
		  'post_status'  => 'publish',
		  'post_title'   => $data['title'],
		  'post_name'    => $slug,
		  'post_content' => $data['content'],
      )
    );
  }

  update_option( 'mbn_default_parts_seeded', 1 );
}
add_action( 'after_switch_theme', 'mbn_seed_default_part_templates' );
add_action( 'admin_init', 'mbn_seed_default_part_templates' );

/**
 * Register the Header/Footer settings meta box on block templates.
 *
 * @return void
 */
function mbn_register_part_meta_box(): void {
  add_meta_box(
    'mbn_template_parts',
    __( 'Header / Footer Settings', 'mbn-theme' ),
    'mbn_render_part_meta_box',
    'mbn_block_template',
    'side',
    'default'
  );
}
add_action( 'add_meta_boxes', 'mbn_register_part_meta_box' );

/**
 * Render the Header/Footer settings meta box (logo + menus).
 *
 * @param WP_Post $post Current template post.
 * @return void
 */
function mbn_render_part_meta_box( $post ): void {
  wp_nonce_field( 'mbn_template_parts_save', 'mbn_template_parts_nonce' );

  $logo_id   = mbn_get_template_logo_id( (int) $post->ID );
  $selected  = mbn_get_template_menus( (int) $post->ID );
  $nav_menus = wp_get_nav_menus();
  ?>
  <div class="mbn-template-parts">
    <p class="mbn-part-field">
      <label class="block font-semibold"><?php esc_html_e( 'Logo', 'mbn-theme' ); ?></label>
      <span class="mbn-logo-preview" style="display:block;margin:6px 0;">
        <?php if ( $logo_id ) : ?>
          <?php echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'style' => 'max-width:100%;height:auto;' ) ); ?>
        <?php endif; ?>
      </span>
      <input type="hidden" class="mbn-logo-id" name="mbn_template_logo_id" value="<?php echo esc_attr( (string) $logo_id ); ?>" />
      <button type="button" class="button mbn-select-logo"><?php esc_html_e( 'Select logo', 'mbn-theme' ); ?></button>
      <button type="button" class="button-link mbn-remove-logo" style="<?php echo $logo_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'mbn-theme' ); ?></button>
    </p>

    <p class="mbn-part-field" style="margin-top:14px;">
      <label class="block font-semibold"><?php esc_html_e( 'Menus', 'mbn-theme' ); ?></label>
      <span class="description" style="display:block;margin:2px 0 6px;">
        <?php esc_html_e( 'Checked menus map to MBN Menu blocks by slot, in order. Manage menus under Appearance → Menus.', 'mbn-theme' ); ?>
      </span>
      <?php if ( empty( $nav_menus ) ) : ?>
        <em><?php esc_html_e( 'No menus yet. Create one under Appearance → Menus.', 'mbn-theme' ); ?></em>
      <?php else : ?>
        <?php
        $slot = 1;
        foreach ( $nav_menus as $menu ) :
          $checked = in_array( (int) $menu->term_id, $selected, true );
          ?>
          <label style="display:block;margin:3px 0;">
            <input type="checkbox" name="mbn_template_menus[]" value="<?php echo esc_attr( (string) $menu->term_id ); ?>" <?php checked( $checked ); ?> />
            <?php echo esc_html( $menu->name ); ?>
            <?php if ( $checked ) : ?>
              <code>slot <?php echo esc_html( (string) ( array_search( (int) $menu->term_id, $selected, true ) + 1 ) ); ?></code>
            <?php endif; ?>
          </label>
          <?php
          ++$slot;
        endforeach;
        ?>
      <?php endif; ?>
    </p>
  </div>
  <?php
}

/**
 * Save the Header/Footer settings meta box.
 *
 * @param int $post_id Template post ID.
 * @return void
 */
function mbn_save_part_meta_box( $post_id ): void {
  if ( ! isset( $_POST['mbn_template_parts_nonce'] ) ) {
    return;
  }

  $nonce = sanitize_text_field( wp_unslash( $_POST['mbn_template_parts_nonce'] ) );
  if ( ! wp_verify_nonce( $nonce, 'mbn_template_parts_save' ) ) {
    return;
  }

  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }

  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return;
  }

  $logo_id = isset( $_POST['mbn_template_logo_id'] ) ? absint( wp_unslash( $_POST['mbn_template_logo_id'] ) ) : 0;
  if ( $logo_id ) {
    update_post_meta( $post_id, '_mbn_template_logo_id', $logo_id );
  } else {
    delete_post_meta( $post_id, '_mbn_template_logo_id' );
  }

  $raw_menus = isset( $_POST['mbn_template_menus'] ) ? (array) wp_unslash( $_POST['mbn_template_menus'] ) : array();
  $menus     = mbn_sanitize_template_menu_ids( $raw_menus );

  if ( ! empty( $menus ) ) {
    update_post_meta( $post_id, '_mbn_template_menus', $menus );
  } else {
    delete_post_meta( $post_id, '_mbn_template_menus' );
  }
}
add_action( 'save_post_mbn_block_template', 'mbn_save_part_meta_box' );

/**
 * Sanitize submitted menu IDs to existing nav menus, preserving order.
 *
 * @param array<int, mixed> $raw Raw submitted menu IDs.
 * @return array<int, int>
 */
function mbn_sanitize_template_menu_ids( array $raw ): array {
  $menus = array();
  foreach ( $raw as $menu_id ) {
    $menu_id = absint( $menu_id );
    if ( $menu_id && is_nav_menu( $menu_id ) ) {
      $menus[] = $menu_id;
    }
  }

  return $menus;
}

/**
 * Enqueue the media picker + helper script for the block-template meta box.
 *
 * @param string $hook Current admin page hook.
 * @return void
 */
function mbn_part_meta_box_assets( string $hook ): void {
  if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
    return;
  }

  $screen = get_current_screen();
  if ( ! $screen || 'mbn_block_template' !== $screen->post_type ) {
    return;
  }

  wp_enqueue_media();
  wp_enqueue_script(
    'mbn-template-parts',
    get_theme_file_uri( 'assets/js/mbn-template-parts.js' ),
    array( 'jquery' ),
    '1.0.0',
    true
  );
  wp_localize_script(
    'mbn-template-parts',
    'mbnTemplateParts',
    array(
		'title'  => __( 'Select logo', 'mbn-theme' ),
		'button' => __( 'Use as logo', 'mbn-theme' ),
    )
  );
}
add_action( 'admin_enqueue_scripts', 'mbn_part_meta_box_assets' );
