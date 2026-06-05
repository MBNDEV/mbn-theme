<?php
/**
 * Shared layout helpers for MBN Gutenberg blocks.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a stable element id for block wrapper scoping.
 *
 * @param array  $attributes Block attributes.
 * @param string $block_slug Block slug without namespace.
 * @return string
 */
function mbn_theme_get_block_element_id( $attributes, $block_slug ) {
  if ( ! empty( $attributes['anchor'] ) ) {
      return sanitize_title( $attributes['anchor'] );
  }

  if ( ! empty( $attributes['blockInstanceId'] ) ) {
      return sanitize_html_class( $attributes['blockInstanceId'] );
  }

	return 'mbn-' . sanitize_html_class( $block_slug );
}

/**
 * Tailwind utility classes for centered inner content columns.
 *
 * @return string
 */
function mbn_theme_get_centered_content_classes() {
	return 'relative z-10 mx-auto w-full max-w-[90%] px-4 sm:px-6 lg:px-8';
}

/**
 * Centered inner content classes for MBN Container blocks (~90% width).
 *
 * @return string
 */
function mbn_theme_get_container_content_classes() {
	return 'relative z-10 mx-auto w-full max-w-[90%] px-4 sm:px-6 lg:px-8';
}

/**
 * Full-width inner content classes for MBN Columns blocks.
 *
 * @return string
 */
function mbn_theme_get_full_width_content_classes() {
	return 'relative z-10 w-full px-4 sm:px-6 lg:px-8';
}

/**
 * Tailwind grid classes for MBN Columns block layouts.
 *
 * @param int $column_count Number of columns (1-6).
 * @return string
 */
function mbn_theme_get_columns_grid_classes( $column_count ) {
	$column_count = max( 1, min( 6, absint( $column_count ) ) );

	$grid_map = array(
		1 => 'grid w-full grid-cols-1 items-stretch gap-6',
		2 => 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2',
		3 => 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-3',
		4 => 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-4',
		5 => 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5',
		6 => 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6',
	);

	return $grid_map[ $column_count ] ?? $grid_map[1];
}

/**
 * Build inline style string from layout attributes.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function mbn_theme_get_layout_inline_styles( $attributes ) {
	$spacing_map = array(
		'marginTop'     => 'margin-top',
		'marginRight'   => 'margin-right',
		'marginBottom'  => 'margin-bottom',
		'marginLeft'    => 'margin-left',
		'paddingTop'    => 'padding-top',
		'paddingRight'  => 'padding-right',
		'paddingBottom' => 'padding-bottom',
		'paddingLeft'   => 'padding-left',
	);

	$styles = array();

	foreach ( $spacing_map as $attribute_key => $property ) {
      if ( ! empty( $attributes[ $attribute_key ] ) ) {
          $styles[] = $property . ':' . esc_attr( $attributes[ $attribute_key ] );
      }
	}

	if ( ! empty( $attributes['backgroundColor'] ) ) {
		$styles[] = 'background-color:' . esc_attr( $attributes['backgroundColor'] );
	}

	if ( ! empty( $attributes['textColor'] ) ) {
		$styles[] = 'color:' . esc_attr( $attributes['textColor'] );
	}

	if ( ! empty( $attributes['accentColor'] ) ) {
		$styles[] = '--mbn-accent-color:' . esc_attr( $attributes['accentColor'] );
	}

	return implode( ';', $styles );
}

/**
 * Scope custom CSS declarations to a block wrapper id.
 *
 * @param string $element_id Block wrapper id.
 * @param string $custom_css Raw CSS declarations or rules.
 * @return string
 */
function mbn_theme_get_scoped_custom_css( $element_id, $custom_css ) {
	$custom_css = trim( (string) $custom_css );

  if ( '' === $custom_css || '' === $element_id ) {
      return '';
  }

	$custom_css = wp_strip_all_tags( $custom_css );

  if ( false === strpos( $custom_css, '{' ) ) {
      return '#' . esc_attr( $element_id ) . '{' . $custom_css . '}';
  }

	return preg_replace( '/([^{}]+)\{/', '#' . esc_attr( $element_id ) . ' $1{', $custom_css );
}

/**
 * Render a shared layout shell with inner block content.
 *
 * @param array  $attributes       Block attributes.
 * @param string $content          Rendered inner blocks content.
 * @param string $block_slug       Block slug without namespace.
 * @param string $wrapper_classes  Wrapper utility classes.
 * @param string $content_classes  Inner content utility classes.
 * @return void
 */
function mbn_theme_render_layout_shell( $attributes, $content, $block_slug, $wrapper_classes, $content_classes ) {
	$background_image_url = $attributes['backgroundImageUrl'] ?? '';
	$background_video_url = $attributes['backgroundVideoUrl'] ?? '';
	$overlay_color        = $attributes['overlayColor'] ?? '';
	$overlay_opacity      = isset( $attributes['overlayOpacity'] ) ? absint( $attributes['overlayOpacity'] ) : 0;
	$custom_css           = $attributes['customCss'] ?? '';

	$element_id    = mbn_theme_get_block_element_id( $attributes, $block_slug );
	$inline_styles = mbn_theme_get_layout_inline_styles( $attributes );
	$scoped_css    = mbn_theme_get_scoped_custom_css( $element_id, $custom_css );
	$has_overlay   = $overlay_opacity > 0 && ! empty( $overlay_color );

	$wrapper_attrs = get_block_wrapper_attributes(
      array(
		  'id'    => $element_id,
		  'class' => trim( $wrapper_classes ),
		  'style' => $inline_styles,
	  )
	);

	ob_start();
  ?>
	<?php if ( $scoped_css ) : ?>
		<style><?php echo $scoped_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>
	<?php endif; ?>

	<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php if ( $background_video_url ) : ?>
			<video class="mbn-layout__video pointer-events-none absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline aria-hidden="true">
				<source src="<?php echo esc_url( $background_video_url ); ?>" type="video/mp4">
			</video>
		<?php endif; ?>

		<?php if ( $background_image_url && ! $background_video_url ) : ?>
			<div class="mbn-layout__image absolute inset-0 z-0 bg-cover bg-center bg-no-repeat" style="background-image:url(<?php echo esc_url( $background_image_url ); ?>);" aria-hidden="true"></div>
		<?php endif; ?>

		<?php if ( $has_overlay ) : ?>
			<div class="mbn-layout__overlay absolute inset-0 z-[1]" style="background-color:<?php echo esc_attr( $overlay_color ); ?>;opacity:<?php echo esc_attr( (string) ( $overlay_opacity / 100 ) ); ?>;" aria-hidden="true"></div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $content_classes ); ?>">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
	<?php
	echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
