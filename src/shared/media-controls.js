/**
 * Shared media controls for blocks.
 *
 * - MediaPicker: a single image/video/SVG picker that shows a thumbnail, the file
 *   URL and a "View in Media Library" link (so the editor can open/edit the
 *   attachment), plus Replace / Clear.
 * - MediaOptions: the standard four-option background group every block with an
 *   image exposes — Image, Video, Poster and an overlay — so background behaviour
 *   is identical everywhere. Maps to the mbn-ai-bg-media attributes.
 */
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, RangeControl, SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

export function MediaPicker( { label, value, onChange, allowedTypes = [ 'image' ], help, sizeValue, onSizeChange } ) {
	const media = useSelect(
		( select ) => ( value ? select( coreStore ).getMedia( value ) : null ),
		[ value ]
	);
	const url = ( media && media.source_url ) || '';
	const isVideo = url && /\.(mp4|webm|ogv|mov|m4v)$/i.test( url );
	const isImage = url && ! isVideo;
	const editLink = value ? `/wp-admin/post.php?post=${ value }&action=edit` : '';
	const previewStyle = { display: 'block', maxWidth: '100%', maxHeight: 96, marginBottom: 6, borderRadius: 4, border: '1px solid #dcdcde' };
	const sizes = ( media && media.media_details && media.media_details.sizes ) || {};
	const sizeOptions = Object.keys( sizes )
		.map( ( name ) => ( {
			label: `${ name } (${ sizes[ name ].width }×${ sizes[ name ].height })`,
			value: name,
		} ) )
		.concat( [ { label: __( 'Full size', 'mbn-theme' ), value: 'full' } ] );

	return (
		<MediaUploadCheck>
			<div className="mbn-control" style={ { marginBottom: 24 } }>
			{ label && <p style={ { fontWeight: 600, margin: '8px 0 4px' } }>{ label }</p> }
			<MediaUpload
				onSelect={ ( m ) => onChange( m.id ) }
				allowedTypes={ allowedTypes }
				value={ value || 0 }
				render={ ( { open } ) => (
					<div>
						{ isImage && (
							<img src={ url } alt="" style={ previewStyle } />
						) }
						{ isVideo && (
							<video src={ url } style={ previewStyle } muted controls playsInline preload="metadata" />
						) }
						<div style={ { display: 'flex', gap: 8, flexWrap: 'wrap', alignItems: 'center' } }>
							<Button variant="secondary" onClick={ open }>
								{ value ? __( 'Replace', 'mbn-theme' ) : __( 'Select', 'mbn-theme' ) }
							</Button>
							{ !! value && (
								<Button variant="link" isDestructive onClick={ () => onChange( 0 ) }>
									{ __( 'Clear', 'mbn-theme' ) }
								</Button>
							) }
						</div>
						{ url && (
							<p style={ { margin: '6px 0 0', fontSize: 11, lineHeight: 1.4 } }>
								<a href={ editLink } target="_blank" rel="noreferrer">
									{ __( 'View in Media Library', 'mbn-theme' ) }
								</a>
								<br />
								<span style={ { opacity: 0.65, wordBreak: 'break-all' } }>{ url }</span>
							</p>
						) }
						{ !! onSizeChange && isImage && (
							<div style={ { marginTop: 8 } }>
								<SelectControl
									label={ __( 'Image size', 'mbn-theme' ) }
									value={ sizeValue || 'full' }
									options={ sizeOptions.length ? sizeOptions : [ { label: __( 'Full size', 'mbn-theme' ), value: 'full' } ] }
									onChange={ onSizeChange }
								/>
							</div>
						) }
						{ help && <p style={ { margin: '4px 0 0', fontSize: 11, opacity: 0.7 } }>{ help }</p> }
					</div>
				) }
			/>
			</div>
		</MediaUploadCheck>
	);
}

/**
 * The standard four background alternatives. `attributes` must include imageId,
 * videoId, posterId and overlayOpacity; pass setAttributes through.
 */
export function MediaOptions( { attributes, setAttributes } ) {
	return (
		<div>
			<MediaPicker
				label={ __( 'Background image', 'mbn-theme' ) }
				value={ attributes.imageId }
				onChange={ ( imageId ) => setAttributes( { imageId } ) }
				sizeValue={ attributes.imageSize }
				onSizeChange={ ( imageSize ) => setAttributes( { imageSize } ) }
			/>
			<MediaPicker
				label={ __( 'Background video', 'mbn-theme' ) }
				value={ attributes.videoId }
				onChange={ ( videoId ) => setAttributes( { videoId } ) }
				allowedTypes={ [ 'video' ] }
				help={ __( 'Lazy-loaded; falls back to the poster (or plain black) until it plays.', 'mbn-theme' ) }
			/>
			<MediaPicker
				label={ __( 'Poster (video still)', 'mbn-theme' ) }
				value={ attributes.posterId }
				onChange={ ( posterId ) => setAttributes( { posterId } ) }
				help={ __( 'Shown until the video is ready and on reduced-motion. Defaults to the background image.', 'mbn-theme' ) }
			/>
			<RangeControl
				label={ __( 'Overlay opacity', 'mbn-theme' ) }
				value={ attributes.overlayOpacity }
				onChange={ ( overlayOpacity ) => setAttributes( { overlayOpacity } ) }
				min={ 0 }
				max={ 100 }
				step={ 5 }
			/>
		</div>
	);
}
