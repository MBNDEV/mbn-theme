<?php
/**
 * Nav Menu Sync - Export/Import WordPress navigation menus to/from PHP files.
 *
 * Exports all nav_menu terms + items to template-parts/nav-menus/*.json for Git tracking.
 * On import, resolves post slugs and term slugs to local IDs so menus work across environments.
 *
 * Workflow:
 *   Local  → edit in Appearance > Menus → Export → commit to Git → push
 *   Remote → pull Git → Block Templates > Nav Menu Sync → Import
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Absolute path to the nav menu export directory.
 *
 * @return string No trailing slash.
 */
function custom_theme_nav_menus_export_dir(): string {
	return get_theme_file_path( 'template-parts/nav-menus' );
}

/**
 * Ensure the export directory exists and is writable.
 *
 * @throws Exception If the directory cannot be created or written to.
 */
function custom_theme_nav_menus_ensure_export_dir(): void {
	$dir = custom_theme_nav_menus_export_dir();

  if ( ! is_dir( $dir ) ) {
      wp_mkdir_p( $dir );
  }

  if ( ! is_dir( $dir ) ) {
      throw new Exception( sprintf( 'Could not create directory: %s', esc_html( $dir ) ) );
  }

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
  if ( ! is_writable( $dir ) ) {
      throw new Exception( sprintf( 'Directory is not writable: %s', esc_html( $dir ) ) );
  }
}

/**
 * Resolve a post_type menu item's object_id from a post slug.
 *
 * @param string $post_type Post type (e.g. 'page', 'post').
 * @param string $slug      Post slug.
 * @return int Post ID, or 0 if not found.
 */
function custom_theme_nav_resolve_post_id( string $post_type, string $slug ): int {
	$post = get_page_by_path( $slug, OBJECT, $post_type );
  if ( $post instanceof WP_Post ) {
      return (int) $post->ID;
  }

	$posts = get_posts(
      array(
		  'post_type'              => $post_type,
		  'name'                   => $slug,
		  'post_status'            => 'any',
		  'numberposts'            => 1,
		  'fields'                 => 'ids',
		  'no_found_rows'          => true,
		  'update_post_meta_cache' => false,
		  'update_post_term_cache' => false,
	  )
	);

	return ! empty( $posts ) ? (int) $posts[0] : 0;
}

/**
 * Resolve a taxonomy menu item's object_id from a term slug.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $slug     Term slug.
 * @return int Term ID, or 0 if not found.
 */
function custom_theme_nav_resolve_term_id( string $taxonomy, string $slug ): int {
	$term = get_term_by( 'slug', $slug, $taxonomy );
	return ( $term instanceof WP_Term ) ? (int) $term->term_id : 0;
}

// ---------------------------------------------------------------------------
// Export
// ---------------------------------------------------------------------------

/**
 * Build a map of item DB ID to 0-based array index for parent resolution.
 *
 * @param array $raw_items Raw nav menu item objects.
 * @return array<int, int>
 */
function custom_theme_nav_build_id_index_map( array $raw_items ): array {
	$id_to_index = array();
  foreach ( $raw_items as $i => $item ) {
      $id_to_index[ (int) $item->ID ] = $i;
  }
	return $id_to_index;
}

/**
 * Enrich a menu item entry with a portable object_slug for post_type items.
 *
 * @param array   $entry Raw entry array (passed by reference).
 * @param WP_Post $post  Post object for this item.
 * @return void
 */
function custom_theme_nav_enrich_post_type_entry( array &$entry, WP_Post $post ): void {
	$entry['object_slug'] = $post->post_name;
}

/**
 * Enrich a menu item entry with a portable object_slug for taxonomy items.
 *
 * @param array   $entry Raw entry array (passed by reference).
 * @param WP_Term $term  Term object for this item.
 * @return void
 */
function custom_theme_nav_enrich_taxonomy_entry( array &$entry, WP_Term $term ): void {
	$entry['object_slug']     = $term->slug;
	$entry['object_taxonomy'] = $term->taxonomy;
}

/**
 * Serialize a single raw menu item object to a portable array entry.
 *
 * Post/page items are stored by slug so they resolve correctly on any environment.
 * Parent relationships are stored as 0-based array indices, not database IDs.
 *
 * @param object         $item        Raw nav menu item.
 * @param array<int,int> $id_to_index Map of item DB ID to array index.
 * @return array
 */
function custom_theme_nav_serialize_item( object $item, array $id_to_index ): array {
	$parent_index = -1;
	$parent_db_id = (int) $item->menu_item_parent;

  if ( $parent_db_id > 0 && isset( $id_to_index[ $parent_db_id ] ) ) {
      $parent_index = $id_to_index[ $parent_db_id ];
  }

	$entry = array(
		'title'        => $item->title,
		'type'         => $item->type,
		'object'       => $item->object,
		'url'          => $item->url,
		'target'       => $item->target,
		'attr_title'   => $item->attr_title,
		'description'  => $item->description,
		'classes'      => array_values( array_filter( (array) $item->classes ) ),
		'xfn'          => $item->xfn,
		'order'        => (int) $item->menu_order,
		'parent_index' => $parent_index,
	);

	if ( 'post_type' === $item->type && $item->object_id > 0 ) {
		$post = get_post( (int) $item->object_id );
      if ( $post instanceof WP_Post ) {
          custom_theme_nav_enrich_post_type_entry( $entry, $post );
      }
	}

	if ( 'taxonomy' === $item->type && $item->object_id > 0 ) {
		$term = get_term( (int) $item->object_id );
      if ( $term instanceof WP_Term ) {
          custom_theme_nav_enrich_taxonomy_entry( $entry, $term );
      }
	}

	return $entry;
}

