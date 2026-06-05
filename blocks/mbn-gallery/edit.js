/**
 * MBN Gallery block editor component.
 *
 * @package CustomTheme
 */

import { useEffect, useCallback } from '@wordpress/element';
import { InspectorControls, MediaUpload } from '@wordpress/block-editor';
import { PanelBody, RangeControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import LayoutShellEdit from '../shared/LayoutShellEdit';
import { FULL_WIDTH_CONTENT_CLASSES } from '../shared/use-layout-styles';
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
export default function Edit( { attributes, setAttributes, clientId, ...props } ) {
	const { images = [], columnCount } = attributes;
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

	const galleryContent = images.length === 0 ? (
		<p className="mbn-gallery__empty text-sm">
			{ __( 'No gallery images selected.', 'mbn-theme' ) }
		</p>
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
	);

	return (
		<>
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

			<LayoutShellEdit
				{ ...props }
				attributes={ attributes }
				setAttributes={ setAttributes }
				clientId={ clientId }
				blockSlug={ BLOCK_SLUG }
				contentClassName={ FULL_WIDTH_CONTENT_CLASSES }
				innerContent={ galleryContent }
			/>
		</>
	);
}
