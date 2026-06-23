import {
	registerBlockType,
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	RichText,
	PanelBody,
	TextareaControl,
	TextControl,
	Button,
	RangeControl,
	useState,
	__,
} from '@mbn/editor';
import metadata from './block.json';

function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow,
		heading,
		body,
		backgroundImageId,
		backgroundImageUrl,
		backgroundImageAlt,
		minHeight,
		overlayOpacity,
		contentMaxWidth,
	} = attributes;
	const [ activeSection, setActiveSection ] = useState( 'content' );

	const blockProps = useBlockProps( {
		className: 'mbn-hero mbn-hero-editor-composer',
		style: {
			'--mbn-hero-min-height': `${ minHeight }px`,
			'--mbn-hero-overlay-opacity': overlayOpacity,
			'--mbn-hero-content-width': `${ contentMaxWidth }px`,
		},
	} );

	const previewStyles = {
		frame: {
			padding: '18px',
			border: '1px solid #d1d5db',
			borderRadius: '8px',
			background: '#f3f4f6',
			display: 'grid',
			gap: '12px',
		},
		section: {
			border: '1px dashed #9ca3af',
			borderRadius: '6px',
			background: '#ffffff',
			padding: '12px',
		},
		active: {
			border: '2px solid #2563eb',
		},
		header: {
			display: 'flex',
			alignItems: 'center',
			justifyContent: 'space-between',
			marginBottom: '8px',
		},
		title: {
			margin: 0,
			fontSize: '12px',
			fontWeight: 700,
			textTransform: 'uppercase',
			letterSpacing: '0.05em',
			color: '#374151',
		},
		jump: {
			fontSize: '11px',
			fontWeight: 600,
			padding: '4px 8px',
			border: '1px solid #c7d2fe',
			borderRadius: '999px',
			background: '#eef2ff',
			color: '#3730a3',
			cursor: 'pointer',
		},
		muted: {
			margin: '0',
			fontSize: '12px',
			color: '#6b7280',
		},
	};

	const sectionStyle = ( key ) =>
		activeSection === key
			? { ...previewStyles.section, ...previewStyles.active }
			: previewStyles.section;

	return (
		<>
			<InspectorControls key={ `hero-inspector-${ activeSection }` }>
				<PanelBody title={ __( 'Background Image', 'mbn-theme' ) } initialOpen={ activeSection === 'media' }>
					<TextControl
						label={ __( 'Background Image Alt Text', 'mbn-theme' ) }
						value={ backgroundImageAlt }
						onChange={ ( value ) => setAttributes( { backgroundImageAlt: value } ) }
					/>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( {
									backgroundImageId: media.id,
									backgroundImageUrl: media.url,
									backgroundImageAlt: media.alt || backgroundImageAlt,
								} )
							}
							allowedTypes={ [ 'image' ] }
							value={ backgroundImageId }
							render={ ( { open } ) => (
								<>
									{ backgroundImageUrl && (
										<img
											src={ backgroundImageUrl }
											alt=""
											className="mbn-hero__media-preview"
										/>
									) }
									<Button variant="secondary" onClick={ open }>
										{ backgroundImageId
											? __( 'Replace Image', 'mbn-theme' )
											: __( 'Upload Image', 'mbn-theme' ) }
									</Button>
									{ backgroundImageId > 0 && (
										<Button
											variant="link"
											isDestructive
											onClick={ () =>
												setAttributes( {
													backgroundImageId: 0,
													backgroundImageUrl: '',
													backgroundImageAlt: '',
												} )
											}
										>
											{ __( 'Remove', 'mbn-theme' ) }
										</Button>
									) }
								</>
							) }
						/>
					</MediaUploadCheck>
				</PanelBody>

				<PanelBody title={ __( 'Content', 'mbn-theme' ) } initialOpen={ activeSection === 'content' }>
					<TextareaControl
						label={ __( 'Body Copy', 'mbn-theme' ) }
						value={ body }
						onChange={ ( value ) => setAttributes( { body: value } ) }
						rows={ 3 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Appearance', 'mbn-theme' ) } initialOpen={ activeSection === 'appearance' }>
					<RangeControl
						label={ __( 'Minimum Height', 'mbn-theme' ) }
						value={ minHeight }
						onChange={ ( value ) => setAttributes( { minHeight: value } ) }
						min={ 420 }
						max={ 900 }
						step={ 10 }
					/>
					<RangeControl
						label={ __( 'Overlay Opacity', 'mbn-theme' ) }
						value={ overlayOpacity }
						onChange={ ( value ) => setAttributes( { overlayOpacity: value } ) }
						min={ 0.3 }
						max={ 1 }
						step={ 0.05 }
					/>
					<RangeControl
						label={ __( 'Content Width', 'mbn-theme' ) }
						value={ contentMaxWidth }
						onChange={ ( value ) => setAttributes( { contentMaxWidth: value } ) }
						min={ 420 }
						max={ 760 }
						step={ 10 }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div style={ previewStyles.frame }>
					<div style={ sectionStyle( 'media' ) }>
						<div style={ previewStyles.header }>
							<p style={ previewStyles.title }>{ __( 'Hero Media Position', 'mbn-theme' ) }</p>
							<button type="button" style={ previewStyles.jump } onClick={ () => setActiveSection( 'media' ) }>
								{ __( 'Edit Media', 'mbn-theme' ) }
							</button>
						</div>
						<p style={ previewStyles.muted }>
							{ backgroundImageUrl
								? __( 'Background image selected from media library.', 'mbn-theme' )
								: __( 'No image selected. Frontend fallback image will be used.', 'mbn-theme' ) }
						</p>
					</div>

					<div style={ sectionStyle( 'content' ) }>
						<div style={ previewStyles.header }>
							<p style={ previewStyles.title }>{ __( 'Hero Content Position', 'mbn-theme' ) }</p>
							<button type="button" style={ previewStyles.jump } onClick={ () => setActiveSection( 'content' ) }>
								{ __( 'Edit Content', 'mbn-theme' ) }
							</button>
						</div>
						<RichText
							tagName="p"
							className="mbn-hero__eyebrow"
							value={ eyebrow }
							onChange={ ( value ) => setAttributes( { eyebrow: value } ) }
							placeholder={ __( 'Enter eyebrow…', 'mbn-theme' ) }
						/>
						<RichText
							tagName="h2"
							className="mbn-hero__heading"
							value={ heading }
							onChange={ ( value ) => setAttributes( { heading: value } ) }
							placeholder={ __( 'Enter heading…', 'mbn-theme' ) }
						/>
						<RichText
							tagName="p"
							className="mbn-hero__body"
							value={ body }
							onChange={ ( value ) => setAttributes( { body: value } ) }
							placeholder={ __( 'Enter body copy…', 'mbn-theme' ) }
						/>
					</div>

					<div style={ sectionStyle( 'appearance' ) }>
						<div style={ previewStyles.header }>
							<p style={ previewStyles.title }>{ __( 'Hero Appearance Position', 'mbn-theme' ) }</p>
							<button type="button" style={ previewStyles.jump } onClick={ () => setActiveSection( 'appearance' ) }>
								{ __( 'Edit Appearance', 'mbn-theme' ) }
							</button>
						</div>
						<p style={ previewStyles.muted }>
							{ __( 'Min Height:', 'mbn-theme' ) } { minHeight }px | { __( 'Overlay:', 'mbn-theme' ) } { overlayOpacity } | { __( 'Content Width:', 'mbn-theme' ) } { contentMaxWidth }px
						</p>
					</div>
				</div>
			</section>
		</>
	);
}

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );