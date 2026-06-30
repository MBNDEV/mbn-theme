<?php
/**
 * MBN Logo block front-end render template.
 *
 * Resolves the logo from the current header/footer template's logo meta (set
 * during template render, or the post being edited in the editor). Falls back
 * to the core custom logo, then the site title text.
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

$logo_id = ( $template_id && function_exists( 'mbn_get_template_logo_id' ) )
  ? mbn_get_template_logo_id( $template_id )
  : 0;

if ( ! $logo_id ) {
  $logo_id = (int) get_theme_mod( 'custom_logo', 0 );
}

$max_width = isset( $attributes['maxWidth'] ) && '' !== $attributes['maxWidth'] ? $attributes['maxWidth'] : '160px';
$home_url  = esc_url( home_url( '/' ) );
$site_name = get_bloginfo( 'name' );

$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'mbn-logo inline-flex items-center' ) );
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
  <a href="<?php echo $home_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" rel="home" class="mbn-logo-link inline-block no-underline" style="max-width:<?php echo esc_attr( $max_width ); ?>;">
    <?php
    if ( $logo_id ) {
      echo wp_get_attachment_image(
        $logo_id,
        'full',
        false,
        array(
			'class' => 'mbn-logo-img h-auto w-full',
			'alt'   => esc_attr( $site_name ),
        )
      );
    } else {
      echo '<span class="mbn-logo-text text-lg font-bold">' . esc_html( $site_name ) . '</span>';
    }
    ?>
  </a>
</div>
