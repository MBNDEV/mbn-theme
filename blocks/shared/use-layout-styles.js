/**
 * Build layout inline styles from block attributes.
 *
 * @package CustomTheme
 */

/** Shared wrapper classes for layout shell blocks. */
export const LAYOUT_WRAPPER_CLASSES =
	'relative isolate min-h-px w-full overflow-hidden';

/** Background video layer classes. */
export const LAYOUT_VIDEO_CLASSES =
	'mbn-layout__video pointer-events-none absolute inset-0 z-0 h-full w-full object-cover';

/** Background image layer classes. */
export const LAYOUT_IMAGE_CLASSES =
	'mbn-layout__image absolute inset-0 z-0 bg-cover bg-center bg-no-repeat';

/** Overlay layer classes. */
export const LAYOUT_OVERLAY_CLASSES =
	'mbn-layout__overlay absolute inset-0 z-[1]';

/** Centered inner content column for section blocks. */
export const CENTERED_CONTENT_CLASSES =
	'relative z-10 mx-auto w-full max-w-[90%] px-4 sm:px-6 lg:px-8';

/** Centered inner content column for container blocks (~90% width). */
export const CONTAINER_CONTENT_CLASSES =
	'relative z-10 mx-auto w-full max-w-[90%] px-4 sm:px-6 lg:px-8';

/** Full-width inner content with edge padding for columns blocks. */
export const FULL_WIDTH_CONTENT_CLASSES =
	'relative z-10 w-full px-4 sm:px-6 lg:px-8';

/** MBN Column wrapper classes (matches render.php). */
export const COLUMN_WRAPPER_CLASSES = 'mbn-column min-h-px min-w-0';

/** MBN Column inner content classes (matches render.php). */
export const COLUMN_CONTENT_CLASSES = 'mbn-column__content flex flex-col gap-4';

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
 * Scope custom CSS declarations to a block wrapper id.
 * Mirrors mbn_theme_get_scoped_custom_css() in PHP.
 *
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
