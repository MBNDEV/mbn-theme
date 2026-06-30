import { Fragment, useEffect, useCallback } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { LayoutShellEdit } from '../shared/controls';
import { WRAPPER, getGridClasses } from '../shared/layout';

const COLUMN_BLOCK = 'mbn-theme/mbn-column';

export default function Edit( props ) {
	const { attributes, setAttributes, clientId } = props;
	const { columnCount } = attributes;

	const innerBlocks = useSelect(
		( select ) => select( 'core/block-editor' ).getBlocks( clientId ),
		[ clientId ]
	);
	const { replaceInnerBlocks } = useDispatch( 'core/block-editor' );

	const syncColumnBlocks = useCallback(
		( targetCount ) => {
			const count = Math.max( 1, Math.min( 6, Number( targetCount ) || 1 ) );
			const nextBlocks = innerBlocks.slice();
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
		<Fragment>
			<InspectorControls>
				<PanelBody title={ __( 'Columns', 'mbn-theme' ) } initialOpen>
					<RangeControl
						label={ __( 'Number of Columns', 'mbn-theme' ) }
						value={ columnCount }
						onChange={ ( value ) => {
							const nextCount = value == null ? 1 : value;
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
				blockSlug="mbn-columns"
				wrapperClassName={ WRAPPER }
				contentClassName="relative z-10 w-full px-4 sm:px-6 lg:px-8"
				innerBlocksClassName={ getGridClasses( columnCount ) }
				innerBlocksOptions={ {
					allowedBlocks: [ COLUMN_BLOCK ],
					orientation: 'horizontal',
					renderAppender: false,
				} }
			/>
		</Fragment>
	);
}
