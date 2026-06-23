/**
 * Shared layout inspector controls for MBN blocks.
 *
 * @package CustomTheme
 */

import { InspectorControls, PanelColorSettings, MediaUpload } from '@wordpress/block-editor';
import { PanelBody, TextControl, RangeControl, Button, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

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

/**
 * @param {Object}   props
 * @param {Object}   props.attributes
 * @param {Function} props.setAttributes
 * @return {JSX.Element} Inspector controls for layout settings.
 */
export default function LayoutControls( { attributes, setAttributes } ) {
	const {
		backgroundImageId,
		backgroundImageUrl,
		backgroundVideoId,
		backgroundVideoUrl,
		overlayOpacity,
		customCss,
	} = attributes;

	return (
		<InspectorControls>
			<PanelBody title={ __( 'Spacing', 'mbn-theme' ) } initialOpen={ false }>
				{ SPACING_FIELDS.map( ( field ) => (
					<TextControl
						key={ field.key }
						label={ field.label }
						value={ attributes[ field.key ] || '' }
						onChange={ ( value ) => setAttributes( { [ field.key ]: value } ) }
						help={ __( 'Examples: 0, 1rem, 16px, var(--wp--preset--spacing--40)', 'mbn-theme' ) }
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
					value={ backgroundImageId }
					render={ ( { open } ) => (
						<div>
							<Button onClick={ open } variant="secondary">
								{ backgroundImageUrl
									? __( 'Replace Background Image', 'mbn-theme' )
									: __( 'Select Background Image', 'mbn-theme' ) }
							</Button>
							{ backgroundImageUrl && (
								<Button
									onClick={ () =>
										setAttributes( {
											backgroundImageUrl: '',
											backgroundImageId: 0,
										} )
									}
									variant="link"
									isDestructive
								>
									{ __( 'Remove Background Image', 'mbn-theme' ) }
								</Button>
							) }
						</div>
					) }
				/>

				<MediaUpload
					onSelect={ ( media ) =>
						setAttributes( {
							backgroundVideoUrl: media.url,
							backgroundVideoId: media.id,
						} )
					}
					allowedTypes={ [ 'video' ] }
					value={ backgroundVideoId }
					render={ ( { open } ) => (
						<div>
							<Button onClick={ open } variant="secondary">
								{ backgroundVideoUrl
									? __( 'Replace Background Video', 'mbn-theme' )
									: __( 'Select Background Video', 'mbn-theme' ) }
							</Button>
							{ backgroundVideoUrl && (
								<Button
									onClick={ () =>
										setAttributes( {
											backgroundVideoUrl: '',
											backgroundVideoId: 0,
										} )
									}
									variant="link"
									isDestructive
								>
									{ __( 'Remove Background Video', 'mbn-theme' ) }
								</Button>
							) }
						</div>
					) }
				/>

				<RangeControl
					label={ __( 'Overlay Opacity (%)', 'mbn-theme' ) }
					value={ overlayOpacity }
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
					value={ customCss }
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
