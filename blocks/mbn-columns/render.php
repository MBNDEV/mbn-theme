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

$column_count = isset( $attributes['columnCount'] ) ? absint( $attributes['columnCount'] ) : 2;
$grid_classes = mbn_theme_get_columns_grid_classes( $column_count );

ob_start();
?>
<div class="<?php echo esc_attr( $grid_classes ); ?>">
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
<?php
$grid_content = ob_get_clean();

mbn_theme_render_layout_shell(
  $attributes,
  $grid_content,
  'mbn-columns',
  'relative isolate min-h-px w-full overflow-hidden',
  mbn_theme_get_full_width_content_classes()
);
