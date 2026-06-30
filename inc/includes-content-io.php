<?php
/**
 * Content import / export — upsert a post (by id) together with its media.
 *
 * Export turns a post into a self-contained JSON payload: the post fields, a
 * map of the attachment ids it references, and the media themselves as
 * filename + base64. Import is an UPSERT keyed by post_id (update when it
 * exists, otherwise insert) that reuses any attachment already in the library
 * by filename, uploads the base64 for the rest, and rewrites the post's media
 * ids / URLs to the local ids. Exposed as a WP-CLI command and an authorized
 * REST endpoint so large content can move between sites without pasting it
 * through a chat (keeps token use down).
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Walk a block-attributes value collecting media attachment ids (keys ending
 * in "Id" whose int value is an attachment).
 *
 * @param mixed           $value Attribute value.
 * @param array<int, int> &$ids   Collected ids (by reference).
 * @return void
 */
function mbn_content_collect_attr_ids( $value, array &$ids ): void {
  if ( ! is_array( $value ) ) {
    return;
  }
  foreach ( $value as $key => $item ) {
    if ( is_int( $item ) && $item > 0 && is_string( $key ) && preg_match( '/Id$/', $key ) && 'attachment' === get_post_type( $item ) ) {
      $ids[] = $item;
    } elseif ( is_array( $item ) ) {
      mbn_content_collect_attr_ids( $item, $ids );
    }
  }
}

/**
 * Collect every attachment id referenced by a post's content (block media ids
 * and raw uploads URLs).
 *
 * @param string $content Post content.
 * @return array<int, int>
 */
function mbn_content_collect_media_ids( string $content ): array {
  $ids    = array();
  $blocks = parse_blocks( $content );

  $walk = static function ( array $blocks, array &$ids ) use ( &$walk ): void {
    foreach ( $blocks as $block ) {
      if ( ! empty( $block['attrs'] ) ) {
        mbn_content_collect_attr_ids( $block['attrs'], $ids );
      }
      if ( ! empty( $block['innerBlocks'] ) ) {
        $walk( $block['innerBlocks'], $ids );
      }
    }
  };
  $walk( $blocks, $ids );

  if ( preg_match_all( '#https?://[^"\'\s)]+/wp-content/uploads/[^"\'\s)]+#i', $content, $matches ) ) {
    foreach ( $matches[0] as $url ) {
      $aid = attachment_url_to_postid( $url );
      if ( $aid ) {
        $ids[] = (int) $aid;
      }
    }
  }

  return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Build the export payload for a post.
 *
 * @param int $post_id Post id.
 * @return array<string, mixed>|WP_Error
 */
function mbn_content_export( int $post_id ) {
  $post = get_post( $post_id );
  if ( ! $post ) {
    return new WP_Error( 'mbn_not_found', __( 'Post not found.', 'mbn-theme' ), array( 'status' => 404 ) );
  }

  $ids   = mbn_content_collect_media_ids( $post->post_content );
  $thumb = (int) get_post_thumbnail_id( $post_id );
  if ( $thumb ) {
    $ids[] = $thumb;
  }
  $ids = array_values( array_unique( $ids ) );

  $medias    = array();
  $media_map = array();
  foreach ( $ids as $id ) {
    $file = get_attached_file( $id );
    if ( ! $file || ! file_exists( $file ) ) {
      continue;
    }
    $filename         = basename( $file );
    $media_map[ $id ] = $filename;
    if ( ! isset( $medias[ $filename ] ) ) {
      // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local media file.
      $bytes               = (string) file_get_contents( $file );
      $medias[ $filename ] = array(
		  'filename' => $filename,
		  'base64'   => base64_encode( $bytes ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- portable media transfer, not obfuscation.
      );
    }
  }

  return array(
	  'post_id'      => $post_id,
	  'post_type'    => $post->post_type,
	  'post_title'   => $post->post_title,
	  'post_status'  => $post->post_status,
	  'post_name'    => $post->post_name,
	  'post_excerpt' => $post->post_excerpt,
	  'post_content' => $post->post_content,
	  'thumbnail'    => $thumb ? basename( (string) get_attached_file( $thumb ) ) : '',
	  'media_map'    => $media_map,
	  'medias'       => array_values( $medias ),
  );
}

/**
 * Find an existing attachment id by file name.
 *
 * @param string $filename File name (basename).
 * @return int Attachment id, or 0.
 */
function mbn_content_find_attachment( string $filename ): int {
  global $wpdb;
  $filename = sanitize_file_name( $filename );
  if ( '' === $filename ) {
    return 0;
  }
  $like = '%/' . $wpdb->esc_like( $filename );
  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- targeted lookup by stored file path.
  $id = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND ( meta_value = %s OR meta_value LIKE %s ) ORDER BY post_id DESC LIMIT 1",
      $filename,
      $like
    )
  );
  return $id ? (int) $id : 0;
}

