/**
 * Shared typography tag selector. Lets a block choose the SEMANTIC element
 * (h1–h6, p, div, span) for a piece of text independently of its visual size —
 * the visual size is a `text-mbn-h*` utility class chosen separately, so a block
 * can render, say, an `<h2>` styled at the H1 size for correct document outline
 * without hardcoding the tag. Pair with the `mbn_tag()` PHP render helper.
 */
import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const MBN_TAG_OPTIONS = [
	{ label: 'H1', value: 'h1' },
	{ label: 'H2', value: 'h2' },
	{ label: 'H3', value: 'h3' },
	{ label: 'H4', value: 'h4' },
	{ label: 'H5', value: 'h5' },
	{ label: 'H6', value: 'h6' },
	{ label: 'Paragraph', value: 'p' },
	{ label: 'Div', value: 'div' },
	{ label: 'Span', value: 'span' },
];

export const MBN_SIZE_OPTIONS = [
	{ label: __( 'H1 size', 'mbn-theme' ), value: 'text-mbn-h1' },
	{ label: __( 'H2 size', 'mbn-theme' ), value: 'text-mbn-h2' },
	{ label: __( 'H3 size', 'mbn-theme' ), value: 'text-mbn-h3' },
	{ label: __( 'H4 size', 'mbn-theme' ), value: 'text-mbn-h4' },
	{ label: __( 'H5 size', 'mbn-theme' ), value: 'text-mbn-h5' },
	{ label: __( 'H6 size', 'mbn-theme' ), value: 'text-mbn-h6' },
	{ label: __( 'Body size', 'mbn-theme' ), value: 'text-mbn-body' },
];

/**
 * Tag selector. `value`/`onChange` bind a `*Tag` attribute (e.g. headingTag).
 */
export function TagControl( { label, value, onChange, tags = MBN_TAG_OPTIONS } ) {
	return (
		<SelectControl
			label={ label || __( 'HTML tag', 'mbn-theme' ) }
			value={ value }
			options={ tags }
			onChange={ onChange }
		/>
	);
}

/**
 * Optional size-utility selector. `value`/`onChange` bind a `*Size` class attribute.
 */
export function SizeControl( { label, value, onChange } ) {
	return (
		<SelectControl
			label={ label || __( 'Text size', 'mbn-theme' ) }
			value={ value }
			options={ MBN_SIZE_OPTIONS }
			onChange={ onChange }
		/>
	);
}
