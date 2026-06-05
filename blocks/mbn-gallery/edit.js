/**
 * MBN Gallery block editor component.
 *
 * @package CustomTheme
 */

import { useEffect, useCallback } from '@wordpress/element';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
} from '@wordpress/block-editor';
import { PanelBody, RangeControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import LayoutControls from '../shared/LayoutControls';
import { getBlockElementId, getLayoutStyles, FULL_WIDTH_CONTENT_CLASSES } from '../shared/use-layout-styles';
import { getColumnGridClasses } from '../shared/column-helpers';

const BLOCK_SLUG = 'mbn-gallery';

/**
 * Normalize media items from the media library into gallery attributes.
 *
 * @param {Array|Object} media Selected media item(s).
 * @return {Array} Normalized gallery images.
 */
function normalizeGalleryImages( media ) {
	const items = Array.isArray( media ) ? media : [ media ];

	return items.map( ( item ) => ( {
		id: item.id,
		url: item.url,
		alt: item.alt || '',
		caption: item.caption || '',
	} ) );
}

/**
 * @param {Object}   props
 * @param {Object}   props.attributes
 * @param {Function} props.setAttributes
 * @param {string}   props.clientId
 * @return {JSX.Element} MBN Gallery block editor.
 */
export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		images = [],
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

	const removeImage = useCallback(
		( index ) => {
			const nextImages = images.filter( ( _, imageIndex ) => imageIndex !== index );
			setAttributes( { images: nextImages } );
		},
		[ images, setAttributes ]
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

	const blockProps = useBlockProps( {
		id: elementId,
		className: 'relative isolate min-h-px w-full overflow-hidden',
		style: layout.style,
	} );

	const hasOverlay = overlayOpacity > 0 && overlayColor;

	return (
		<>
			<LayoutControls attributes={ attributes } setAttributes={ setAttributes } />

			<InspectorControls>
				<PanelBody title={ __( 'Gallery', 'mbn-theme' ) } initialOpen={ true }>
					<RangeControl
						label={ __( 'Columns', 'mbn-theme' ) }
						value={ columnCount }
						onChange={ ( value ) => setAttributes( { columnCount: value ?? 1 } ) }
						min={ 1 }
						max={ 6 }
					/>

					<MediaUpload
						onSelect={ ( media ) => {
							const nextImages = normalizeGalleryImages( media );
							setAttributes( { images: [ ...images, ...nextImages ] } );
						} }
						allowedTypes={ [ 'image' ] }
						multiple
						gallery
						render={ ( { open } ) => (
							<Button onClick={ open } variant="primary">
								{ __( 'Add Images', 'mbn-theme' ) }
							</Button>
						) }
					/>

					{ images.length > 0 && (
						<Button
							onClick={ () => setAttributes( { images: [] } ) }
							variant="link"
							isDestructive
						>
							{ __( 'Remove All Images', 'mbn-theme' ) }
						</Button>
					) }
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
					{ images.length === 0 ? (
						<div className="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-600">
							{ __( 'Add images from the block sidebar to build your gallery.', 'mbn-theme' ) }
						</div>
					) : (
						<div className={ gridClasses } role="list">
							{ images.map( ( image, index ) => (
								<figure
									key={ `${ image.id || 'image' }-${ index }` }
									className="mbn-gallery__item relative overflow-hidden rounded-lg"
									role="listitem"
								>
									<div className="aspect-[4/3] w-full overflow-hidden">
										<img
											src={ image.url }
											alt={ image.alt || '' }
											className="h-full w-full object-cover"
										/>
									</div>
									{ image.caption && (
										<figcaption className="mt-2 text-sm">{ image.caption }</figcaption>
									) }
									<Button
										className="absolute right-2 top-2"
										onClick={ () => removeImage( index ) }
										variant="secondary"
										size="small"
									>
										{ __( 'Remove', 'mbn-theme' ) }
									</Button>
								</figure>
							) ) }
						</div>
					) }
				</div>
			</div>
		</>
	);
}
