<?php
/**
 * MBN FAQs – dynamic render.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ── load defaults from block.json ── */
$schema         = json_decode( file_get_contents( __DIR__ . '/block.json' ), true );
$default_attrs  = array();
if ( isset( $schema['attributes'] ) ) {
	foreach ( $schema['attributes'] as $key => $cfg ) {
		$default_attrs[ $key ] = $cfg['default'] ?? '';
	}
}
$attributes = wp_parse_args( $attributes, $default_attrs );

/* ── helper: build inline style string ── */
$css = static function ( array $decls ) {
	$out = '';
	foreach ( $decls as $prop => $val ) {
		if ( '' !== $val && null !== $val ) {
			$out .= $prop . ':' . $val . ';';
		}
	}
	return $out;
};

/* ── wrapper styles ── */
$wrapper_decls = array(
	'text-align'        => $attributes['alignment'] ?? '',
	'width'             => $attributes['containerWidth'] ?? '',
	'padding-top'       => $attributes['paddingTop'] ?? '',
	'padding-right'     => $attributes['paddingRight'] ?? '',
	'padding-bottom'    => $attributes['paddingBottom'] ?? '',
	'padding-left'      => $attributes['paddingLeft'] ?? '',
	'margin-top'        => $attributes['marginTop'] ?? '',
	'margin-right'      => $attributes['marginRight'] ?? '',
	'margin-bottom'     => $attributes['marginBottom'] ?? '',
	'margin-left'       => $attributes['marginLeft'] ?? '',
	'border-radius'     => $attributes['borderRadius'] ?? '',
	'border-width'      => $attributes['borderWidth'] ?? '',
	'border-color'      => $attributes['borderColor'] ?? '',
);

if ( 'none' !== ( $attributes['borderStyle'] ?? 'none' ) ) {
	$wrapper_decls['border-style'] = $attributes['borderStyle'];
}

$bg_type = $attributes['bgType'] ?? 'color';
if ( 'gradient' === $bg_type && ! empty( $attributes['bgGradient'] ) ) {
	$wrapper_decls['background'] = $attributes['bgGradient'];
} elseif ( 'image' === $bg_type && ! empty( $attributes['bgImageUrl'] ) ) {
	$wrapper_decls['background-image']    = 'url(' . esc_url_raw( $attributes['bgImageUrl'] ) . ')';
	$wrapper_decls['background-size']     = $attributes['bgImageSize'] ?? 'cover';
	$wrapper_decls['background-position'] = $attributes['bgImagePosition'] ?? 'center center';
} elseif ( ! empty( $attributes['bgColor'] ) ) {
	$wrapper_decls['background-color'] = $attributes['bgColor'];
}

/* ── question styles ── */
$question_decls = array(
	'max-width'      => $attributes['questionMaxWidth'] ?? '',
	'margin-top'     => $attributes['questionMarginTop'] ?? '',
	'margin-right'   => $attributes['questionMarginRight'] ?? '',
	'margin-bottom'  => $attributes['questionMarginBottom'] ?? '',
	'margin-left'    => $attributes['questionMarginLeft'] ?? '',
	'padding-top'    => $attributes['questionPaddingTop'] ?? '',
	'padding-right'  => $attributes['questionPaddingRight'] ?? '',
	'padding-bottom' => $attributes['questionPaddingBottom'] ?? '',
	'padding-left'   => $attributes['questionPaddingLeft'] ?? '',
	'font-family'    => $attributes['questionFontFamily'] ?? '',
	'font-size'      => $attributes['questionFontSize'] ?? '',
	'font-weight'    => $attributes['questionFontWeight'] ?? '',
	'line-height'    => $attributes['questionLineHeight'] ?? '',
	'letter-spacing' => $attributes['questionLetterSpacing'] ?? '',
	'color'          => $attributes['questionColor'] ?? '',
	'text-align'     => $attributes['questionAlign'] ?? '',
);

