/**
 * Pure layout helpers shared by the MBN layout blocks (no JSX).
 * Mirror the PHP helpers in inc/block-layout-helpers.php so editor === front end.
 */

export const WRAPPER = 'relative isolate min-h-px w-full overflow-hidden';

export const SPACING_KEYS = [
	'marginTop',
	'marginRight',
	'marginBottom',
	'marginLeft',
	'paddingTop',
	'paddingRight',
	'paddingBottom',
	'paddingLeft',
];

export function getLayoutStyles( attributes ) {
	const style = {};

	SPACING_KEYS.forEach( ( key ) => {
		if ( attributes[ key ] ) {
			style[ key ] = attributes[ key ];
		}
	} );

	if ( attributes.backgroundColor ) {
		style.backgroundColor = attributes.backgroundColor;
	}
	if ( attributes.textColor ) {
		style.color = attributes.textColor;
	}
	if ( attributes.accentColor ) {
		style[ '--mbn-accent-color' ] = attributes.accentColor;
	}

	return style;
}

export function getBlockElementId( attributes, blockSlug ) {
	if ( attributes.anchor ) {
		return attributes.anchor;
	}
	if ( attributes.blockInstanceId ) {
		return attributes.blockInstanceId;
	}
	return 'mbn-' + blockSlug;
}

export function getScopedCustomCss( elementId, customCss ) {
	const css = ( customCss || '' ).trim();
	if ( ! css || ! elementId ) {
		return '';
	}
	if ( css.indexOf( '{' ) === -1 ) {
		return '#' + elementId + '{' + css + '}';
	}
	return css.replace( /([^{}]+)\{/g, '#' + elementId + ' $1{' );
}

export function getGridClasses( columnCount ) {
	const count = Math.max( 1, Math.min( 6, Number( columnCount ) || 1 ) );
	switch ( count ) {
		case 2:
			return 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2';
		case 3:
			return 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-3';
		case 4:
			return 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-4';
		case 5:
			return 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5';
		case 6:
			return 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6';
		default:
			return 'grid w-full grid-cols-1 items-stretch gap-6';
	}
}