/**
 * Resolve which registered theme locations the given menu is assigned to.
 *
 * @param WP_Term $menu Nav menu term.
 * @return string[] Array of location slugs.
 */
function custom_theme_nav_resolve_menu_locations( WP_Term $menu ): array {
	$all_locations  = get_nav_menu_locations();
	$menu_locations = array();

  foreach ( $all_locations as $location => $menu_id ) {
    if ( (int) $menu_id === (int) $menu->term_id ) {
        $menu_locations[] = $location;
    }
  }

	return $menu_locations;
}

/**
 * Build the PHP file content string for a nav menu export.
 *
 * @param WP_Term $menu          Nav menu term.
 * @param array   $exported_data Data array to serialize.
 * @return string
 * @throws Exception If JSON encoding fails.
 */
function custom_theme_nav_build_export_file_content( WP_Term $menu, array $exported_data ): string {
	$encoded = wp_json_encode( $exported_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

  if ( false === $encoded ) {
      throw new Exception( sprintf( 'Failed to encode menu "%s" as JSON.', esc_html( $menu->name ) ) );
  }

	return $encoded;
}

/**
 * Write content to a file using WP_Filesystem.
 *
 * @param string $file_path    Absolute path to write.
 * @param string $file_content Content to write.
 * @throws Exception On write failure.
 */
function custom_theme_nav_write_file( string $file_path, string $file_content ): void {
	global $wp_filesystem;

  if ( empty( $wp_filesystem ) ) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
      WP_Filesystem();
  }

  if ( false === $wp_filesystem->put_contents( $file_path, $file_content, FS_CHMOD_FILE ) ) {
      throw new Exception( sprintf( 'Failed to write: %s', esc_html( $file_path ) ) );
  }
}

/**
 * Serialize a single WP_Term (nav_menu) to a PHP file.
 *
 * Post/page items are stored by slug so they resolve correctly on any environment.
 * Custom (external) links are stored verbatim — use relative URLs (e.g. /contact)
 * so they remain portable.
 * Parent relationships are stored as 0-based array indices, not database IDs.
 *
 * @param WP_Term $menu Nav menu term.
 * @return string Absolute path to the written file.
 * @throws Exception On write failure.
 */
function custom_theme_export_nav_menu( WP_Term $menu ): string {
	$raw_items   = wp_get_nav_menu_items( $menu->term_id );
	$raw_items   = is_array( $raw_items ) ? $raw_items : array();
	$id_to_index = custom_theme_nav_build_id_index_map( $raw_items );

	$items = array();
  foreach ( $raw_items as $item ) {
      $items[] = custom_theme_nav_serialize_item( $item, $id_to_index );
  }

	$data = array(
		'name'      => $menu->name,
		'slug'      => $menu->slug,
		'locations' => custom_theme_nav_resolve_menu_locations( $menu ),
		'items'     => $items,
	);

	$file_content = custom_theme_nav_build_export_file_content( $menu, $data );

	custom_theme_nav_menus_ensure_export_dir();

	$file_path = custom_theme_nav_menus_export_dir() . '/' . sanitize_file_name( $menu->slug ) . '.json';

	custom_theme_nav_write_file( $file_path, $file_content );

	return $file_path;
}

/**
 * Export every registered nav menu to its own PHP file.
 *
 * @param array $menu_slugs Optional array of menu slugs to export. Empty = all menus.
 * @return array{ exported: int, errors: string[] }
 * @throws Exception If no menus selected.
 */
function custom_theme_export_all_nav_menus( array $menu_slugs = array() ): array {
	$all_menus = wp_get_nav_menus();

  if ( ! empty( $menu_slugs ) ) {
      $menus = array_filter(
        $all_menus,
        function ( $menu ) use ( $menu_slugs ) {
            return in_array( $menu->slug, $menu_slugs, true );
        }
      );
  } else {
      $menus = $all_menus;
  }

  if ( empty( $menus ) ) {
      throw new Exception( esc_html__( 'Please select at least one menu to export.', 'mbn-theme' ) );
  }

	$exported = 0;
	$errors   = array();

  foreach ( $menus as $menu ) {
    try {
        custom_theme_export_nav_menu( $menu );
        ++$exported;
    } catch ( Exception $e ) {
        $errors[] = $menu->name . ': ' . $e->getMessage();
    }
  }

	return array(
		'exported' => $exported,
		'errors'   => $errors,
	);
}

// ---------------------------------------------------------------------------
// Import
// ---------------------------------------------------------------------------

/**
 * Validate the raw data array from a nav menu PHP file.
 *
 * @param mixed  $data     Data loaded from the file.
 * @param string $filename File basename for error messages.
 * @throws Exception If required keys are missing or types are wrong.
 */
function custom_theme_validate_nav_menu_file_data( $data, string $filename ): void {
  if ( ! is_array( $data ) ) {
      throw new Exception( sprintf( 'Invalid format in %s: expected array.', esc_html( $filename ) ) );
  }

  foreach ( array( 'name', 'slug', 'items' ) as $required ) {
    if ( ! isset( $data[ $required ] ) ) {
        throw new Exception( sprintf( 'Missing required key "%s" in %s.', esc_html( $required ), esc_html( $filename ) ) );
    }
  }
}

/**
 * Create or retrieve a nav menu by slug; update its name if it already exists.
 *
 * @param string $name Menu display name.
 * @param string $slug Menu slug.
 * @return int Menu term_id.
 * @throws Exception If creation fails.
 */
