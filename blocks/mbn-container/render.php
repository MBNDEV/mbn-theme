<?php
/**
 * MBN Container block front-end render template.
 *
 * @package CustomTheme
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks content.
 * @var WP_Block $block      Block instance.
 */

mbn_theme_render_layout_shell(
  $attributes,
  $content,
  'mbn-container',
  'relative isolate min-h-px w-full overflow-hidden',
  'relative z-10 container mx-auto'
);
