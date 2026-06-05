<?php
/**
 * Shared layout helpers for MBN Gutenberg blocks.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_theme_file_path( 'inc/class-mbn-theme-block-layout.php' );

/**
 * Resolve a stable element id for block wrapper scoping.
 *
 * @param array  $attributes Block attributes.
 * @param string $block_slug Block slug without namespace.
 * @return string
 */
function mbn_theme_get_block_element_id( $attributes, $block_slug ) {
	return Mbn_Theme_Block_Layout::get_block_element_id( $attributes, $block_slug );
}

/**
 * Build inline style string from layout attributes.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function mbn_theme_get_layout_inline_styles( $attributes ) {
	return Mbn_Theme_Block_Layout::get_layout_inline_styles( $attributes );
}

/**
 * Scope custom CSS declarations to a block wrapper id.
 *
 * @param string $element_id Block wrapper id.
 * @param string $custom_css Raw CSS declarations or rules.
 * @return string
 */
function mbn_theme_get_scoped_custom_css( $element_id, $custom_css ) {
	return Mbn_Theme_Block_Layout::get_scoped_custom_css( $element_id, $custom_css );
}

/**
 * Render a shared layout shell with inner block content.
 *
 * @param array  $attributes      Block attributes.
 * @param string $content         Rendered inner blocks content.
 * @param string $block_slug      Block slug without namespace.
 * @param string $wrapper_classes Wrapper utility classes.
 * @param string $content_classes Inner content utility classes.
 * @return void
 */
function mbn_theme_render_layout_shell( $attributes, $content, $block_slug, $wrapper_classes, $content_classes ) {
	Mbn_Theme_Block_Layout::render_layout_shell( $attributes, $content, $block_slug, $wrapper_classes, $content_classes );
}