function custom_theme_nav_get_or_create_menu( string $name, string $slug ): int {
	$existing = wp_get_nav_menu_object( $slug );
  if ( $existing instanceof WP_Term ) {
      return (int) $existing->term_id;
  }

	$result = wp_create_nav_menu( $name );
  if ( is_wp_error( $result ) ) {
      throw new Exception( esc_html( $result->get_error_message() ) );
  }

	return (int) $result;
}

/**
 * Resolve the object-id args for a post_type menu item.
 *
 * @param array  $args      Args array being built (passed by reference).
 * @param string $post_type Post type slug.
 * @param string $slug      Post slug.
 * @return void
 */
function custom_theme_nav_apply_post_type_object_id( array &$args, string $post_type, string $slug ): void {
	$post_id = custom_theme_nav_resolve_post_id( $post_type, $slug );
  if ( $post_id > 0 ) {
      $args['menu-item-object-id'] = $post_id;
      $args['menu-item-url']       = (string) get_permalink( $post_id );
  } else {
      // Post not found in this environment — fall back to custom link using the stored URL.
      $args['menu-item-type']   = 'custom';
      $args['menu-item-object'] = 'custom';
  }
}

/**
 * Resolve the object-id args for a taxonomy menu item.
 *
 * @param array  $args        Args array being built (passed by reference).
 * @param string $object_type Taxonomy slug fallback when object_taxonomy is absent.
 * @param string $slug        Term slug.
 * @param array  $item        Raw serialized item (may contain object_taxonomy key).
 * @return void
 */
function custom_theme_nav_apply_taxonomy_object_id( array &$args, string $object_type, string $slug, array $item ): void {
	$taxonomy = sanitize_key( $item['object_taxonomy'] ?? $object_type );
	$term_id  = custom_theme_nav_resolve_term_id( $taxonomy, $slug );

  if ( $term_id > 0 ) {
      $args['menu-item-object-id'] = $term_id;
      $link                        = get_term_link( $term_id, $taxonomy );
      $args['menu-item-url']       = is_string( $link ) ? $link : $args['menu-item-url'];
  } else {
      // Term not found in this environment — fall back to custom link using the stored URL.
      $args['menu-item-type']   = 'custom';
      $args['menu-item-object'] = 'custom';
  }
}

/**
 * Resolve and apply object-ID args to a menu item args array based on item type.
 *
 * Extracted from custom_theme_nav_build_item_args to reduce cyclomatic complexity.
 *
 * @param array $args Args array being built (passed by reference).
 * @param array $item Serialized menu item.
 * @return void
 */
function custom_theme_nav_resolve_object_id_args( array &$args, array $item ): void {
	$type = $args['menu-item-type'];
	$slug = sanitize_title( $item['object_slug'] ?? '' );

  if ( '' === $slug ) {
      return;
  }

  if ( 'post_type' === $type ) {
      custom_theme_nav_apply_post_type_object_id( $args, $args['menu-item-object'], $slug );
  } elseif ( 'taxonomy' === $type ) {
      custom_theme_nav_apply_taxonomy_object_id( $args, $args['menu-item-object'], $slug, $item );
  }
}

/**
 * Merge a raw serialized menu item with safe scalar defaults.
 *
 * Centralises all null-coalescing / ternary logic so that
 * custom_theme_nav_build_item_args remains below the complexity threshold.
 *
 * @param array $item Raw serialized menu item.
 * @param int   $pos  1-based fallback menu order position.
 * @return array Item with every expected key guaranteed to be present.
 */
function custom_theme_nav_normalize_item( array $item, int $pos ): array {
	return wp_parse_args(
      $item,
      array(
		  'title'       => '',
		  'type'        => 'custom',
		  'object'      => 'custom',
		  'url'         => '',
		  'target'      => '',
		  'attr_title'  => '',
		  'description' => '',
		  'classes'     => array(),
		  'xfn'         => '',
		  'order'       => $pos,
		  'object_slug' => '',
	  )
	);
}

/**
 * Build the wp_update_nav_menu_item() args for one serialized item.
 * Does NOT set parent-id — that is handled in a second pass.
 *
 * @param array $item Serialized menu item.
 * @param int   $pos  1-based menu order position.
 * @return array
 */
function custom_theme_nav_build_item_args( array $item, int $pos ): array {
	$item = custom_theme_nav_normalize_item( $item, $pos );

	$args = array(
		'menu-item-title'       => sanitize_text_field( $item['title'] ),
		'menu-item-type'        => sanitize_text_field( $item['type'] ),
		'menu-item-object'      => sanitize_text_field( $item['object'] ),
		'menu-item-url'         => esc_url_raw( $item['url'] ),
		'menu-item-target'      => sanitize_text_field( $item['target'] ),
		'menu-item-attr-title'  => sanitize_text_field( $item['attr_title'] ),
		'menu-item-description' => sanitize_text_field( $item['description'] ),
		'menu-item-classes'     => implode( ' ', array_map( 'sanitize_html_class', (array) $item['classes'] ) ),
		'menu-item-xfn'         => sanitize_text_field( $item['xfn'] ),
		'menu-item-position'    => (int) $item['order'],
		'menu-item-status'      => 'publish',
		'menu-item-parent-id'   => 0,
	);

	custom_theme_nav_resolve_object_id_args( $args, $item );

	return $args;
}

/**
 * Delete all existing items in a nav menu for a clean slate before re-import.
 *
 * @param int $menu_id Menu term_id.
 * @return void
 */
function custom_theme_nav_clear_menu_items( int $menu_id ): void {
	$existing = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );
  if ( ! is_array( $existing ) ) {
      return;
  }

  foreach ( $existing as $old_item ) {
      wp_delete_post( (int) $old_item->ID, true );
  }
}

/**
 * Snapshot existing menu items so they can be restored on import failure.
 *
 * @param int $menu_id Menu term_id.
 * @return array Snapshot of menu item args, each with _original_id and _original_parent_id.
 */
