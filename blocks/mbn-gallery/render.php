<?php
/**
 * MBN Gallery block front-end render template.
 *
 * @package CustomTheme
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks content.
 * @var WP_Block $block      Block instance.
 */

$column_count = isset( $attributes['columnCount'] ) ? absint( $attributes['columnCount'] ) : 3;
$images       = isset( $attributes['images'] ) && is_array( $attributes['images'] ) ? $attributes['images'] : array();
$grid_classes = mbn_theme_get_columns_grid_classes( $column_count );

ob_start();

if ( empty( $images ) ) :
  ?>
	<p class="mbn-gallery__empty text-sm"><?php esc_html_e( 'No gallery images selected.', 'mbn-theme' ); ?></p>
	<?php
else :
  ?>
	<div class="<?php echo esc_attr( $grid_classes ); ?>" role="list">
		<?php foreach ( $images as $image ) : ?>
			<?php
			if ( ! is_array( $image ) ) {
				continue;
			}

			$attachment_id = isset( $image['id'] ) ? absint( $image['id'] ) : 0;
			$image_url     = isset( $image['url'] ) ? esc_url( $image['url'] ) : '';
			$image_alt     = isset( $image['alt'] ) ? sanitize_text_field( (string) $image['alt'] ) : '';
			$caption       = isset( $image['caption'] ) ? sanitize_text_field( (string) $image['caption'] ) : '';

			if ( $attachment_id <= 0 && '' === $image_url ) {
				continue;
			}

			if ( '' === $image_alt && $attachment_id > 0 ) {
				$stored_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
				$image_alt  = is_string( $stored_alt ) ? sanitize_text_field( $stored_alt ) : '';
			}
			?>
			<figure class="mbn-gallery__item relative overflow-hidden rounded-lg" role="listitem">
				<div class="aspect-[4/3] w-full overflow-hidden">
					<?php if ( $attachment_id > 0 ) : ?>
						<?php
						$image_html = wp_get_attachment_image(
                          $attachment_id,
                          'large',
                          false,
                          array(
							  'class'   => 'h-full w-full object-cover',
							  'loading' => 'lazy',
							  'alt'     => $image_alt,
						  )
						);

						if ( is_string( $image_html ) && '' !== $image_html ) {
							echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					<?php elseif ( '' !== $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" class="h-full w-full object-cover" loading="lazy" decoding="async">
					<?php endif; ?>
				</div>
				<?php if ( '' !== $caption ) : ?>
					<figcaption class="mt-2 text-sm"><?php echo esc_html( $caption ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
	<?php
endif;

$gallery_html = ob_get_clean();

mbn_theme_render_layout_shell(
  $attributes,
  $gallery_html,
  'mbn-gallery',
  'relative isolate min-h-px w-full overflow-hidden',
  mbn_theme_get_full_width_content_classes()
);
