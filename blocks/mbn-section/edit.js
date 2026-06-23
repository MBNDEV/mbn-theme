import {
	InspectorControls,
	InnerBlocks,
	MediaUpload,
	MediaUploadCheck,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	SelectControl,
	TextControl,
	RangeControl,
	ColorPicker,
	__experimentalBoxControl as BoxControl,
	__experimentalUnitControl as UnitControl,
} from '@wordpress/components';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const SHADOW_OPTIONS = [
	{ label: __( 'None', 'mbn-theme' ), value: 'none' },
	{ label: __( 'Small', 'mbn-theme' ), value: 'sm' },
	{ label: __( 'Medium', 'mbn-theme' ), value: 'md' },
	{ label: __( 'Large', 'mbn-theme' ), value: 'lg' },
	{ label: __( 'Custom', 'mbn-theme' ), value: 'custom' },
];

const BORDER_STYLE_OPTIONS = [
	{ label: __( 'None', 'mbn-theme' ), value: 'none' },
	{ label: __( 'Solid', 'mbn-theme' ), value: 'solid' },
	{ label: __( 'Dashed', 'mbn-theme' ), value: 'dashed' },
	{ label: __( 'Dotted', 'mbn-theme' ), value: 'dotted' },
	{ label: __( 'Double', 'mbn-theme' ), value: 'double' },
];

const ALIGNMENT_OPTIONS = [
	{ label: __( 'Left', 'mbn-theme' ), value: 'left' },
	{ label: __( 'Center', 'mbn-theme' ), value: 'center' },
	{ label: __( 'Right', 'mbn-theme' ), value: 'right' },
];

const JUSTIFY_CONTENT_OPTIONS = [
	{ label: __( 'Start', 'mbn-theme' ), value: 'flex-start' },
	{ label: __( 'Center', 'mbn-theme' ), value: 'center' },
	{ label: __( 'End', 'mbn-theme' ), value: 'flex-end' },
	{ label: __( 'Space Between', 'mbn-theme' ), value: 'space-between' },
	{ label: __( 'Space Around', 'mbn-theme' ), value: 'space-around' },
];

const ALIGN_ITEMS_OPTIONS = [
	{ label: __( 'Start', 'mbn-theme' ), value: 'flex-start' },
	{ label: __( 'Center', 'mbn-theme' ), value: 'center' },
	{ label: __( 'End', 'mbn-theme' ), value: 'flex-end' },
	{ label: __( 'Stretch', 'mbn-theme' ), value: 'stretch' },
];

const BG_TYPE_OPTIONS = [
	{ label: __( 'None', 'mbn-theme' ), value: 'none' },
	{ label: __( 'Color', 'mbn-theme' ), value: 'color' },
	{ label: __( 'Gradient', 'mbn-theme' ), value: 'gradient' },
	{ label: __( 'Image', 'mbn-theme' ), value: 'image' },
	{ label: __( 'Video', 'mbn-theme' ), value: 'video' },
];

const BG_SIZE_OPTIONS = [
	{ label: __( 'Cover', 'mbn-theme' ), value: 'cover' },
	{ label: __( 'Contain', 'mbn-theme' ), value: 'contain' },
	{ label: __( 'Auto', 'mbn-theme' ), value: 'auto' },
];

const BG_POSITION_OPTIONS = [
	{ label: __( 'Center Center', 'mbn-theme' ), value: 'center center' },
	{ label: __( 'Top Left', 'mbn-theme' ), value: 'top left' },
	{ label: __( 'Top Center', 'mbn-theme' ), value: 'top center' },
	{ label: __( 'Top Right', 'mbn-theme' ), value: 'top right' },
	{ label: __( 'Center Left', 'mbn-theme' ), value: 'center left' },
	{ label: __( 'Center Right', 'mbn-theme' ), value: 'center right' },
	{ label: __( 'Bottom Left', 'mbn-theme' ), value: 'bottom left' },
	{ label: __( 'Bottom Center', 'mbn-theme' ), value: 'bottom center' },
	{ label: __( 'Bottom Right', 'mbn-theme' ), value: 'bottom right' },
];

const BG_REPEAT_OPTIONS = [
	{ label: __( 'No Repeat', 'mbn-theme' ), value: 'no-repeat' },
	{ label: __( 'Repeat', 'mbn-theme' ), value: 'repeat' },
	{ label: __( 'Repeat X', 'mbn-theme' ), value: 'repeat-x' },
	{ label: __( 'Repeat Y', 'mbn-theme' ), value: 'repeat-y' },
];

const BG_ATTACHMENT_OPTIONS = [
	{ label: __( 'Scroll', 'mbn-theme' ), value: 'scroll' },
	{ label: __( 'Fixed', 'mbn-theme' ), value: 'fixed' },
];

