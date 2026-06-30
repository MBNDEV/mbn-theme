/**
 * Shared editor UI for the MBN layout blocks (JSX).
 */
import {
	InspectorControls,
	PanelColorSettings,
	MediaUpload,
	useBlockProps,
	useInnerBlocksProps,
	InnerBlocks,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	RangeControl,
	SelectControl,
	Button,
} from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	getLayoutStyles,
	getBlockElementId,
	getScopedCustomCss,
} from './layout';

const SPACING_FIELDS = [
	{ key: 'marginTop', label: __( 'Margin Top', 'mbn-theme' ) },
	{ key: 'marginRight', label: __( 'Margin Right', 'mbn-theme' ) },
	{ key: 'marginBottom', label: __( 'Margin Bottom', 'mbn-theme' ) },
	{ key: 'marginLeft', label: __( 'Margin Left', 'mbn-theme' ) },
	{ key: 'paddingTop', label: __( 'Padding Top', 'mbn-theme' ) },
	{ key: 'paddingRight', label: __( 'Padding Right', 'mbn-theme' ) },
	{ key: 'paddingBottom', label: __( 'Padding Bottom', 'mbn-theme' ) },
	{ key: 'paddingLeft', label: __( 'Padding Left', 'mbn-theme' ) },
];

function MediaButtons( { open, url, urlKey, idKey, setAttributes, labels } ) {
	return (
		<div className="mbn-control" style={ { marginBottom: 24 } }>
			<Button onClick={ open } variant="secondary">
				{ url ? labels.replace : labels.select }
			</Button>
			{ url && (
				<Button
					onClick={ () => setAttributes( { [ urlKey ]: '', [ idKey ]: 0 } ) }
					variant="link"
					isDestructive
				>
					{ labels.remove }
				</Button>
			) }
		</div>
	);
}

export function LayoutControls( { attributes, setAttributes } ) {
	return (
		<InspectorControls>
			<PanelBody title={ __( 'Spacing', 'mbn-theme' ) } initialOpen={ false }>
				{ SPACING_FIELDS.map( ( field ) => (
					<TextControl
						key={ field.key }
						label={ field.label }
						value={ attributes[ field.key ] || '' }
						onChange={ ( value ) => setAttributes( { [ field.key ]: value } ) }
						help={ __(
							'Examples: 0, 1rem, 16px, var(--wp--preset--spacing--40)',
							'mbn-theme'
						) }
					/>
				) ) }
			</PanelBody>

			<PanelBody title={ __( 'Background', 'mbn-theme' ) } initialOpen={ false }>
				<MediaUpload
					onSelect={ ( media ) =>
						setAttributes( {
							backgroundImageUrl: media.url,
							backgroundImageId: media.id,
						} )
					}
					allowedTypes={ [ 'image' ] }
					value={ attributes.backgroundImageId }
					render={ ( { open } ) => (
						<MediaButtons
							open={ open }
							url={ attributes.backgroundImageUrl }
							urlKey="backgroundImageUrl"
							idKey="backgroundImageId"
							setAttributes={ setAttributes }
							labels={ {
								select: __( 'Select Background Image', 'mbn-theme' ),
								replace: __( 'Replace Background Image', 'mbn-theme' ),
								remove: __( 'Remove Background Image', 'mbn-theme' ),
							} }
						/>
					) }
				/>
					{ !! attributes.backgroundImageId && (
						<SelectControl
							label={ __( 'Background image size', 'mbn-theme' ) }
							help={ __( 'Rendered as a responsive <img> (srcset). Full keeps the largest as the src fallback.', 'mbn-theme' ) }
							value={ attributes.backgroundImageSize || 'full' }
							options={ [
								{ label: __( 'Full size', 'mbn-theme' ), value: 'full' },
								{ label: __( 'Large', 'mbn-theme' ), value: 'large' },
								{ label: __( 'Medium large', 'mbn-theme' ), value: 'medium_large' },
								{ label: __( 'Medium', 'mbn-theme' ), value: 'medium' },
								{ label: __( 'Thumbnail', 'mbn-theme' ), value: 'thumbnail' },
							] }
							onChange={ ( backgroundImageSize ) => setAttributes( { backgroundImageSize } ) }
						/>
					) }
				<MediaUpload
					onSelect={ ( media ) =>
						setAttributes( {
							backgroundVideoUrl: media.url,
							backgroundVideoId: media.id,
						} )
					}
					allowedTypes={ [ 'video' ] }
					value={ attributes.backgroundVideoId }
					render={ ( { open } ) => (
						<MediaButtons
							open={ open }
							url={ attributes.backgroundVideoUrl }
							urlKey="backgroundVideoUrl"
							idKey="backgroundVideoId"
							setAttributes={ setAttributes }
							labels={ {
								select: __( 'Select Background Video', 'mbn-theme' ),
								replace: __( 'Replace Background Video', 'mbn-theme' ),
								remove: __( 'Remove Background Video', 'mbn-theme' ),
							} }
						/>
					) }
				/>
				<RangeControl
					label={ __( 'Overlay Opacity (%)', 'mbn-theme' ) }
					value={ attributes.overlayOpacity }
					onChange={ ( value ) => setAttributes( { overlayOpacity: value } ) }
					min={ 0 }
					max={ 100 }
				/>
			</PanelBody>

			<PanelColorSettings
				title={ __( 'Colors', 'mbn-theme' ) }
				initialOpen={ false }
				colorSettings={ [
					{
						value: attributes.backgroundColor,
						onChange: ( value ) => setAttributes( { backgroundColor: value || '' } ),
						label: __( 'Background', 'mbn-theme' ),
					},
					{
						value: attributes.textColor,
						onChange: ( value ) => setAttributes( { textColor: value || '' } ),
						label: __( 'Text', 'mbn-theme' ),
					},
					{
						value: attributes.accentColor,
						onChange: ( value ) => setAttributes( { accentColor: value || '' } ),
						label: __( 'Accent', 'mbn-theme' ),
					},
					{
						value: attributes.overlayColor,
						onChange: ( value ) => setAttributes( { overlayColor: value || '' } ),
						label: __( 'Overlay', 'mbn-theme' ),
					},
				] }
			/>

			<PanelBody title={ __( 'Custom CSS', 'mbn-theme' ) } initialOpen={ false }>
				<TextareaControl
					label={ __( 'Scoped CSS', 'mbn-theme' ) }
					value={ attributes.customCss }
					onChange={ ( value ) => setAttributes( { customCss: value } ) }
					help={ __(
						'Declarations are scoped to this block wrapper. Example: border-radius: 1rem;',
						'mbn-theme'
					) }
					rows={ 6 }
				/>
			</PanelBody>
		</InspectorControls>
	);
}