function custom_theme_nav_snapshot_items( int $menu_id ): array {
	$items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );
  if ( ! is_array( $items ) ) {
      return array();
  }

	$snapshot = array();

  foreach ( $items as $item ) {
      $snapshot[] = array(
		  'menu-item-title'       => $item->title,
		  'menu-item-url'         => $item->url,
		  'menu-item-status'      => 'publish',
		  'menu-item-position'    => $item->menu_order,
		  'menu-item-type'        => $item->type,
		  'menu-item-object'      => $item->object,
		  'menu-item-object-id'   => $item->object_id,
		  'menu-item-parent-id'   => 0,
		  'menu-item-target'      => $item->target,
		  'menu-item-classes'     => is_array( $item->classes ) ? implode( ' ', $item->classes ) : '',
		  'menu-item-attr-title'  => $item->attr_title,
		  'menu-item-description' => $item->description,
		  '_original_id'          => (int) $item->ID,
		  '_original_parent_id'   => (int) $item->menu_item_parent,
	  );
  }

	return $snapshot;
}

/**
 * Restore menu items from a snapshot taken before a failed import.
 *
 * @param int   $menu_id  Menu term_id.
 * @param array $snapshot Snapshot from custom_theme_nav_snapshot_items().
 * @return void
 */
function custom_theme_nav_restore_items_from_snapshot( int $menu_id, array $snapshot ): void {
  if ( empty( $snapshot ) ) {
      return;
  }

	$id_map = array(); // old_id => new_id

  // First pass: re-create all items flat (parent relationships wired in second pass).
  foreach ( $snapshot as $item ) {
      $args = $item;
      unset( $args['_original_id'], $args['_original_parent_id'] );
      $new_id = wp_update_nav_menu_item( $menu_id, 0, $args );

    if ( ! is_wp_error( $new_id ) ) {
        $id_map[ $item['_original_id'] ] = (int) $new_id;
    }
  }

  // Second pass: restore parent/child relationships.
  foreach ( $snapshot as $item ) {
      $orig_parent = $item['_original_parent_id'];

    if ( $orig_parent > 0 && isset( $id_map[ $orig_parent ], $id_map[ $item['_original_id'] ] ) ) {
        wp_update_nav_menu_item(
          $menu_id,
          $id_map[ $item['_original_id'] ],
          array(
			  'menu-item-parent-id' => $id_map[ $orig_parent ],
			  'menu-item-status'    => 'publish',
		  )
        );
    }
  }
}

/**
 * First-pass insert: create all items and return an array-index to new post ID map.
 *
 * @param int   $menu_id Menu term_id.
 * @param array $items   Serialized items array.
 * @return array<int, int>
 */
function custom_theme_nav_insert_items_first_pass( int $menu_id, array $items ): array {
	$new_ids = array();

  foreach ( $items as $i => $item ) {
      $args   = custom_theme_nav_build_item_args( $item, $i + 1 );
      $new_id = wp_update_nav_menu_item( $menu_id, 0, $args );

    if ( ! is_wp_error( $new_id ) ) {
        $new_ids[ $i ] = (int) $new_id;
    }
  }

	return $new_ids;
}

/**
 * Second-pass: wire up parent/child relationships using the new post IDs.
 *
 * @param int             $menu_id Menu term_id.
 * @param array           $items   Serialized items array.
 * @param array<int, int> $new_ids Array-index to new post ID map.
 * @return void
 */
function custom_theme_nav_wire_parent_relationships( int $menu_id, array $items, array $new_ids ): void {
  foreach ( $items as $i => $item ) {
      $parent_index = (int) ( $item['parent_index'] ?? -1 );

    if ( $parent_index < 0 || ! isset( $new_ids[ $parent_index ], $new_ids[ $i ] ) ) {
        continue;
    }

      wp_update_nav_menu_item(
        $menu_id,
        $new_ids[ $i ],
        array(
			'menu-item-parent-id' => $new_ids[ $parent_index ],
			'menu-item-status'    => 'publish',
		)
      );
  }
}

/**
 * Assign a menu to theme locations.
 *
 * @param int      $menu_id   Menu term_id.
 * @param string[] $locations Array of theme location slugs to assign.
 * @return void
 */
function custom_theme_nav_assign_locations( int $menu_id, array $locations ): void {
  if ( empty( $locations ) ) {
      return;
  }

	$current = get_nav_menu_locations();

  foreach ( $locations as $location ) {
      $current[ sanitize_key( $location ) ] = $menu_id;
  }

	set_theme_mod( 'nav_menu_locations', $current );
}

/**
 * Normalize nav import mode to a supported value.
 *
 * @param string $mode Raw import mode.
 * @return string One of: skip_existing, update_existing, create_copy.
 */
function custom_theme_nav_normalize_import_mode( string $mode ): string {
	$normalized = sanitize_key( $mode );
	$allowed    = array( 'skip_existing', 'update_existing', 'create_copy' );

  if ( ! in_array( $normalized, $allowed, true ) ) {
      return 'skip_existing';
  }

	return $normalized;
}

/**
 * Generate a unique menu slug for imported copies.
 *
 * @param string $base_slug Base menu slug.
 * @return string Unique menu slug.
 */
function custom_theme_nav_generate_copy_slug( string $base_slug ): string {
	$base_slug = sanitize_title( $base_slug );
	$candidate = $base_slug . '-imported-copy';
	$index     = 2;

  while ( wp_get_nav_menu_object( $candidate ) instanceof WP_Term ) {
      $candidate = $base_slug . '-imported-copy-' . $index;
      ++$index;
  }

	return $candidate;
}

