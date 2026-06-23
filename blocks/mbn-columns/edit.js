/**
 * MBN Columns block editor component.
 *
 * @package CustomTheme
 */

import { useEffect, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import LayoutShellEdit from '../shared/LayoutShellEdit';

const BLOCK_SLUG = 'mbn-columns';
const COLUMN_BLOCK = 'mbn-theme/mbn-column';

/**
 * @param {number} columnCount Number of columns (1-6).
 * @return {string} Grid classes — keep in sync with render.php.
 */
function getGridClasses( columnCount ) {
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

/**
 * @param {Object}   props
 * @param {Object}   props.attributes
 * @param {Function} props.setAttributes
 * @param {string}   props.clientId
 * @return {JSX.Element} MBN Columns block editor.
 */
export default function Edit( { attributes, setAttributes, clientId, ...props } ) {
	const { columnCount } = attributes;
	const gridClasses = getGridClasses( columnCount );

	const innerBlocks = useSelect(
		( select ) => select( 'core/block-editor' ).getBlocks( clientId ),
		[ clientId ]
	);

	const { replaceInnerBlocks } = useDispatch( 'core/block-editor' );

	const syncColumnBlocks = useCallback(
		( targetCount ) => {
			const count = Math.max( 1, Math.min( 6, Number( targetCount ) || 1 ) );
			const nextBlocks = [ ...innerBlocks ];

			while ( nextBlocks.length < count ) {
				nextBlocks.push( createBlock( COLUMN_BLOCK ) );
			}

			while ( nextBlocks.length > count ) {
				nextBlocks.pop();
			}

			if ( nextBlocks.length !== innerBlocks.length ) {
				replaceInnerBlocks( clientId, nextBlocks, false );
			}
		},
		[ clientId, innerBlocks, replaceInnerBlocks ]
	);

	useEffect( () => {
		if ( attributes.align !== 'full' ) {
			setAttributes( { align: 'full' } );
		}
	}, [ attributes.align, setAttributes ] );

	useEffect( () => {
		if ( innerBlocks.length === 0 ) {
			syncColumnBlocks( columnCount );
		}
	}, [ columnCount, innerBlocks.length, syncColumnBlocks ] );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Columns', 'mbn-theme' ) } initialOpen={ true }>
					<RangeControl
						label={ __( 'Number of Columns', 'mbn-theme' ) }
						value={ columnCount }
						onChange={ ( value ) => {
							const nextCount = value ?? 1;
							setAttributes( { columnCount: nextCount } );
							syncColumnBlocks( nextCount );
						} }
						min={ 1 }
						max={ 6 }
					/>
				</PanelBody>
			</InspectorControls>

			<LayoutShellEdit
				{ ...props }
				attributes={ attributes }
				setAttributes={ setAttributes }
				clientId={ clientId }
				blockSlug={ BLOCK_SLUG }
				wrapperClassName="relative isolate min-h-px w-full overflow-hidden"
				contentClassName="relative z-10 w-full px-4 sm:px-6 lg:px-8"
				innerBlocksClassName={ gridClasses }
				innerBlocksOptions={ {
					allowedBlocks: [ COLUMN_BLOCK ],
					orientation: 'horizontal',
					renderAppender: false,
				} }
			/>
		</>
	);
}