export function LayoutShellEdit( props ) {
	const {
		attributes,
		setAttributes,
		clientId,
		blockSlug,
		wrapperClassName,
		contentClassName,
		innerBlocksClassName = '',
		innerBlocksOptions = {},
	} = props;

	const elementId = getBlockElementId( attributes, blockSlug );
	const style = getLayoutStyles( attributes );
	const scopedCss = getScopedCustomCss( elementId, attributes.customCss );
	const hasNestedInnerBlocks = Boolean( innerBlocksClassName );

	useEffect( () => {
		if ( ! attributes.blockInstanceId ) {
			setAttributes( {
				blockInstanceId:
					'mbn-' + blockSlug + '-' + clientId.replace( /-/g, '' ).slice( 0, 8 ),
			} );
		}
	}, [ attributes.blockInstanceId, blockSlug, clientId, setAttributes ] );

	const blockProps = useBlockProps( {
		id: elementId,
		className: wrapperClassName,
		style,
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{ className: hasNestedInnerBlocks ? innerBlocksClassName : contentClassName },
		{ renderAppender: InnerBlocks.ButtonBlockAppender, ...innerBlocksOptions }
	);

	const hasOverlay = attributes.overlayOpacity > 0 && attributes.overlayColor;

	const contentArea = hasNestedInnerBlocks ? (
		<div className={ contentClassName }>
			<div { ...innerBlocksProps } />
		</div>
	) : (
		<div { ...innerBlocksProps } />
	);

	return (
		<Fragment>
			<LayoutControls attributes={ attributes } setAttributes={ setAttributes } />
			{ scopedCss && <style>{ scopedCss }</style> }
			<div { ...blockProps }>
				{ attributes.backgroundVideoUrl && (
					<video
						className="mbn-layout-video pointer-events-none absolute inset-0 z-0 h-full w-full object-cover"
						autoPlay
						muted
						loop
						playsInline
						aria-hidden="true"
					>
						<source src={ attributes.backgroundVideoUrl } type="video/mp4" />
					</video>
				) }
				{ attributes.backgroundImageUrl && ! attributes.backgroundVideoUrl && (
					<img
						className="mbn-layout-image pointer-events-none absolute inset-0 z-0 h-full w-full object-cover object-center"
						src={ attributes.backgroundImageUrl }
						alt=""
						aria-hidden="true"
					/>
				) }
				{ hasOverlay && (
					<div
						className="mbn-layout-overlay absolute inset-0 z-[1]"
						style={ {
							backgroundColor: attributes.overlayColor,
							opacity: attributes.overlayOpacity / 100,
						} }
						aria-hidden="true"
					/>
				) }
				{ contentArea }
			</div>
		</Fragment>
	);
}