/* ── answer styles ── */
$answer_decls = array(
	'max-width'      => $attributes['answerMaxWidth'] ?? '',
	'margin-top'     => $attributes['answerMarginTop'] ?? '',
	'margin-right'   => $attributes['answerMarginRight'] ?? '',
	'margin-bottom'  => $attributes['answerMarginBottom'] ?? '',
	'margin-left'    => $attributes['answerMarginLeft'] ?? '',
	'padding-top'    => $attributes['answerPaddingTop'] ?? '',
	'padding-right'  => $attributes['answerPaddingRight'] ?? '',
	'padding-bottom' => $attributes['answerPaddingBottom'] ?? '',
	'padding-left'   => $attributes['answerPaddingLeft'] ?? '',
	'font-family'    => $attributes['answerFontFamily'] ?? '',
	'font-size'      => $attributes['answerFontSize'] ?? '',
	'font-weight'    => $attributes['answerFontWeight'] ?? '',
	'line-height'    => $attributes['answerLineHeight'] ?? '',
	'letter-spacing' => $attributes['answerLetterSpacing'] ?? '',
	'color'          => $attributes['answerColor'] ?? '',
	'text-align'     => $attributes['answerAlign'] ?? '',
);

/* ── allowed heading tags ── */
$allowed_tags = array( 'h2', 'h3', 'h4', 'h5', 'h6', 'div' );
$question_tag = in_array( $attributes['questionTag'] ?? 'h3', $allowed_tags, true )
	? $attributes['questionTag']
	: 'h3';

/* ── block ID / custom CSS ── */
$block_id    = sanitize_html_class( $attributes['blockId'] ?? '' );
$custom_id   = sanitize_html_class( $attributes['customId'] ?? '' );
$custom_class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', trim( $attributes['customClass'] ?? '' ) ) ) );
$custom_css  = '';
if ( ! empty( $attributes['customCss'] ) && ! empty( $block_id ) ) {
	$custom_css = str_replace( '{{WRAPPER}}', '.' . $block_id, $attributes['customCss'] );
}

$wrapper_class = trim( 'mbn-faqs ' . $custom_class . ' ' . $block_id );
$extra_attrs   = array(
	'class' => $wrapper_class,
	'style' => esc_attr( $css( $wrapper_decls ) ),
);
if ( ! empty( $custom_id ) ) {
	$extra_attrs['id'] = $custom_id;
}
$wrapper_attributes = get_block_wrapper_attributes( $extra_attrs );

$items = is_array( $attributes['items'] ) ? $attributes['items'] : array();
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php if ( ! empty( $custom_css ) ) : ?>
		<style><?php echo wp_strip_all_tags( $custom_css ); ?></style>
	<?php endif; ?>

	<?php foreach ( $items as $item ) : ?>
		<?php
		$question = wp_kses_post( $item['question'] ?? '' );
		$answer   = wp_kses_post( $item['answer'] ?? '' );
		if ( empty( $question ) && empty( $answer ) ) {
			continue;
		}
		?>
		<div class="mbn-faqs__item">
			<?php if ( ! empty( $question ) ) : ?>
				<<?php echo esc_html( $question_tag ); ?>
					class="mbn-faqs__question"
					style="<?php echo esc_attr( $css( $question_decls ) ); ?>"
				>
					<?php echo $question; // already sanitized ?>
				</<?php echo esc_html( $question_tag ); ?>>
			<?php endif; ?>

			<?php if ( ! empty( $answer ) ) : ?>
				<div
					class="mbn-faqs__answer"
					style="<?php echo esc_attr( $css( $answer_decls ) ); ?>"
				>
					<?php echo $answer; // already sanitized ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>

<script>
( function () {
	document.querySelectorAll( '.mbn-faqs__question' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var item = btn.closest( '.mbn-faqs__item' );
			if ( item ) {
				item.classList.toggle( 'is-open' );
			}
		} );
	} );
} )();
</script>
