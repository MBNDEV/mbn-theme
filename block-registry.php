<?php
/**
 * Gutenberg block registry.
 *
 * Blocks are authored in `src/<name>/` (block.json + index.js/edit.js + render.php)
 * and compiled with `@wordpress/scripts` to `build/<name>/`. Each built block.json
 * declares its editorScript/viewScript + render, so register_block_type() wires the
 * editor script (with auto-detected dependencies from the sibling *.asset.php) and
 * the server render automatically. Run `npm run build` to (re)generate `build/`.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Absolute paths of every compiled block directory under build/.
 *
 * Scans recursively so blocks may be grouped in sub-folders (e.g.
 * `build/components/<name>/`) as well as living at the top level
 * (`build/<name>/`). A directory qualifies when it contains a block.json.
 *
 * @return array<int, string>
 */
function custom_theme_get_block_dirs() {
	$build_dir = get_theme_file_path( 'build' );

  if ( ! is_dir( $build_dir ) ) {
      return array();
  }

	$dirs     = array();
	$iterator = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator( $build_dir, FilesystemIterator::SKIP_DOTS ),
      RecursiveIteratorIterator::SELF_FIRST
	);

  foreach ( $iterator as $file ) {
    if ( $file->isFile() && 'block.json' === $file->getFilename() ) {
        $dirs[] = $file->getPath();
    }
  }

	return array_values( array_unique( $dirs ) );
}

/**
 * Register all compiled blocks. block.json wires editor/view scripts + render.
 *
 * @return void
 */
function custom_theme_register_blocks() {
  foreach ( custom_theme_get_block_dirs() as $block_folder ) {
      register_block_type( $block_folder );
  }
}
add_action( 'init', 'custom_theme_register_blocks' );

/**
 * Register the custom block category for theme blocks.
 *
 * @param array $categories Array of block categories.
 * @return array Modified array of block categories.
 */
function custom_theme_register_block_category( $categories ) {
  foreach ( $categories as $category ) {
    if ( 'mbn-blocks' === $category['slug'] ) {
        return $categories;
    }
  }

	return array_merge(
      array(
		  array(
			  'slug'  => 'mbn-blocks',
			  'title' => __( 'MBN Blocks', 'mbn-theme' ),
			  'icon'  => 'wordpress',
		  ),
	  ),
      $categories
	);
}
add_filter( 'block_categories_all', 'custom_theme_register_block_category' );