/**
 * Upload a base64 media file and return the new attachment id.
 *
 * @param string $filename File name.
 * @param string $base64   Base64-encoded bytes.
 * @return int Attachment id, or 0 on failure.
 */
function mbn_content_upload_media( string $filename, string $base64 ): int {
  $filename = sanitize_file_name( $filename );
  if ( '' === $filename || '' === $base64 ) {
    return 0;
  }
  $bytes = base64_decode( $base64, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- decoding a transferred media file, not obfuscation.
  if ( false === $bytes ) {
    return 0;
  }

  $filetype = wp_check_filetype( $filename );
  if ( empty( $filetype['type'] ) ) {
    return 0;
  }
  if ( 'image/svg+xml' === $filetype['type'] && function_exists( 'mbn_sanitize_svg_markup' ) ) {
    $bytes = mbn_sanitize_svg_markup( $bytes );
  }

  $upload = wp_upload_bits( $filename, null, $bytes );
  if ( ! empty( $upload['error'] ) ) {
    return 0;
  }

  $attach_id = wp_insert_attachment(
    array(
		'post_mime_type' => $filetype['type'],
		'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
		'post_status'    => 'inherit',
    ),
    $upload['file']
  );
  if ( is_wp_error( $attach_id ) || ! $attach_id ) {
    return 0;
  }

  require_once ABSPATH . 'wp-admin/includes/image.php';
  wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $upload['file'] ) );

  return (int) $attach_id;
}

/**
 * Remap media attachment ids inside a block-attributes value.
 *
 * @param mixed          $value Attribute value.
 * @param array<int,int> $idmap old id => new id.
 * @return mixed
 */
function mbn_content_remap_attr_ids( $value, array $idmap ) {
  if ( ! is_array( $value ) ) {
    return $value;
  }
  foreach ( $value as $key => $item ) {
    if ( is_int( $item ) && is_string( $key ) && preg_match( '/Id$/', $key ) && isset( $idmap[ $item ] ) ) {
      $value[ $key ] = $idmap[ $item ];
    } elseif ( is_array( $item ) ) {
      $value[ $key ] = mbn_content_remap_attr_ids( $item, $idmap );
    }
  }
  return $value;
}

/**
 * Recursively remap media ids in a parsed block tree.
 *
 * @param array<int, array<string, mixed>> $blocks Parsed blocks.
 * @param array<int,int>                   $idmap  old id => new id.
 * @return array<int, array<string, mixed>>
 */
