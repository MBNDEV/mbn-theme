<?php
/**
 * Hero — server-side render.
 *
 * @package MBNTheme
 * @var array    $attributes Block attributes from block.json.
 * @var string   $content    Inner blocks content (unused).
 * @var WP_Block $block      Block instance.
 */

$eyebrow              = isset( $attributes['eyebrow'] ) ? sanitize_text_field( $attributes['eyebrow'] ) : '';
$heading              = isset( $attributes['heading'] ) ? sanitize_text_field( $attributes['heading'] ) : '';
$body                 = isset( $attributes['body'] ) ? sanitize_text_field( $attributes['body'] ) : '';
$background_image_id  = isset( $attributes['backgroundImageId'] ) ? absint( $attributes['backgroundImageId'] ) : 0;
$background_image_url = isset( $attributes['backgroundImageUrl'] ) ? esc_url( $attributes['backgroundImageUrl'] ) : '';
$background_image_alt = isset( $attributes['backgroundImageAlt'] ) ? sanitize_text_field( $attributes['backgroundImageAlt'] ) : '';
$min_height           = isset( $attributes['minHeight'] ) ? absint( $attributes['minHeight'] ) : 600;
$overlay_opacity      = isset( $attributes['overlayOpacity'] ) ? floatval( $attributes['overlayOpacity'] ) : 0.78;
$content_max_width    = isset( $attributes['contentMaxWidth'] ) ? absint( $attributes['contentMaxWidth'] ) : 614;

$min_height        = max( 420, min( 900, $min_height ) );
$overlay_opacity   = max( 0.3, min( 1, $overlay_opacity ) );
$content_max_width = max( 420, min( 760, $content_max_width ) );

if ( $background_image_id > 0 ) {
	$resolved_background_image = wp_get_attachment_image_url( $background_image_id, 'full' );
  if ( $resolved_background_image ) {
      $background_image_url = $resolved_background_image;
  }
}

$style = sprintf(
  '--mbn-hero-min-height:%1$spx;--mbn-hero-overlay-opacity:%2$s;--mbn-hero-content-width:%3$spx;',
  esc_attr( (string) $min_height ),
  esc_attr( (string) $overlay_opacity ),
  esc_attr( (string) $content_max_width )
);

$wrapper_attributes = get_block_wrapper_attributes(
  array(
	  'class' => 'mbn-hero',
	  'style' => $style,
  )
);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $background_image_url ) : ?>
		<div class="mbn-hero__media" aria-hidden="true">
			<img class="mbn-hero__media-image" src="<?php echo esc_url( $background_image_url ); ?>" alt="<?php echo esc_attr( $background_image_alt ); ?>" />
		</div>
	<?php endif; ?>
	<div class="mbn-hero__overlay" aria-hidden="true"></div>
	<div class="mbn-hero__glow" aria-hidden="true"></div>
	<div class="mbn-hero__inner">
		<div class="mbn-hero__content">
			<?php if ( '' !== $eyebrow ) : ?>
				<p class="mbn-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $heading ) : ?>
				<h1 class="mbn-hero__heading"><?php echo esc_html( $heading ); ?></h1>
			<?php endif; ?>
			<?php if ( '' !== $body ) : ?>
				<p class="mbn-hero__body"><?php echo esc_html( $body ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