/**
 * Import a single nav menu from a deserialized data array.
 *
 * Existing items are cleared before re-importing so the result always
 * matches the file exactly (no stale items left behind).
 *
 * @param array  $data Data from a nav menu PHP file.
 * @param string $import_mode Import mode: skip_existing, update_existing, create_copy.
 * @return array{ created: bool, action: string }
 * @throws Exception On failure.
 */
function custom_theme_import_single_nav_menu( array $data, string $import_mode = 'skip_existing' ): array {
	$name        = sanitize_text_field( $data['name'] );
	$slug        = sanitize_title( $data['slug'] );
	$items       = is_array( $data['items'] ) ? $data['items'] : array();
	$locs        = is_array( $data['locations'] ?? null ) ? $data['locations'] : array();
	$import_mode = custom_theme_nav_normalize_import_mode( $import_mode );

	$existing_menu = wp_get_nav_menu_object( $slug );
	$exists        = $existing_menu instanceof WP_Term;

  if ( $exists && 'skip_existing' === $import_mode ) {
      return array(
          'created' => false,
          'action'  => 'skipped',
      );
  }

  if ( $exists && 'create_copy' === $import_mode ) {
      $slug = custom_theme_nav_generate_copy_slug( $slug );
      $name = sprintf( '%s (Imported Copy)', $name );
  }

	$created = ! ( wp_get_nav_menu_object( $slug ) instanceof WP_Term );
	$menu_id = custom_theme_nav_get_or_create_menu( $name, $slug );

	// Snapshot existing items before the destructive clear so we can restore on failure.
	$backup = custom_theme_nav_snapshot_items( $menu_id );

	custom_theme_nav_clear_menu_items( $menu_id );

	$new_ids = custom_theme_nav_insert_items_first_pass( $menu_id, $items );

	// Rollback: if items were expected but none were inserted, restore backup and abort.
  if ( ! empty( $items ) && empty( $new_ids ) ) {
      custom_theme_nav_restore_items_from_snapshot( $menu_id, $backup );
      throw new Exception(
        sprintf(
              // translators: %s is the menu slug.
          esc_html__( 'Import failed for menu "%s": no items were inserted. Rolled back to previous state.', 'mbn-theme' ),
          esc_html( $slug )
        )
      );
  }

	custom_theme_nav_wire_parent_relationships( $menu_id, $items, $new_ids );

	custom_theme_nav_assign_locations( $menu_id, $locs );

  if ( $exists && 'create_copy' === $import_mode ) {
      return array(
          'created' => true,
          'action'  => 'copied',
      );
  }

	return array(
		'created' => $created,
		'action'  => $created ? 'created' : 'updated',
	);
}

/**
 * Import all nav menus from PHP files in the export directory.
 *
 * @param array  $selected_files Optional array of filenames to import. Empty = all files.
 * @param string $import_mode    Import mode: skip_existing, update_existing, create_copy.
 * @return array{ created: int, updated: int, skipped: int, copied: int, errors: string[] }
 * @throws Exception If directory is missing or has no files.
 */
function custom_theme_import_all_nav_menus( array $selected_files = array(), string $import_mode = 'skip_existing' ): array { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
	$dir         = custom_theme_nav_menus_export_dir();
	$import_mode = custom_theme_nav_normalize_import_mode( $import_mode );

  if ( ! is_dir( $dir ) ) {
      throw new Exception( 'Nav menus directory not found. Export menus first.' );
  }

	$all_files = glob( $dir . '/*.json' );
  if ( empty( $all_files ) ) {
      throw new Exception( sprintf( 'No nav menu files found in %s. Export first and commit to Git.', esc_html( $dir ) ) );
  }

  if ( ! empty( $selected_files ) ) {
      $files = array_filter(
        $all_files,
        function ( $f ) use ( $selected_files ) {
            return in_array( basename( $f ), $selected_files, true );
        }
      );
      $files = array_values( $files );
  } else {
      $files = $all_files;
  }

  if ( empty( $files ) ) {
      throw new Exception( esc_html__( 'Please select at least one menu file to import.', 'mbn-theme' ) );
  }

	$created = 0;
	$updated = 0;
	$skipped = 0;
	$copied  = 0;
	$errors  = array();

  foreach ( $files as $file ) {
    try {
        $filename = basename( $file );
        $raw      = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $data     = $raw ? json_decode( $raw, true ) : null;
      if ( null === $data || JSON_ERROR_NONE !== json_last_error() ) {
          throw new Exception( sprintf( 'Invalid JSON in file: %s', esc_html( $filename ) ) );
      }
        custom_theme_validate_nav_menu_file_data( $data, $filename );
				$result = custom_theme_import_single_nav_menu( $data, $import_mode );

      if ( isset( $result['action'] ) && 'skipped' === $result['action'] ) {
          ++$skipped;
      } elseif ( isset( $result['action'] ) && 'copied' === $result['action'] ) {
          ++$copied;
      } elseif ( $result['created'] ) {
          ++$created;
      } else {
          ++$updated;
      }
    } catch ( Exception $e ) {
        $errors[] = basename( $file ) . ': ' . $e->getMessage();
    }
  }

	return array(
		'created' => $created,
		'updated' => $updated,
		'skipped' => $skipped,
		'copied'  => $copied,
		'errors'  => $errors,
	);
}

// ---------------------------------------------------------------------------
// Admin form handlers
// ---------------------------------------------------------------------------

/**
 * Build the admin notice message for an export result.
 *
 * @param array $result Result from custom_theme_export_all_nav_menus().
 * @return string Message string.
 */