function shadowFromPreset( shadow, custom ) {
	if ( shadow === 'custom' ) return custom || '';
	if ( shadow === 'sm' ) return '0 1px 2px rgba(0,0,0,.1)';
	if ( shadow === 'md' ) return '0 4px 10px rgba(0,0,0,.15)';
	if ( shadow === 'lg' ) return '0 12px 30px rgba(0,0,0,.2)';
	return '';
}

export default function Edit( { attributes, setAttributes, clientId } ) {
	useEffect( () => {
		if ( ! attributes.blockId ) {
			setAttributes( { blockId: `mbn-section-${ clientId.slice( 0, 8 ) }` } );
		}
	}, [ attributes.blockId, clientId, setAttributes ] );

	const sectionStyle = {
		position: 'relative',
	};
	
	const containerStyle = {
		textAlign: attributes.alignment || undefined,
		justifyContent: attributes.justifyContent || undefined,
		alignItems: attributes.alignItems || undefined,
		
		boxShadow: shadowFromPreset( attributes.shadow, attributes.shadowCustom ) || undefined,
		
		borderStyle: attributes.borderStyle && attributes.borderStyle !== 'none' ? attributes.borderStyle : undefined,
		borderWidth: attributes.borderWidth || undefined,
		borderColor: attributes.borderColor || undefined,
		borderRadius: attributes.borderRadius || undefined,
		
		paddingTop: attributes.paddingTop || undefined,
		paddingRight: attributes.paddingRight || undefined,
		paddingBottom: attributes.paddingBottom || undefined,
		paddingLeft: attributes.paddingLeft || undefined,
		
		marginTop: attributes.marginTop || undefined,
		marginRight: attributes.marginRight || undefined,
		marginBottom: attributes.marginBottom || undefined,
		marginLeft: attributes.marginLeft || undefined,
		
		width: attributes.width || undefined,
		maxWidth: attributes.maxWidth || undefined,
		minWidth: attributes.minWidth || undefined,
		
		height: attributes.height || undefined,
		maxHeight: attributes.maxHeight || undefined,
		minHeight: attributes.minHeight || undefined,
	};

	// Background styles
	if ( attributes.bgType === 'color' && attributes.bgColor ) {
		sectionStyle.backgroundColor = attributes.bgColor;
	}

	if ( attributes.bgType === 'gradient' && attributes.bgGradient ) {
		sectionStyle.background = attributes.bgGradient;
	}

	if ( attributes.bgType === 'image' && attributes.bgImageUrl ) {
		sectionStyle.backgroundImage = `url(${ attributes.bgImageUrl })`;
		sectionStyle.backgroundSize = attributes.bgImageSize || 'cover';
		sectionStyle.backgroundPosition = attributes.bgImagePosition || 'center center';
		sectionStyle.backgroundRepeat = attributes.bgImageRepeat || 'no-repeat';
		sectionStyle.backgroundAttachment = attributes.bgImageAttachment || 'scroll';
	}

	const blockProps = useBlockProps( {
		className: 'mbn-section',
		style: sectionStyle,
	} );

	return (
		<>
			<InspectorControls>
				{/* Layout Panel */}
				<PanelBody title={ __( 'Layout', 'mbn-theme' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Text Alignment', 'mbn-theme' ) }
						value={ attributes.alignment }
						options={ ALIGNMENT_OPTIONS }
						onChange={ ( value ) => setAttributes( { alignment: value } ) }
					/>

					<SelectControl
						label={ __( 'Justify Content', 'mbn-theme' ) }
						value={ attributes.justifyContent }
						options={ JUSTIFY_CONTENT_OPTIONS }
						onChange={ ( value ) => setAttributes( { justifyContent: value } ) }
					/>

					<SelectControl
						label={ __( 'Align Items', 'mbn-theme' ) }
						value={ attributes.alignItems }
						options={ ALIGN_ITEMS_OPTIONS }
						onChange={ ( value ) => setAttributes( { alignItems: value } ) }
					/>

					<hr />

					<SelectControl
						label={ __( 'Shadow', 'mbn-theme' ) }
						value={ attributes.shadow }
						options={ SHADOW_OPTIONS }
						onChange={ ( value ) => setAttributes( { shadow: value } ) }
					/>

					{ attributes.shadow === 'custom' && (
						<TextControl
							label={ __( 'Custom Shadow', 'mbn-theme' ) }
							value={ attributes.shadowCustom }
							onChange={ ( value ) => setAttributes( { shadowCustom: value } ) }
							placeholder="0 4px 10px rgba(0,0,0,.15)"
						/>
					) }

					<hr />

					<SelectControl
						label={ __( 'Border Style', 'mbn-theme' ) }
						value={ attributes.borderStyle }
						options={ BORDER_STYLE_OPTIONS }
						onChange={ ( value ) => setAttributes( { borderStyle: value } ) }
					/>

					{ attributes.borderStyle && attributes.borderStyle !== 'none' && (
						<>
							<UnitControl
								label={ __( 'Border Width', 'mbn-theme' ) }
								value={ attributes.borderWidth }
								onChange={ ( value ) => setAttributes( { borderWidth: value } ) }
							/>

							<TextControl
								label={ __( 'Border Color', 'mbn-theme' ) }
								value={ attributes.borderColor }
								onChange={ ( value ) => setAttributes( { borderColor: value } ) }
								type="color"
							/>

							<UnitControl
								label={ __( 'Border Radius', 'mbn-theme' ) }
								value={ attributes.borderRadius }
								onChange={ ( value ) => setAttributes( { borderRadius: value } ) }
							/>
						</>
					) }

					<hr />

					<p style={ { fontWeight: 600, marginBottom: '8px' } }>
						{ __( 'Padding', 'mbn-theme' ) }
					</p>
					<div style={ { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' } }>
						<UnitControl
							label={ __( 'Top', 'mbn-theme' ) }
							value={ attributes.paddingTop }
							onChange={ ( value ) => setAttributes( { paddingTop: value } ) }
						/>
						<UnitControl
							label={ __( 'Right', 'mbn-theme' ) }
							value={ attributes.paddingRight }
							onChange={ ( value ) => setAttributes( { paddingRight: value } ) }
						/>
						<UnitControl
							label={ __( 'Bottom', 'mbn-theme' ) }
							value={ attributes.paddingBottom }
							onChange={ ( value ) => setAttributes( { paddingBottom: value } ) }
						/>
						<UnitControl
							label={ __( 'Left', 'mbn-theme' ) }
							value={ attributes.paddingLeft }
							onChange={ ( value ) => setAttributes( { paddingLeft: value } ) }
						/>
					</div>

					<hr />

					<p style={ { fontWeight: 600, marginBottom: '8px' } }>
						{ __( 'Margin', 'mbn-theme' ) }
					</p>
					<div style={ { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' } }>
						<UnitControl
							label={ __( 'Top', 'mbn-theme' ) }
							value={ attributes.marginTop }
							onChange={ ( value ) => setAttributes( { marginTop: value } ) }
						/>
						<UnitControl
							label={ __( 'Right', 'mbn-theme' ) }
							value={ attributes.marginRight }
							onChange={ ( value ) => setAttributes( { marginRight: value } ) }
						/>
						<UnitControl
							label={ __( 'Bottom', 'mbn-theme' ) }
							value={ attributes.marginBottom }
							onChange={ ( value ) => setAttributes( { marginBottom: value } ) }
						/>
						<UnitControl
							label={ __( 'Left', 'mbn-theme' ) }
							value={ attributes.marginLeft }
							onChange={ ( value ) => setAttributes( { marginLeft: value } ) }
						/>
					</div>

					<hr />

					<p style={ { fontWeight: 600, marginBottom: '8px' } }>
						{ __( 'Width', 'mbn-theme' ) }
					</p>
					<UnitControl
						label={ __( 'Width', 'mbn-theme' ) }
						value={ attributes.width }
						onChange={ ( value ) => setAttributes( { width: value } ) }
					/>
					<UnitControl
						label={ __( 'Max Width', 'mbn-theme' ) }
						value={ attributes.maxWidth }
						onChange={ ( value ) => setAttributes( { maxWidth: value } ) }
					/>
					<UnitControl
						label={ __( 'Min Width', 'mbn-theme' ) }
						value={ attributes.minWidth }
						onChange={ ( value ) => setAttributes( { minWidth: value } ) }
					/>

					<hr />

					<p style={ { fontWeight: 600, marginBottom: '8px' } }>
						{ __( 'Height', 'mbn-theme' ) }
					</p>
					<UnitControl
						label={ __( 'Height', 'mbn-theme' ) }
						value={ attributes.height }
						onChange={ ( value ) => setAttributes( { height: value } ) }
					/>
					<UnitControl
						label={ __( 'Max Height', 'mbn-theme' ) }
						value={ attributes.maxHeight }
						onChange={ ( value ) => setAttributes( { maxHeight: value } ) }
					/>
					<UnitControl
						label={ __( 'Min Height', 'mbn-theme' ) }
						value={ attributes.minHeight }
						onChange={ ( value ) => setAttributes( { minHeight: value } ) }
					/>
				</PanelBody>

				{/* Background Panel */}
				<PanelBody title={ __( 'Background', 'mbn-theme' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Background Type', 'mbn-theme' ) }
						value={ attributes.bgType }
						options={ BG_TYPE_OPTIONS }
						onChange={ ( value ) => setAttributes( { bgType: value } ) }
					/>

					{ attributes.bgType === 'color' && (
						<div style={ { marginTop: '12px' } }>
							<p style={ { marginBottom: '8px' } }>
								{ __( 'Background Color', 'mbn-theme' ) }
							</p>
							<ColorPicker
								color={ attributes.bgColor }
								onChange={ ( value ) => setAttributes( { bgColor: value } ) }
								enableAlpha
							/>
						</div>
					) }

					{ attributes.bgType === 'gradient' && (
						<TextControl
							label={ __( 'Gradient CSS', 'mbn-theme' ) }
							value={ attributes.bgGradient }
							onChange={ ( value ) => setAttributes( { bgGradient: value } ) }
							placeholder="linear-gradient(135deg, #667eea 0%, #764ba2 100%)"
							help={ __( 'Enter a CSS gradient value', 'mbn-theme' ) }
						/>
					) }

					{ attributes.bgType === 'image' && (
						<>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => {
										setAttributes( {
											bgImageId: media.id,
											bgImageUrl: media.url,
										} );
									} }
									allowedTypes={ [ 'image' ] }
									value={ attributes.bgImageId }
									render={ ( { open } ) => (
										<div style={ { marginTop: '12px' } }>
											{ attributes.bgImageUrl ? (
												<div>
													<img
														src={ attributes.bgImageUrl }
														alt={ __( 'Background', 'mbn-theme' ) }
														style={ { maxWidth: '100%', height: 'auto' } }
													/>
													<Button
														onClick={ open }
														variant="secondary"
														style={ { marginTop: '8px', marginRight: '8px' } }
													>
														{ __( 'Replace Image', 'mbn-theme' ) }
													</Button>
													<Button
														onClick={ () => {
															setAttributes( {
																bgImageId: 0,
																bgImageUrl: '',
															} );
														} }
														variant="secondary"
														isDestructive
														style={ { marginTop: '8px' } }
													>
														{ __( 'Remove Image', 'mbn-theme' ) }
													</Button>
												</div>
											) : (
												<Button onClick={ open } variant="primary">
													{ __( 'Select Background Image', 'mbn-theme' ) }
												</Button>
											) }
										</div>
									) }
								/>
							</MediaUploadCheck>

							{ attributes.bgImageUrl && (
								<>
									<SelectControl
										label={ __( 'Background Size', 'mbn-theme' ) }
										value={ attributes.bgImageSize }
										options={ BG_SIZE_OPTIONS }
										onChange={ ( value ) => setAttributes( { bgImageSize: value } ) }
									/>

									<SelectControl
										label={ __( 'Background Position', 'mbn-theme' ) }
										value={ attributes.bgImagePosition }
										options={ BG_POSITION_OPTIONS }
										onChange={ ( value ) => setAttributes( { bgImagePosition: value } ) }
									/>

									<SelectControl
										label={ __( 'Background Repeat', 'mbn-theme' ) }
										value={ attributes.bgImageRepeat }
										options={ BG_REPEAT_OPTIONS }
										onChange={ ( value ) => setAttributes( { bgImageRepeat: value } ) }
									/>

									<SelectControl
										label={ __( 'Background Attachment', 'mbn-theme' ) }
										value={ attributes.bgImageAttachment }
										options={ BG_ATTACHMENT_OPTIONS }
										onChange={ ( value ) => setAttributes( { bgImageAttachment: value } ) }
									/>
								</>
							) }
						</>
					) }

					{ attributes.bgType === 'video' && (
						<>
							<TextControl
								label={ __( 'Video URL', 'mbn-theme' ) }
								value={ attributes.bgVideoUrl }
								onChange={ ( value ) => setAttributes( { bgVideoUrl: value } ) }
								placeholder="https://example.com/video.mp4"
								help={ __( 'Enter a direct link to an MP4 video', 'mbn-theme' ) }
							/>

							<RangeControl
								label={ __( 'Video Opacity', 'mbn-theme' ) }
								value={ attributes.bgVideoOpacity }
								onChange={ ( value ) => setAttributes( { bgVideoOpacity: value } ) }
								min={ 0 }
								max={ 1 }
								step={ 0.1 }
							/>
						</>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ attributes.bgType === 'video' && attributes.bgVideoUrl && (
					<video
						autoPlay
						loop
						muted
						playsInline
						style={ {
							position: 'absolute',
							top: 0,
							left: 0,
							width: '100%',
							height: '100%',
							objectFit: 'cover',
							opacity: attributes.bgVideoOpacity,
							pointerEvents: 'none',
							zIndex: -1,
						} }
					>
						<source src={ attributes.bgVideoUrl } type="video/mp4" />
					</video>
				) }
				
				<div className="container" style={ containerStyle }>
					<InnerBlocks />
				</div>
			</div>
		</>
	);
}
