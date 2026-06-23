<?php
/**
 * Image Box dynamic render.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'imageId'                => 0,
	'imageUrl'               => '',
	'imageAlt'               => '',
	'imageSize'              => 'full',
	'imageWidth'             => '',
	'imageHeight'            => '',
	'imageObjectFit'         => 'cover',
	'imageMarginTop'         => '',
	'imageMarginRight'       => '',
	'imageMarginBottom'      => '',
	'imageMarginLeft'        => '',
	'titleTag'               => 'h2',
	'title'                  => '',
	'titleMaxWidth'          => '',
	'titleMarginTop'         => '',
	'titleMarginRight'       => '',
	'titleMarginBottom'      => '',
	'titleMarginLeft'        => '',
	'titlePaddingTop'        => '',
	'titlePaddingRight'      => '',
	'titlePaddingBottom'     => '',
	'titlePaddingLeft'       => '',
	'titleFontFamily'        => '',
	'titleFontSize'          => '',
	'titleFontWeight'        => '',
	'titleLineHeight'        => '',
	'titleLetterSpacing'     => '',
	'titleColor'             => '',
	'titleAlign'             => '',
	'textarea'               => '',
	'textareaMaxWidth'       => '',
	'textareaMarginTop'      => '',
	'textareaMarginRight'    => '',
	'textareaMarginBottom'   => '',
	'textareaMarginLeft'     => '',
	'textareaPaddingTop'     => '',
	'textareaPaddingRight'   => '',
	'textareaPaddingBottom'  => '',
	'textareaPaddingLeft'    => '',
	'textareaFontFamily'     => '',
	'textareaFontSize'       => '',
	'textareaFontWeight'     => '',
	'textareaLineHeight'     => '',
	'textareaLetterSpacing'  => '',
	'textareaColor'          => '',
	'textareaAlign'          => '',
	'button1Text'            => '',
	'button1Url'             => '',
	'button1Target'          => '_self',
	'button1Style'           => 'primary',
	'button2Text'            => '',
	'button2Url'             => '',
	'button2Target'          => '_self',
	'button2Style'           => 'secondary',
	'buttonMarginTop'        => '',
	'buttonMarginRight'      => '',
	'buttonMarginBottom'     => '',
	'buttonMarginLeft'       => '',
	'buttonPaddingTop'       => '',
	'buttonPaddingRight'     => '',
	'buttonPaddingBottom'    => '',
	'buttonPaddingLeft'      => '',
	'buttonFontFamily'       => '',
	'buttonFontSize'         => '',
	'buttonFontWeight'       => '',
	'buttonLineHeight'       => '',
	'buttonLetterSpacing'    => '',
	'buttonBorderRadius'     => '',
	'buttonBorderStyle'      => 'none',
	'buttonBorderWidth'      => '',
	'buttonBorderColor'      => '',
	'contentPosition'        => 'left',
	'containerWidth'         => '',
	'containerPaddingTop'    => '',
	'containerPaddingRight'  => '',
	'containerPaddingBottom' => '',
	'containerPaddingLeft'   => '',
	'containerMarginTop'     => '',
	'containerMarginRight'   => '',
	'containerMarginBottom'  => '',
	'containerMarginLeft'    => '',
	'containerColor'         => '',
	'containerBgType'        => 'color',
	'containerBgColor'       => '',
	'containerBgGradient'    => '',
	'containerBorderRadius'  => '',
	'containerBorderStyle'   => 'none',
	'containerBorderWidth'   => '',
	'containerBorderColor'   => '',
	'containerShadow'        => 'none',
	'containerShadowCustom'  => '',
	'boxLinkUrl'             => '',
	'boxLinkTarget'          => '_self',
	'blockId'                => '',
	'customCss'              => '',
);

$attributes = wp_parse_args( $attributes, $defaults );

$allowed_title_tags = array( 'h2', 'h3', 'h4', 'h5', 'h6', 'div' );
$title_tag          = in_array( $attributes['titleTag'], $allowed_title_tags, true ) ? $attributes['titleTag'] : 'h2';

$style_string = static function ( $declarations ) {
	$style = '';
  foreach ( $declarations as $property => $value ) {
    if ( '' !== $value && null !== $value ) {
        $style .= $property . ':' . $value . ';';
    }
  }
	return $style;
};

$shadow_map = array(
	'sm' => '0 1px 2px rgba(0,0,0,.1)',
	'md' => '0 4px 10px rgba(0,0,0,.15)',
	'lg' => '0 12px 30px rgba(0,0,0,.2)',
);

$container_style = array(
	'width'          => $attributes['containerWidth'],
	'padding-top'    => $attributes['containerPaddingTop'],
	'padding-right'  => $attributes['containerPaddingRight'],
	'padding-bottom' => $attributes['containerPaddingBottom'],
	'padding-left'   => $attributes['containerPaddingLeft'],
	'margin-top'     => $attributes['containerMarginTop'],
	'margin-right'   => $attributes['containerMarginRight'],
	'margin-bottom'  => $attributes['containerMarginBottom'],
	'margin-left'    => $attributes['containerMarginLeft'],
	'color'          => $attributes['containerColor'],
	'border-radius'  => $attributes['containerBorderRadius'],
	'border-width'   => $attributes['containerBorderWidth'],
	'border-color'   => $attributes['containerBorderColor'],
);

if ( 'none' !== $attributes['containerBorderStyle'] ) {
	$container_style['border-style'] = $attributes['containerBorderStyle'];
}
if ( 'gradient' === $attributes['containerBgType'] && ! empty( $attributes['containerBgGradient'] ) ) {
	$container_style['background'] = $attributes['containerBgGradient'];
} elseif ( ! empty( $attributes['containerBgColor'] ) ) {
	$container_style['background-color'] = $attributes['containerBgColor'];
}
if ( 'custom' === $attributes['containerShadow'] && ! empty( $attributes['containerShadowCustom'] ) ) {
	$container_style['box-shadow'] = $attributes['containerShadowCustom'];
} elseif ( isset( $shadow_map[ $attributes['containerShadow'] ] ) ) {
	$container_style['box-shadow'] = $shadow_map[ $attributes['containerShadow'] ];
}

$image_style = array(
	'width'         => $attributes['imageWidth'],
	'height'        => $attributes['imageHeight'],
	'object-fit'    => $attributes['imageObjectFit'],
	'margin-top'    => $attributes['imageMarginTop'],
	'margin-right'  => $attributes['imageMarginRight'],
	'margin-bottom' => $attributes['imageMarginBottom'],
	'margin-left'   => $attributes['imageMarginLeft'],
);

$title_style = array(
	'max-width'      => $attributes['titleMaxWidth'],
	'margin-top'     => $attributes['titleMarginTop'],
	'margin-right'   => $attributes['titleMarginRight'],
	'margin-bottom'  => $attributes['titleMarginBottom'],
	'margin-left'    => $attributes['titleMarginLeft'],
	'padding-top'    => $attributes['titlePaddingTop'],
	'padding-right'  => $attributes['titlePaddingRight'],
	'padding-bottom' => $attributes['titlePaddingBottom'],
	'padding-left'   => $attributes['titlePaddingLeft'],
	'font-family'    => $attributes['titleFontFamily'],
	'font-size'      => $attributes['titleFontSize'],
	'font-weight'    => $attributes['titleFontWeight'],
	'line-height'    => $attributes['titleLineHeight'],
	'letter-spacing' => $attributes['titleLetterSpacing'],
	'color'          => $attributes['titleColor'],
	'text-align'     => $attributes['titleAlign'],
);

$textarea_style = array(
	'max-width'      => $attributes['textareaMaxWidth'],
	'margin-top'     => $attributes['textareaMarginTop'],
	'margin-right'   => $attributes['textareaMarginRight'],
	'margin-bottom'  => $attributes['textareaMarginBottom'],
	'margin-left'    => $attributes['textareaMarginLeft'],
	'padding-top'    => $attributes['textareaPaddingTop'],
	'padding-right'  => $attributes['textareaPaddingRight'],
	'padding-bottom' => $attributes['textareaPaddingBottom'],
	'padding-left'   => $attributes['textareaPaddingLeft'],
	'font-family'    => $attributes['textareaFontFamily'],
	'font-size'      => $attributes['textareaFontSize'],
	'font-weight'    => $attributes['textareaFontWeight'],
	'line-height'    => $attributes['textareaLineHeight'],
	'letter-spacing' => $attributes['textareaLetterSpacing'],
	'color'          => $attributes['textareaColor'],
	'text-align'     => $attributes['textareaAlign'],
);

$button_style = array(
	'margin-top'     => $attributes['buttonMarginTop'],
	'margin-right'   => $attributes['buttonMarginRight'],
	'margin-bottom'  => $attributes['buttonMarginBottom'],
	'margin-left'    => $attributes['buttonMarginLeft'],
	'padding-top'    => $attributes['buttonPaddingTop'],
	'padding-right'  => $attributes['buttonPaddingRight'],
	'padding-bottom' => $attributes['buttonPaddingBottom'],
	'padding-left'   => $attributes['buttonPaddingLeft'],
	'font-family'    => $attributes['buttonFontFamily'],
	'font-size'      => $attributes['buttonFontSize'],
	'font-weight'    => $attributes['buttonFontWeight'],
	'line-height'    => $attributes['buttonLineHeight'],
	'letter-spacing' => $attributes['buttonLetterSpacing'],
	'border-radius'  => $attributes['buttonBorderRadius'],
	'border-width'   => $attributes['buttonBorderWidth'],
	'border-color'   => $attributes['buttonBorderColor'],
);

if ( 'none' !== $attributes['buttonBorderStyle'] ) {
	$button_style['border-style'] = $attributes['buttonBorderStyle'];
}

$block_id = sanitize_html_class( $attributes['blockId'] );

$wrapper_attributes = get_block_wrapper_attributes(
  array(
	  'class' => trim( 'mbn-image-box is-pos-' . sanitize_html_class( $attributes['contentPosition'] ) . ' ' . $block_id ),
	  'style' => esc_attr( $style_string( $container_style ) ),
  )
);

$custom_css = '';
if ( ! empty( $attributes['customCss'] ) && ! empty( $block_id ) ) {
	$custom_css = str_replace( '{{WRAPPER}}', '.' . $block_id, $attributes['customCss'] );
}

$image_html = '';
if ( ! empty( $attributes['imageId'] ) ) {
	$image_html = wp_get_attachment_image(
      (int) $attributes['imageId'],
      ! empty( $attributes['imageSize'] ) ? $attributes['imageSize'] : 'full',
      false,
      array(
		  'alt'   => $attributes['imageAlt'],
		  'style' => $style_string( $image_style ),
	  )
	);
}
if ( empty( $image_html ) && ! empty( $attributes['imageUrl'] ) ) {
	$image_html = sprintf(
      '<img src="%1$s" alt="%2$s" style="%3$s" />',
      esc_url( $attributes['imageUrl'] ),
      esc_attr( $attributes['imageAlt'] ),
      esc_attr( $style_string( $image_style ) )
	);
}

$button_variant_class = static function ( $variant ) {
  if ( in_array( $variant, array( 'primary', 'secondary', 'outline' ), true ) ) {
      return 'btn-' . $variant;
  }
	return 'btn-primary';
};

$content_class = 'mbn-image-box__content has-align-' . sanitize_html_class( $attributes['contentPosition'] );
$open_box      = ! empty( $attributes['boxLinkUrl'] ) ? '<a class="mbn-image-box__box-link" href="' . esc_url( $attributes['boxLinkUrl'] ) . '" target="' . esc_attr( '_blank' === $attributes['boxLinkTarget'] ? '_blank' : '_self' ) . '" ' . ( '_blank' === $attributes['boxLinkTarget'] ? 'rel="noopener noreferrer"' : '' ) . '>' : '';
$close_box     = ! empty( $attributes['boxLinkUrl'] ) ? '</a>' : '';
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes ?>>
	<?php if ( ! empty( $custom_css ) ) : ?>
		<style><?php echo esc_html( wp_strip_all_tags( $custom_css ) ); ?></style>
	<?php endif; ?>
	<?php echo wp_kses_post( $open_box ); ?>
	<?php if ( ! empty( $image_html ) ) : ?>
		<div class="mbn-image-box__media"><?php echo wp_kses_post( $image_html ); ?></div>
	<?php endif; ?>

	<div class="<?php echo esc_attr( $content_class ); ?>">
		<?php if ( ! empty( $attributes['title'] ) ) : ?>
			<<?php echo esc_html( $title_tag ); ?> class="mbn-image-box__title" style="<?php echo esc_attr( $style_string( $title_style ) ); ?>">
				<?php echo wp_kses_post( $attributes['title'] ); ?>
			</<?php echo esc_html( $title_tag ); ?>>
		<?php endif; ?>

		<?php if ( ! empty( $attributes['textarea'] ) ) : ?>
			<div class="mbn-image-box__textarea" style="<?php echo esc_attr( $style_string( $textarea_style ) ); ?>">
				<?php echo wp_kses_post( $attributes['textarea'] ); ?>
			</div>
		<?php endif; ?>

		<div class="mbn-image-box__buttons">
			<?php if ( ! empty( $attributes['button1Text'] ) ) : ?>
				<a
					class="mbn-image-box__button <?php echo esc_attr( $button_variant_class( $attributes['button1Style'] ) ); ?>"
				href="<?php echo esc_url( ! empty( $attributes['button1Url'] ) ? $attributes['button1Url'] : '#' ); ?>"
					target="<?php echo esc_attr( '_blank' === $attributes['button1Target'] ? '_blank' : '_self' ); ?>"
					<?php echo '_blank' === $attributes['button1Target'] ? 'rel="noopener noreferrer"' : ''; ?>
					style="<?php echo esc_attr( $style_string( $button_style ) ); ?>"
				>
					<?php echo esc_html( $attributes['button1Text'] ); ?>
				</a>
			<?php endif; ?>
			<?php if ( ! empty( $attributes['button2Text'] ) ) : ?>
				<a
					class="mbn-image-box__button <?php echo esc_attr( $button_variant_class( $attributes['button2Style'] ) ); ?>"
				href="<?php echo esc_url( ! empty( $attributes['button2Url'] ) ? $attributes['button2Url'] : '#' ); ?>"
					target="<?php echo esc_attr( '_blank' === $attributes['button2Target'] ? '_blank' : '_self' ); ?>"
					<?php echo '_blank' === $attributes['button2Target'] ? 'rel="noopener noreferrer"' : ''; ?>
					style="<?php echo esc_attr( $style_string( $button_style ) ); ?>"
				>
					<?php echo esc_html( $attributes['button2Text'] ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
	<?php echo wp_kses_post( $close_box ); ?>
</div>
