/**
 * Build layout inline styles from block attributes.
 *
 * @package CustomTheme
 */

/** Centered inner content column for section blocks. */
export const CENTERED_CONTENT_CLASSES =
	'relative z-10 mx-auto w-full max-w-[90%] px-4 sm:px-6 lg:px-8';

/** Centered inner content column for container blocks (~90% width). */
export const CONTAINER_CONTENT_CLASSES =
	'relative z-10 mx-auto w-full max-w-[90%] px-4 sm:px-6 lg:px-8';

/** Full-width inner content with edge padding for columns blocks. */
export const FULL_WIDTH_CONTENT_CLASSES =
	'relative z-10 w-full px-4 sm:px-6 lg:px-8';

const SPACING_KEYS = [
	'marginTop',
	'marginRight',
	'marginBottom',
	'marginLeft',
	'paddingTop',
	'paddingRight',
	'paddingBottom',
	'paddingLeft',
];

/**
 * @param {Object} attributes Block attributes.
 * @return {{ style: Object }} Layout inline styles.
 */
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

	return { style };
}

/**
 * @param {Object} attributes Block attributes.
 * @param {string} blockSlug  Block slug without namespace.
 * @return {string} Stable element id for scoping custom CSS.
 */
export function getBlockElementId( attributes, blockSlug ) {
	if ( attributes.anchor ) {
		return attributes.anchor;
	}

	if ( attributes.blockInstanceId ) {
		return attributes.blockInstanceId;
	}

	return `mbn-${ blockSlug }`;
}
