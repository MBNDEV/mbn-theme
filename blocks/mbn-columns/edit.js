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
import { FULL_WIDTH_CONTENT_CLASSES } from '../shared/use-layout-styles';
import { getColumnGridClasses } from '../shared/column-helpers';

const BLOCK_SLUG = 'mbn-columns';
const COLUMN_BLOCK = 'mbn-theme/mbn-column';

/**
 * @param {Object}   props
 * @param {Object}   props.attributes
 * @param {Function} props.setAttributes
 * @param {string}   props.clientId
 * @return {JSX.Element} MBN Columns block editor.
 */
export default function Edit( { attributes, setAttributes, clientId, ...props } ) {
	const { columnCount } = attributes;
	const gridClasses = getColumnGridClasses( columnCount );

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
				contentClassName={ FULL_WIDTH_CONTENT_CLASSES }
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