function custom_theme_nav_build_export_message( array $result ): string {
	$message = sprintf(
		// translators: %d is the number of menus exported.
      __( '%d menu(s) exported to template-parts/nav-menus/ successfully!', 'mbn-theme' ),
      $result['exported']
	);

  if ( ! empty( $result['errors'] ) ) {
      $message .= ' Errors: ' . implode( '; ', array_map( 'esc_html', $result['errors'] ) );
  }

	return $message;
}

/**
 * Build the admin notice message for an import result.
 *
 * @param array $result Result from custom_theme_import_all_nav_menus().
 * @return string Message string.
 */
function custom_theme_nav_build_import_message( array $result ): string {
	$message = sprintf(
		// translators: %1$d menus created, %2$d updated.
      __( 'Import complete! Created: %1$d, Updated: %2$d', 'mbn-theme' ),
      $result['created'],
      $result['updated']
	);

  if ( ! empty( $result['copied'] ) ) {
      // translators: %d is number of menus created as imported copies.
          $message .= sprintf( ' | ' . __( 'Copied: %d', 'mbn-theme' ), (int) $result['copied'] );
  }

  if ( ! empty( $result['skipped'] ) ) {
      // translators: %d is number of existing menus skipped.
          $message .= sprintf( ' | ' . __( 'Skipped existing: %d', 'mbn-theme' ), (int) $result['skipped'] );
  }

  if ( ! empty( $result['errors'] ) ) {
      $message .= ' | Errors: ' . implode( '; ', array_map( 'esc_html', $result['errors'] ) );
  }

	return $message;
}

/**
 * Handle an export form submission.
 *
 * @param array $menu_slugs Menu slugs to export (already sanitized).
 * @return void
 */
function custom_theme_handle_export_action( array $menu_slugs ): void {
  try {
    if ( empty( $menu_slugs ) ) {
        add_settings_error(
          'custom_theme_nav_sync',
          'export_no_selection',
          esc_html__( 'Please select at least one menu to export.', 'mbn-theme' ),
          'error'
        );
        return;
    }

      $result  = custom_theme_export_all_nav_menus( $menu_slugs );
      $message = custom_theme_nav_build_export_message( $result );
      $type    = empty( $result['errors'] ) ? 'updated' : 'warning';
      add_settings_error( 'custom_theme_nav_sync', 'export_success', $message, $type );
  } catch ( Exception $e ) {
      add_settings_error(
        'custom_theme_nav_sync',
        'export_error',
        'Export failed: ' . esc_html( $e->getMessage() ),
        'error'
      );
  }
}

/**
 * Handle an import form submission.
 *
 * @param array  $selected_files Menu filenames to import (already sanitized).
 * @param string $import_mode    Import mode: skip_existing, update_existing, create_copy.
 * @return void
 */
function custom_theme_handle_import_action( array $selected_files, string $import_mode = 'skip_existing' ): void {
  try {
    if ( empty( $selected_files ) ) {
        add_settings_error(
          'custom_theme_nav_sync',
          'import_no_selection',
          esc_html__( 'Please select at least one menu file to import.', 'mbn-theme' ),
          'error'
        );
        return;
    }

	$result    = custom_theme_import_all_nav_menus( $selected_files, $import_mode );
      $message = custom_theme_nav_build_import_message( $result );
      $type    = empty( $result['errors'] ) ? 'updated' : 'warning';
      add_settings_error( 'custom_theme_nav_sync', 'import_success', $message, $type );
  } catch ( Exception $e ) {
      add_settings_error(
        'custom_theme_nav_sync',
        'import_error',
        'Import failed: ' . esc_html( $e->getMessage() ),
        'error'
      );
  }
}

/**
 * Process export/import form submissions.
 *
 * @return void
 */
function custom_theme_handle_nav_menu_sync_actions(): void { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
  if ( ! isset( $_POST['custom_theme_nav_sync_action'] ) ) {
      return;
  }

  if ( ! current_user_can( 'manage_options' ) ) {
      return;
  }

	check_admin_referer( 'custom_theme_nav_sync', 'custom_theme_nav_sync_nonce' );

	$action = sanitize_text_field( wp_unslash( $_POST['custom_theme_nav_sync_action'] ) );

  if ( 'export_menus' === $action ) {
      $menu_slugs = isset( $_POST['menu_slugs'] ) ? array_map( 'sanitize_title', (array) $_POST['menu_slugs'] ) : array();
      custom_theme_handle_export_action( $menu_slugs );
  } elseif ( 'import_menus' === $action ) {
      $selected_files      = isset( $_POST['menu_files'] ) ? array_map( 'sanitize_file_name', (array) $_POST['menu_files'] ) : array();
			$import_mode   = isset( $_POST['import_mode'] )
				? custom_theme_nav_normalize_import_mode( sanitize_text_field( wp_unslash( $_POST['import_mode'] ) ) )
				: 'skip_existing';
			$sync_password = isset( $_POST['sync_password'] )
				? (string) wp_unslash( $_POST['sync_password'] )
				: '';

    if ( ! custom_theme_verify_sync_password( $sync_password ) ) {
        $message = '' === custom_theme_get_sync_password()
            ? esc_html__( 'Import blocked: sync password is not configured. Define CUSTOM_THEME_SYNC_PASSWORD in wp-config.php or environment.', 'mbn-theme' )
            : esc_html__( 'Import blocked: invalid sync password.', 'mbn-theme' );

        add_settings_error( 'custom_theme_nav_sync', 'invalid_sync_password', $message, 'error' );
        return;
    }
			custom_theme_handle_import_action( $selected_files, $import_mode );
  }
}
add_action( 'admin_init', 'custom_theme_handle_nav_menu_sync_actions' );

// ---------------------------------------------------------------------------
// Admin page
// ---------------------------------------------------------------------------

