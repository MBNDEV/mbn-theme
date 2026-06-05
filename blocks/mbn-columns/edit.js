/**
 * MBN Columns block editor component.
 *
 * @package CustomTheme
 */

import { useEffect, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import {
	useBlockProps,
	useInnerBlocksProps,
	InnerBlocks,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import LayoutControls from '../shared/LayoutControls';
import { getBlockElementId, getLayoutStyles, FULL_WIDTH_CONTENT_CLASSES } from '../shared/use-layout-styles';
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
export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		columnCount,
		backgroundImageUrl,
		backgroundVideoUrl,
		overlayColor,
		overlayOpacity,
		customCss,
	} = attributes;

	const elementId = getBlockElementId( attributes, BLOCK_SLUG );
	const layout = getLayoutStyles( attributes );
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
		if ( ! attributes.blockInstanceId ) {
			setAttributes( {
				blockInstanceId: `mbn-${ BLOCK_SLUG }-${ clientId.replace( /-/g, '' ).slice( 0, 8 ) }`,
			} );
		}
	}, [ attributes.blockInstanceId, clientId, setAttributes ] );

	useEffect( () => {
		if ( innerBlocks.length === 0 ) {
			syncColumnBlocks( columnCount );
		}
	}, [ columnCount, innerBlocks.length, syncColumnBlocks ] );

	const blockProps = useBlockProps( {
		id: elementId,
		className: 'relative isolate min-h-px w-full overflow-hidden',
		style: layout.style,
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{
			className: gridClasses,
		},
		{
			allowedBlocks: [ COLUMN_BLOCK ],
			orientation: 'horizontal',
			renderAppender: false,
		}
	);

	const hasOverlay = overlayOpacity > 0 && overlayColor;

	return (
		<>
			<LayoutControls attributes={ attributes } setAttributes={ setAttributes } />

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

			{ customCss && (
				<style>
					{ `#${ elementId }{${ customCss }}` }
				</style>
			) }

			<div { ...blockProps }>
				{ backgroundVideoUrl && (
					<video
						className="pointer-events-none absolute inset-0 z-0 h-full w-full object-cover"
						autoPlay
						muted
						loop
						playsInline
						aria-hidden="true"
					>
						<source src={ backgroundVideoUrl } type="video/mp4" />
					</video>
				) }

				{ backgroundImageUrl && ! backgroundVideoUrl && (
					<div
						className="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat"
						style={ { backgroundImage: `url(${ backgroundImageUrl })` } }
						aria-hidden="true"
					/>
				) }

				{ hasOverlay && (
					<div
						className="absolute inset-0 z-[1]"
						style={ {
							backgroundColor: overlayColor,
							opacity: overlayOpacity / 100,
						} }
						aria-hidden="true"
					/>
				) }

				<div className={ FULL_WIDTH_CONTENT_CLASSES }>
					<div { ...innerBlocksProps } />
				</div>
			</div>
		</>
	);
}
