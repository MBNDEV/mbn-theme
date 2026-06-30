<?php
/**
 * MBN Column child block front-end render template.
 *
 * @package CustomTheme
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks content.
 * @var WP_Block $block      Block instance.
 */

$wrapper_attrs = get_block_wrapper_attributes(
  array(
	  'class' => 'mbn-column w-full',
  )
);
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