/**
 * Register Nav Menu Sync under the WordPress Tools menu.
 *
 * @return void
 */
function custom_theme_add_nav_menu_sync_page(): void {
	add_management_page(
      __( 'Nav Menu Sync', 'mbn-theme' ),
      __( 'Nav Menu Sync', 'mbn-theme' ),
      'manage_options',
      'nav-menu-sync',
      'custom_theme_render_nav_menu_sync_page'
	);
}
add_action( 'admin_menu', 'custom_theme_add_nav_menu_sync_page' );

/**
 * Render the menu status table rows.
 *
 * @param array  $menus      Array of WP_Term nav menu objects.
 * @param array  $assigned   Result of get_nav_menu_locations().
 * @param array  $registered Result of get_registered_nav_menus().
 * @param string $export_dir Absolute export directory path.
 * @return void
 */
function custom_theme_render_nav_menu_table_rows( array $menus, array $assigned, array $registered, string $export_dir ): void {
  foreach ( $menus as $menu ) {
      $menu_items = wp_get_nav_menu_items( $menu->term_id );
      $item_count = is_array( $menu_items ) ? count( $menu_items ) : 0;
      $menu_locs  = array();

    foreach ( $assigned as $loc => $mid ) {
      if ( (int) $mid === (int) $menu->term_id && isset( $registered[ $loc ] ) ) {
        $menu_locs[] = esc_html( $registered[ $loc ] ) . ' <code>(' . esc_html( $loc ) . ')</code>';
      }
    }

      $file_path = $export_dir . '/' . sanitize_file_name( $menu->slug ) . '.json';
      $has_file  = file_exists( $file_path );
    ?>
		<tr>
			<td>
				<input type="checkbox" name="menu_slugs[]" value="<?php echo esc_attr( $menu->slug ); ?>">
			</td>
			<td><strong><?php echo esc_html( $menu->name ); ?></strong></td>
			<td><code><?php echo esc_html( $menu->slug ); ?></code></td>
			<td><?php echo esc_html( (string) $item_count ); ?></td>
			<td>
              <?php
              if ( $menu_locs ) {
                  echo wp_kses(
                    implode( '<br>', $menu_locs ),
                    array(
						'br'   => array(),
						'code' => array(),
					)
                  );
              } else {
                  echo '&mdash;';
              }
              ?>
			</td>
			<td>
              <?php if ( $has_file ) : ?>
					<span style="color:#46b450;">&#10003; <code><?php echo esc_html( $menu->slug ); ?>.json</code></span>
				<?php else : ?>
					<span style="color:#dba617;">&#9888; <?php esc_html_e( 'Not yet exported', 'mbn-theme' ); ?></span>
				<?php endif; ?>
			</td>
		</tr>
		<?php
  }
}

/**
 * Render the Nav Menu Sync admin page.
 *
 * @return void
 */