function mbn_content_remap_blocks( array $blocks, array $idmap ): array {
  foreach ( $blocks as &$block ) {
    if ( ! empty( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
      $block['attrs'] = mbn_content_remap_attr_ids( $block['attrs'], $idmap );
    }
    if ( ! empty( $block['innerBlocks'] ) ) {
      $block['innerBlocks'] = mbn_content_remap_blocks( $block['innerBlocks'], $idmap );
    }
  }
  return $blocks;
}

/**
 * Rewrite a post's media ids (block attributes) and raw uploads URLs to local ids.
 *
 * @param string               $content          Post content.
 * @param array<int,int>       $idmap            old id => new id.
 * @param array<string,string> $basename_url_map filename => new URL.
 * @return string
 */
function mbn_content_remap( string $content, array $idmap, array $basename_url_map ): string {
  if ( $idmap ) {
    $content = serialize_blocks( mbn_content_remap_blocks( parse_blocks( $content ), $idmap ) );
  }

  if ( $basename_url_map ) {
    $content = preg_replace_callback(
      '#https?://[^"\'\s)]+/wp-content/uploads/[^"\'\s)]+#i',
      static function ( $matches ) use ( $basename_url_map ) {
        $base = basename( (string) wp_parse_url( $matches[0], PHP_URL_PATH ) );
        return $basename_url_map[ $base ] ?? $matches[0];
      },
      $content
    );
  }

  return $content;
}

/**
 * Resolve one media item: reuse an existing attachment by filename, else upload it.
 *
 * @param array<string, mixed> $media One media entry (filename + base64).
 * @return array{filename:string, id:int, status:string}
 */
function mbn_content_resolve_media( array $media ): array {
  $filename = isset( $media['filename'] ) ? sanitize_file_name( (string) $media['filename'] ) : '';
  if ( '' === $filename ) {
    return array(
		'filename' => '',
		'id'       => 0,
		'status'   => 'skipped',
    );
  }

  $existing = mbn_content_find_attachment( $filename );
  if ( $existing ) {
    return array(
		'filename' => $filename,
		'id'       => $existing,
		'status'   => 'reused',
    );
  }

  $id = mbn_content_upload_media( $filename, (string) ( $media['base64'] ?? '' ) );
  return array(
	  'filename' => $filename,
	  'id'       => $id,
	  'status'   => $id ? 'uploaded' : 'failed',
  );
}

/**
 * Insert or update the post (upsert keyed by post_id).
 *
 * @param array<string, mixed> $postarr Post fields (unslashed).
 * @param int                  $post_id Desired post id (0 to always insert).
 * @return array{result:int|WP_Error, action:string}
 */
function mbn_content_upsert_post( array $postarr, int $post_id ): array {
  if ( $post_id && get_post( $post_id ) ) {
    $postarr['ID'] = $post_id;
    return array(
		'result' => wp_update_post( wp_slash( $postarr ), true ),
		'action' => 'updated',
    );
  }
  if ( $post_id ) {
    $postarr['import_id'] = $post_id;
  }
  return array(
	  'result' => wp_insert_post( wp_slash( $postarr ), true ),
	  'action' => 'created',
  );
}

/**
 * Resolve a set of media items (reuse-or-upload each).
 *
 * @param array<int, mixed> $medias Media entries.
 * @return array{ids:array<string,int>, urls:array<string,string>, results:array<int,array<string,mixed>>}
 */
function mbn_content_import_medias( array $medias ): array {
  $fn_to_id  = array();
  $fn_to_url = array();
  $results   = array();
  foreach ( $medias as $media ) {
    $resolved  = mbn_content_resolve_media( (array) $media );
    $results[] = $resolved;
    if ( $resolved['id'] ) {
      $fn_to_id[ $resolved['filename'] ]  = $resolved['id'];
      $fn_to_url[ $resolved['filename'] ] = wp_get_attachment_url( $resolved['id'] );
    }
  }
  return array(
	  'ids'     => $fn_to_id,
	  'urls'    => $fn_to_url,
	  'results' => $results,
  );
}

/**
 * Build the old-id => new-id map from a media_map and the filename => id lookup.
 *
 * @param array<int|string, string> $media_map old id => filename.
 * @param array<string, int>        $fn_to_id  filename => new id.
 * @return array<int, int>
 */
function mbn_content_build_idmap( array $media_map, array $fn_to_id ): array {
  $idmap = array();
  foreach ( $media_map as $old_id => $filename ) {
    $filename = sanitize_file_name( (string) $filename );
    if ( isset( $fn_to_id[ $filename ] ) ) {
      $idmap[ (int) $old_id ] = $fn_to_id[ $filename ];
    }
  }
  return $idmap;
}

/**
 * Sanitize an import payload into a post array.
 *
 * @param array<string, mixed> $data    Payload.
 * @param string               $content Remapped content.
 * @return array<string, mixed>
 */
function mbn_content_postarr( array $data, string $content ): array {
  return array(
	  'post_title'   => sanitize_text_field( (string) ( $data['post_title'] ?? '' ) ),
	  'post_content' => $content,
	  'post_status'  => sanitize_key( (string) ( $data['post_status'] ?? 'draft' ) ),
	  'post_type'    => sanitize_key( (string) ( $data['post_type'] ?? 'post' ) ),
	  'post_excerpt' => sanitize_text_field( (string) ( $data['post_excerpt'] ?? '' ) ),
  );
}

/**
 * Upsert a post (by post_id) and its media from an export payload.
 *
 * @param array<string, mixed> $data Export payload.
 * @return array<string, mixed>|WP_Error
 */
function mbn_content_import( array $data ) {
  if ( empty( $data['post_content'] ) && empty( $data['medias'] ) ) {
    return new WP_Error( 'mbn_bad_request', __( 'Nothing to import.', 'mbn-theme' ), array( 'status' => 400 ) );
  }

  $media   = mbn_content_import_medias( (array) ( $data['medias'] ?? array() ) );
  $idmap   = mbn_content_build_idmap( (array) ( $data['media_map'] ?? array() ), $media['ids'] );
  $content = mbn_content_remap( (string) ( $data['post_content'] ?? '' ), $idmap, array_filter( $media['urls'] ) );
  $upsert  = mbn_content_upsert_post( mbn_content_postarr( $data, $content ), (int) ( $data['post_id'] ?? 0 ) );

  if ( is_wp_error( $upsert['result'] ) ) {
    return $upsert['result'];
  }
  $new_id = (int) $upsert['result'];

  $thumb_fn = sanitize_file_name( (string) ( $data['thumbnail'] ?? '' ) );
  if ( '' !== $thumb_fn && isset( $media['ids'][ $thumb_fn ] ) ) {
    set_post_thumbnail( $new_id, $media['ids'][ $thumb_fn ] );
  }

  return array(
	  'post_id' => $new_id,
	  'action'  => $upsert['action'],
	  'media'   => $media['results'],
  );
}

/**
 * REST permission: a shared token (MBN_IO_TOKEN constant in wp-config) or the
 * edit_posts capability (works with application passwords / a logged-in editor).
 *
 * @param WP_REST_Request $request Request.
 * @return bool
 */
function mbn_content_io_permission( $request ): bool {
  if ( defined( 'MBN_IO_TOKEN' ) && MBN_IO_TOKEN ) {
    $token = (string) $request->get_header( 'x-mbn-token' );
    if ( '' !== $token && hash_equals( (string) MBN_IO_TOKEN, $token ) ) {
      return true;
    }
  }
  return current_user_can( 'edit_posts' );
}

/**
 * Register the content-io REST routes.
 *
 * @return void
 */
function mbn_content_io_register_routes(): void {
  register_rest_route(
    'mbn/v1',
    '/posts/(?P<id>\d+)',
    array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => static function ( $request ) {
		  $result = mbn_content_export( (int) $request['id'] );
		  return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
		},
		'permission_callback' => 'mbn_content_io_permission',
    )
  );

  register_rest_route(
    'mbn/v1',
    '/posts',
    array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => static function ( $request ) {
		  $result = mbn_content_import( (array) $request->get_json_params() );
		  return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
		},
		'permission_callback' => 'mbn_content_io_permission',
    )
  );
}
add_action( 'rest_api_init', 'mbn_content_io_register_routes' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
  /**
   * Export a post (with its media) to JSON.
   *
   * ## OPTIONS
   * <id> : Post id. [--file=<path>] : Write JSON to a file instead of stdout.
   */
  WP_CLI::add_command(
    'mbn-content export',
    static function ( $args, $assoc_args ) {
      $result = mbn_content_export( (int) ( $args[0] ?? 0 ) );
      if ( is_wp_error( $result ) ) {
        WP_CLI::error( $result->get_error_message() );
      }
      $json = wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
      if ( ! empty( $assoc_args['file'] ) ) {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- CLI export to a chosen path.
        file_put_contents( $assoc_args['file'], $json );
        WP_CLI::success( 'Exported to ' . $assoc_args['file'] );
      } else {
        WP_CLI::line( $json );
      }
    }
  );

  /**
   * Import (upsert) a post export. Reads --file=<path> or stdin. Accepts a single
   * payload or an array of them.
   */
  WP_CLI::add_command(
    'mbn-content import',
    static function ( $args, $assoc_args ) {
      // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- CLI import from a chosen path / stdin.
      $json = ! empty( $assoc_args['file'] ) ? file_get_contents( $assoc_args['file'] ) : file_get_contents( 'php://stdin' );
      $data = json_decode( (string) $json, true );
      if ( ! is_array( $data ) ) {
        WP_CLI::error( 'Invalid JSON.' );
      }
      $items = ( isset( $data['post_content'] ) || isset( $data['post_id'] ) ) ? array( $data ) : $data;
      foreach ( $items as $item ) {
        $result = mbn_content_import( (array) $item );
        if ( is_wp_error( $result ) ) {
          WP_CLI::warning( $result->get_error_message() );
        } else {
          WP_CLI::success( sprintf( '%s post %d (%d media)', $result['action'], $result['post_id'], count( $result['media'] ) ) );
        }
      }
    }
  );
}
