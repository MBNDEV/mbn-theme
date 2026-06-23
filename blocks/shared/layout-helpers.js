/**
 * Editor layout helpers (styles, ids, scoped CSS only).
 *
 * @package CustomTheme
 */

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

/**
 * @param {string} elementId Block wrapper id.
 * @param {string} customCss  Raw CSS declarations or rules.
 * @return {string} Scoped CSS string.
 */
export function getScopedCustomCss( elementId, customCss ) {
	const css = ( customCss || '' ).trim();

	if ( ! css || ! elementId ) {
		return '';
	}

	if ( ! css.includes( '{' ) ) {
		return `#${ elementId }{${ css }}`;
	}

	return css.replace( /([^{}]+)\{/g, `#${ elementId } $1{` );
}
