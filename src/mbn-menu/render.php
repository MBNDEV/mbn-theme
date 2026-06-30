<?php
/**
 * MBN Menu block front-end render template.
 *
 * Renders the menu at the given 1-based slot from the current header/footer
 * template's selected menus (chosen in the template meta box). Menus are the
 * ones managed under Appearance > Menus.
 *
 * @package CustomTheme
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner content (unused).
 * @var WP_Block $block      Block instance.
 */

$template_id = isset( $GLOBALS['mbn_current_template_id'] )
  ? (int) $GLOBALS['mbn_current_template_id']
  : (int) get_the_ID();

$slot        = isset( $attributes['slot'] ) ? max( 1, (int) $attributes['slot'] ) : 1;
$orientation = isset( $attributes['orientation'] ) && 'vertical' === $attributes['orientation'] ? 'vertical' : 'horizontal';

$menus   = ( $template_id && function_exists( 'mbn_get_template_menus' ) ) ? mbn_get_template_menus( $template_id ) : array();
$menu_id = $menus[ $slot - 1 ] ?? 0;

if ( ! $menu_id || ! is_nav_menu( $menu_id ) ) {
  if ( is_admin() ) {
    /* translators: %d: menu slot number. */
    echo '<p class="mbn-menu-empty text-sm opacity-60">' . esc_html( sprintf( __( 'No menu assigned to slot %d. Set it in the template’s Header / Footer Settings.', 'mbn-theme' ), $slot ) ) . '</p>';
  }
  return;
}

$menu_class = 'vertical' === $orientation
  ? 'mbn-menu m-0 flex flex-col gap-2 p-0'
  : 'mbn-menu m-0 flex flex-wrap items-center gap-6 p-0';

$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'mbn-nav' ) );
?>
<nav <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
  <?php
  wp_nav_menu(
    array(
		'menu'        => $menu_id,
		'container'   => false,
		'menu_class'  => $menu_class,
		'depth'       => 2,
		'fallback_cb' => false,
    )
  );
  ?>
</nav>
