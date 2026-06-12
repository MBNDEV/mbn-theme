<?php
/**
 * Icon Box dynamic render.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = json_decode( file_get_contents( __DIR__ . '/block.json' ), true );
$default_attrs = array();
if ( isset( $defaults['attributes'] ) ) {
	foreach ( $defaults['attributes'] as $key => $config ) {
		$default_attrs[ $key ] = $config['default'] ?? '';
	}
}
$attributes = wp_parse_args( $attributes, $default_attrs );

$style_string = static function ( $declarations ) {
	$style = '';
	foreach ( $declarations as $property => $value ) {
		if ( '' !== $value && null !== $value ) {
			$style .= $property . ':' . $value . ';';
		}
	}
	return $style;
};

$allowed_title_tags = array( 'h2', 'h3', 'h4', 'h5', 'h6', 'div' );
$title_tag = in_array( $attributes['titleTag'], $allowed_title_tags, true ) ? $attributes['titleTag'] : 'h3';

$shadow_map = array(
	'sm' => '0 1px 2px rgba(0,0,0,.1)',
	'md' => '0 4px 10px rgba(0,0,0,.15)',
	'lg' => '0 12px 30px rgba(0,0,0,.2)',
);

$container_style = array(
	'width' => $attributes['containerWidth'] ?? '',
	'padding-top' => $attributes['containerPaddingTop'] ?? '',
	'padding-right' => $attributes['containerPaddingRight'] ?? '',
	'padding-bottom' => $attributes['containerPaddingBottom'] ?? '',
	'padding-left' => $attributes['containerPaddingLeft'] ?? '',
	'margin-top' => $attributes['containerMarginTop'] ?? '',
	'margin-right' => $attributes['containerMarginRight'] ?? '',
	'margin-bottom' => $attributes['containerMarginBottom'] ?? '',
	'margin-left' => $attributes['containerMarginLeft'] ?? '',
	'border-radius' => $attributes['containerBorderRadius'] ?? '',
	'border-width' => $attributes['containerBorderWidth'] ?? '',
	'border-color' => $attributes['containerBorderColor'] ?? '',
);

if ( 'none' !== ( $attributes['containerBorderStyle'] ?? 'none' ) ) {
	$container_style['border-style'] = $attributes['containerBorderStyle'];
}
if ( 'gradient' === ( $attributes['containerBgType'] ?? '' ) && ! empty( $attributes['containerBgGradient'] ) ) {
	$container_style['background'] = $attributes['containerBgGradient'];
} elseif ( 'image' === ( $attributes['containerBgType'] ?? '' ) && ! empty( $attributes['containerBgImageUrl'] ) ) {
	$container_style['background-image'] = 'url(' . esc_url_raw( $attributes['containerBgImageUrl'] ) . ')';
	$container_style['background-size'] = $attributes['containerBgImageSize'] ?? 'cover';
	$container_style['background-position'] = $attributes['containerBgImagePosition'] ?? 'center center';
} elseif ( ! empty( $attributes['containerBgColor'] ) ) {
	$container_style['background-color'] = $attributes['containerBgColor'];
}
if ( 'custom' === ( $attributes['containerShadow'] ?? '' ) && ! empty( $attributes['containerShadowCustom'] ) ) {
	$container_style['box-shadow'] = $attributes['containerShadowCustom'];
} elseif ( isset( $shadow_map[ $attributes['containerShadow'] ?? '' ] ) ) {
	$container_style['box-shadow'] = $shadow_map[ $attributes['containerShadow'] ];
}

$icon_style = array(
	'background-color' => $attributes['iconBgColor'] ?? '',
	'border-width' => $attributes['iconBorderWidth'] ?? '',
	'border-color' => $attributes['iconBorderColor'] ?? '',
	'padding-top' => $attributes['iconPaddingTop'] ?? '',
	'padding-right' => $attributes['iconPaddingRight'] ?? '',
	'padding-bottom' => $attributes['iconPaddingBottom'] ?? '',
	'padding-left' => $attributes['iconPaddingLeft'] ?? '',
	'margin-top' => $attributes['iconMarginTop'] ?? '',
	'margin-right' => $attributes['iconMarginRight'] ?? '',
	'margin-bottom' => $attributes['iconMarginBottom'] ?? '',
	'margin-left' => $attributes['iconMarginLeft'] ?? '',
	'width' => $attributes['iconWidth'] ?? '',
	'display' => 'inline-flex',
	'align-items' => 'center',
	'justify-content' => 'center',
);
if ( 'none' !== ( $attributes['iconBorderStyle'] ?? 'none' ) ) {
	$icon_style['border-style'] = $attributes['iconBorderStyle'];
}
if ( 'circle' === ( $attributes['iconShape'] ?? '' ) ) {
	$icon_style['border-radius'] = '999px';
} elseif ( 'square' === ( $attributes['iconShape'] ?? '' ) ) {
	$icon_style['border-radius'] = '0';
} else {
	$icon_style['border-radius'] = $attributes['iconBorderRadius'] ?? '';
}

$title_style = array(
	'max-width' => $attributes['titleMaxWidth'] ?? '',
	'margin-top' => $attributes['titleMarginTop'] ?? '',
	'margin-right' => $attributes['titleMarginRight'] ?? '',
	'margin-bottom' => $attributes['titleMarginBottom'] ?? '',
	'margin-left' => $attributes['titleMarginLeft'] ?? '',
	'padding-top' => $attributes['titlePaddingTop'] ?? '',
	'padding-right' => $attributes['titlePaddingRight'] ?? '',
	'padding-bottom' => $attributes['titlePaddingBottom'] ?? '',
	'padding-left' => $attributes['titlePaddingLeft'] ?? '',
	'font-family' => $attributes['titleFontFamily'] ?? '',
	'font-size' => $attributes['titleFontSize'] ?? '',
	'font-weight' => $attributes['titleFontWeight'] ?? '',
	'line-height' => $attributes['titleLineHeight'] ?? '',
	'letter-spacing' => $attributes['titleLetterSpacing'] ?? '',
	'color' => $attributes['titleColor'] ?? '',
	'text-align' => $attributes['titleAlign'] ?? '',
);

$description_style = array(
	'max-width' => $attributes['descriptionMaxWidth'] ?? '',
	'margin-top' => $attributes['descriptionMarginTop'] ?? '',
	'margin-right' => $attributes['descriptionMarginRight'] ?? '',
	'margin-bottom' => $attributes['descriptionMarginBottom'] ?? '',
	'margin-left' => $attributes['descriptionMarginLeft'] ?? '',
	'padding-top' => $attributes['descriptionPaddingTop'] ?? '',
	'padding-right' => $attributes['descriptionPaddingRight'] ?? '',
	'padding-bottom' => $attributes['descriptionPaddingBottom'] ?? '',
	'padding-left' => $attributes['descriptionPaddingLeft'] ?? '',
	'font-family' => $attributes['descriptionFontFamily'] ?? '',
	'font-size' => $attributes['descriptionFontSize'] ?? '',
	'font-weight' => $attributes['descriptionFontWeight'] ?? '',
	'line-height' => $attributes['descriptionLineHeight'] ?? '',
	'letter-spacing' => $attributes['descriptionLetterSpacing'] ?? '',
	'color' => $attributes['descriptionColor'] ?? '',
	'text-align' => $attributes['descriptionAlign'] ?? '',
);

$button_style = array(
	'margin-top' => $attributes['buttonMarginTop'] ?? '',
	'margin-right' => $attributes['buttonMarginRight'] ?? '',
	'margin-bottom' => $attributes['buttonMarginBottom'] ?? '',
	'margin-left' => $attributes['buttonMarginLeft'] ?? '',
	'padding-top' => $attributes['buttonPaddingTop'] ?? '',
	'padding-right' => $attributes['buttonPaddingRight'] ?? '',
	'padding-bottom' => $attributes['buttonPaddingBottom'] ?? '',
	'padding-left' => $attributes['buttonPaddingLeft'] ?? '',
	'font-family' => $attributes['buttonFontFamily'] ?? '',
	'font-size' => $attributes['buttonFontSize'] ?? '',
	'font-weight' => $attributes['buttonFontWeight'] ?? '',
	'border-width' => $attributes['buttonBorderWidth'] ?? '',
	'border-color' => $attributes['buttonBorderColor'] ?? '',
	'border-radius' => $attributes['buttonBorderRadius'] ?? '',
);
if ( 'none' !== ( $attributes['buttonBorderStyle'] ?? 'none' ) ) {
	$button_style['border-style'] = $attributes['buttonBorderStyle'];
}

$block_id = sanitize_html_class( $attributes['blockId'] ?? '' );
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => trim( 'mbn-icon-box is-pos-' . sanitize_html_class( $attributes['contentPosition'] ?? 'center' ) . ' ' . $block_id ),
		'style' => esc_attr( $style_string( $container_style ) ),
	)
);

$custom_css = '';
if ( ! empty( $attributes['customCss'] ) && ! empty( $block_id ) ) {
	$custom_css = str_replace( '{{WRAPPER}}', '.' . $block_id, $attributes['customCss'] );
}

$button_variant = in_array( $attributes['buttonStyle'] ?? '', array( 'primary', 'secondary', 'outline' ), true ) ? $attributes['buttonStyle'] : 'primary';
$content_class = 'mbn-icon-box__content has-align-' . sanitize_html_class( $attributes['contentPosition'] ?? 'center' );
$open_box      = ! empty( $attributes['boxLinkUrl'] ) ? '<a class="mbn-icon-box__box-link" href="' . esc_url( $attributes['boxLinkUrl'] ) . '" target="' . esc_attr( '_blank' === ( $attributes['boxLinkTarget'] ?? '' ) ? '_blank' : '_self' ) . '" ' . ( '_blank' === ( $attributes['boxLinkTarget'] ?? '' ) ? 'rel="noopener noreferrer"' : '' ) . '>' : '';
$close_box     = ! empty( $attributes['boxLinkUrl'] ) ? '</a>' : '';
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php if ( ! empty( $custom_css ) ) : ?>
		<style><?php echo wp_strip_all_tags( $custom_css ); ?></style>
	<?php endif; ?>
	<?php echo wp_kses_post( $open_box ); ?>
	<div class="<?php echo esc_attr( $content_class ); ?>">
		<?php if ( ! empty( $attributes['iconImageUrl'] ) || ! empty( $attributes['iconSvgCode'] ) ) : ?>
			<div class="mbn-icon-box__icon" style="<?php echo esc_attr( $style_string( $icon_style ) ); ?>">
				<?php if ( 'svg' === ( $attributes['iconType'] ?? '' ) && ! empty( $attributes['iconSvgCode'] ) ) : ?>
					<?php echo wp_kses_post( $attributes['iconSvgCode'] ); ?>
				<?php elseif ( ! empty( $attributes['iconImageUrl'] ) ) : ?>
					<img src="<?php echo esc_url( $attributes['iconImageUrl'] ); ?>" alt="<?php echo esc_attr( $attributes['iconImageAlt'] ?? '' ); ?>" />
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $attributes['title'] ) ) : ?>
			<<?php echo esc_html( $title_tag ); ?> class="mbn-icon-box__title" style="<?php echo esc_attr( $style_string( $title_style ) ); ?>">
				<?php echo wp_kses_post( $attributes['title'] ); ?>
			</<?php echo esc_html( $title_tag ); ?>>
		<?php endif; ?>

		<?php if ( ! empty( $attributes['description'] ) ) : ?>
			<div class="mbn-icon-box__description" style="<?php echo esc_attr( $style_string( $description_style ) ); ?>">
				<?php echo wp_kses_post( $attributes['description'] ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $attributes['buttonText'] ) ) : ?>
			<a class="mbn-icon-box__button btn-<?php echo esc_attr( $button_variant ); ?>" href="<?php echo esc_url( $attributes['buttonUrl'] ?: '#' ); ?>" target="<?php echo esc_attr( '_blank' === ( $attributes['buttonTarget'] ?? '' ) ? '_blank' : '_self' ); ?>" <?php echo '_blank' === ( $attributes['buttonTarget'] ?? '' ) ? 'rel="noopener noreferrer"' : ''; ?> style="<?php echo esc_attr( $style_string( $button_style ) ); ?>">
				<?php echo esc_html( $attributes['buttonText'] ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php echo wp_kses_post( $close_box ); ?>
</div>