function custom_theme_render_nav_menu_sync_page(): void { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
	$menus          = wp_get_nav_menus();
	$registered     = get_registered_nav_menus();
	$assigned       = get_nav_menu_locations();
	$export_dir     = custom_theme_nav_menus_export_dir();
	$glob_result    = is_dir( $export_dir ) ? glob( $export_dir . '/*.json' ) : array();
	$exported_files = is_array( $glob_result ) ? $glob_result : array();
  ?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Nav Menu Sync', 'mbn-theme' ); ?></h1>
		<p><?php esc_html_e( 'Export and import navigation menus using JSON files in template-parts/nav-menus/.', 'mbn-theme' ); ?></p>

		<?php settings_errors( 'custom_theme_nav_sync' ); ?>

		<?php if ( ! empty( $menus ) ) : ?>
			<!-- Export menus form - wraps both menu table and export button -->
			<form method="post">
				<?php wp_nonce_field( 'custom_theme_nav_sync', 'custom_theme_nav_sync_nonce' ); ?>
				<input type="hidden" name="custom_theme_nav_sync_action" value="export_menus">
				
				<!-- Registered menus status table -->
				<div class="card" style="max-width:860px;">
					<h2><?php esc_html_e( 'Current Menus', 'mbn-theme' ); ?></h2>
					<table class="widefat striped" style="margin-top:10px;">
						<thead>
							<tr>
								<th style="width:40px;">
									<input type="checkbox" id="select-all-menus" title="Select all">
								</th>
								<th><?php esc_html_e( 'Menu Name', 'mbn-theme' ); ?></th>
								<th><?php esc_html_e( 'Slug', 'mbn-theme' ); ?></th>
								<th><?php esc_html_e( 'Items', 'mbn-theme' ); ?></th>
								<th><?php esc_html_e( 'Theme Location', 'mbn-theme' ); ?></th>
								<th><?php esc_html_e( 'Exported File', 'mbn-theme' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php custom_theme_render_nav_menu_table_rows( $menus, $assigned, $registered, $export_dir ); ?>
						</tbody>
					</table>
					<script>
					document.getElementById( 'select-all-menus' ).addEventListener( 'change', function () {
						document.querySelectorAll( 'input[name="menu_slugs[]"]' ).forEach( function ( cb ) {
							cb.checked = this.checked;
						}, this );
					} );
					</script>
				</div>

				<!-- Export card -->
				<div class="card" style="max-width:860px;margin-top:20px;">
					<h2>&#x1F4E4; <?php esc_html_e( 'Export Menus to Files', 'mbn-theme' ); ?></h2>
					<p>
						<?php esc_html_e( 'Select menus above to export to', 'mbn-theme' ); ?>
						<code>template-parts/nav-menus/{slug}.json</code>.
						<?php esc_html_e( 'Use these files for version control and deployments.', 'mbn-theme' ); ?>
					</p>
					<button type="submit" class="button button-secondary" style="margin-top:15px;">
						&#x1F4E4; <?php esc_html_e( 'Export Selected Menus to Files', 'mbn-theme' ); ?>
					</button>
				</div>
			</form>
		<?php else : ?>
			<div class="card" style="max-width:860px;">
				<h2><?php esc_html_e( 'Current Menus', 'mbn-theme' ); ?></h2>
				<p><em><?php esc_html_e( 'No menus found. Create menus via Appearance > Menus first.', 'mbn-theme' ); ?></em></p>
			</div>
		<?php endif; ?>

		<!-- Import card -->
		<div class="card" style="max-width:860px;margin-top:20px;">
			<h2>&#x1F4E5; <?php esc_html_e( 'Import Menus from Files', 'mbn-theme' ); ?></h2>
			<p>
				<?php esc_html_e( 'Select menu files from', 'mbn-theme' ); ?>
				<code>template-parts/nav-menus/*.json</code>
				<?php esc_html_e( 'to import. Import Mode controls whether existing menu slugs are skipped, updated, or copied.', 'mbn-theme' ); ?>
			</p>
			<?php if ( empty( $exported_files ) ) : ?>
				<p><em style="color:#d63638;">
					<?php esc_html_e( 'No exported files found. Export first, commit to Git, and pull on this environment.', 'mbn-theme' ); ?>
				</em></p>
			<?php else : ?>
				<p>
					<strong style="color:#d63638;">&#9888; <?php esc_html_e( 'Warning:', 'mbn-theme' ); ?></strong>
					<?php esc_html_e( 'Clear-and-rebuild only happens when you choose Update existing menus. Skip and Create copy modes do not overwrite matching menu slugs.', 'mbn-theme' ); ?>
				</p>
				<form method="post" style="margin-top:15px;">
					<?php wp_nonce_field( 'custom_theme_nav_sync', 'custom_theme_nav_sync_nonce' ); ?>
					<input type="hidden" name="custom_theme_nav_sync_action" value="import_menus">
					<div style="background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;padding:12px 14px;margin:0 0 15px 0;">
						<label for="menu-import-mode" style="display:block;font-weight:600;margin-bottom:6px;">
							<?php esc_html_e( 'Import Mode', 'mbn-theme' ); ?>
						</label>
						<select id="menu-import-mode" name="import_mode" style="min-width:280px;">
							<option value="skip_existing" selected><?php esc_html_e( 'Skip existing menus (safe)', 'mbn-theme' ); ?></option>
							<option value="update_existing"><?php esc_html_e( 'Update existing menus', 'mbn-theme' ); ?></option>
							<option value="create_copy"><?php esc_html_e( 'Create imported copies for existing menus', 'mbn-theme' ); ?></option>
						</select>
						<p style="margin:6px 0 0;color:#646970;">
							<?php esc_html_e( 'Choose how to handle files whose menu slug already exists in the database.', 'mbn-theme' ); ?>
						</p>
						<?php if ( custom_theme_is_sync_password_required() ) : ?>
							<label for="menu-sync-password" style="display:block;font-weight:600;margin:10px 0 6px;">
								<?php esc_html_e( 'Sync Password', 'mbn-theme' ); ?>
							</label>
							<input type="password" id="menu-sync-password" name="sync_password" autocomplete="current-password" style="min-width:280px;" required>
							<p style="margin:6px 0 0;color:#646970;">
								<?php esc_html_e( 'Required on production before import runs.', 'mbn-theme' ); ?>
							</p>
						<?php endif; ?>
					</div>
					
					<table class="widefat" style="margin-bottom:15px;">
						<thead>
							<tr>
								<th style="width:40px;">
									<input type="checkbox" id="select-all-import-menus" title="Select all">
								</th>
								<th><?php esc_html_e( 'Menu File', 'mbn-theme' ); ?></th>
								<th><?php esc_html_e( 'Slug', 'mbn-theme' ); ?></th>
								<th><?php esc_html_e( 'Action', 'mbn-theme' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $exported_files as $f ) : ?>
								<?php
								$filename = basename( $f );
								$_raw     = file_get_contents( $f ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
								$data     = ( false !== $_raw ) ? json_decode( $_raw, true ) : null;
								if ( null === $data || JSON_ERROR_NONE !== json_last_error() ) {
									continue;
								}
								$slug   = isset( $data['slug'] ) ? $data['slug'] : pathinfo( $filename, PATHINFO_FILENAME );
								$exists = wp_get_nav_menu_object( $slug ) instanceof WP_Term;
								?>
								<tr>
									<td>
										<input type="checkbox" name="menu_files[]" value="<?php echo esc_attr( $filename ); ?>" checked>
									</td>
									<td><code><?php echo esc_html( $filename ); ?></code></td>
									<td><code><?php echo esc_html( $slug ); ?></code></td>
									<td>
										<?php if ( $exists ) : ?>
											<span style="color:#f0b849;">&#8635; <?php esc_html_e( 'Will Update', 'mbn-theme' ); ?></span>
										<?php else : ?>
											<span style="color:#46b450;">+ <?php esc_html_e( 'Will Create', 'mbn-theme' ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					
					<script>
					document.getElementById( 'select-all-import-menus' ).addEventListener( 'change', function () {
						document.querySelectorAll( 'input[name="menu_files[]"]' ).forEach( function ( cb ) {
							cb.checked = this.checked;
						}, this );
					} );
					</script>
					
					<button type="submit" class="button button-primary">
						&#x1F4E5; <?php esc_html_e( 'Import Selected Menus from Files', 'mbn-theme' ); ?>
					</button>
				</form>
			<?php endif; ?>
		</div>

	</div>
	<?php
}
