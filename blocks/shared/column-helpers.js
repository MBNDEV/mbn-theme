/**
 * Column grid helpers for MBN Columns block.
 *
 * @package CustomTheme
 */

const GRID_CLASS_MAP = {
	1: 'grid w-full grid-cols-1 items-stretch gap-6',
	2: 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2',
	3: 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-3',
	4: 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-4',
	5: 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5',
	6: 'grid w-full grid-cols-1 items-stretch gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6',
};

/**
 * @param {number} columnCount Number of columns (1-6).
 * @return {string} Tailwind grid utility classes.
 */
export function getColumnGridClasses( columnCount ) {
	const count = Math.max( 1, Math.min( 6, Number( columnCount ) || 1 ) );

	return GRID_CLASS_MAP[ count ] || GRID_CLASS_MAP[ 1 ];
}
