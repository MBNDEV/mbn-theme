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
$column_count = max( 1, min( 6, $column_count ) );

// Keep grid classes in sync with edit.js.
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
  'relative z-10 w-full px-4 sm:px-6 lg:px-8'
);
