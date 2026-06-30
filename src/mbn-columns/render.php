<?php
/**
 * MBN Columns block front-end render template.
 *
 * @package CustomTheme
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks content.
 * @var WP_Block $block      Block instance.
 */

$block_slug      = 'mbn-columns';
$wrapper_classes = 'relative isolate min-h-px w-full overflow-hidden';
$content_classes = 'relative z-10 w-full px-4 sm:px-6 lg:px-8';

$column_count = isset( $attributes['columnCount'] ) ? absint( $attributes['columnCount'] ) : 2;
$column_count = max( 1, min( 6, $column_count ) );

// Keep grid classes in sync with assets/js/mbn-blocks-editor.js (getGridClasses).
switch ( $column_count ) {
  case 2:
      $grid_classes = 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2';
      break;
  case 3:
      $grid_classes = 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-3';
      break;
  case 4:
      $grid_classes = 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-4';
      break;
  case 5:
      $grid_classes = 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5';
      break;
  case 6:
      $grid_classes = 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6';
      break;
  default:
      $grid_classes = 'grid w-full grid-cols-1 items-stretch gap-6';
}

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
	  'class' => $wrapper_classes,
	  'style' => $inline_styles,
  )
);
?>
<?php if ( $scoped_css ) : ?>
	<style><?php echo $scoped_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>
<?php endif; ?>

<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $background_video_url ) : ?>
		<video class="mbn-layout-video pointer-events-none absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline aria-hidden="true">
			<source src="<?php echo esc_url( $background_video_url ); ?>" type="video/mp4">
		</video>
	<?php endif; ?>

	<?php
	if ( ! $background_video_url ) {
		echo mbn_layout_bg_image_html( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
	}
	?>

	<?php if ( $has_overlay ) : ?>
		<div class="mbn-layout-overlay absolute inset-0 z-[1]" style="background-color:<?php echo esc_attr( $overlay_color ); ?>;opacity:<?php echo esc_attr( (string) ( $overlay_opacity / 100 ) ); ?>;" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="<?php echo esc_attr( $content_classes ); ?>">
		<div class="<?php echo esc_attr( $grid_classes ); ?>">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
</div>
