import {
	InspectorAdvancedControls,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	RichText,
	URLInputButton,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	BaseControl,
	Button,
	PanelBody,
	SelectControl,
	TextControl,
	TextareaControl,
	__experimentalBoxControl as BoxControl,
	__experimentalUnitControl as UnitControl,
} from '@wordpress/components';
import { Fragment, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const TARGET_OPTIONS = [
	{ label: __( 'Same Tab', 'mbn-theme' ), value: '_self' },
	{ label: __( 'New Tab', 'mbn-theme' ), value: '_blank' },
];

const ALIGN_OPTIONS = [
	{ label: __( 'Left', 'mbn-theme' ), value: 'left' },
	{ label: __( 'Center', 'mbn-theme' ), value: 'center' },
	{ label: __( 'Right', 'mbn-theme' ), value: 'right' },
];

const TITLE_TAG_OPTIONS = [
	{ label: 'h2', value: 'h2' },
	{ label: 'h3', value: 'h3' },
	{ label: 'h4', value: 'h4' },
	{ label: 'h5', value: 'h5' },
	{ label: 'h6', value: 'h6' },
	{ label: 'div', value: 'div' },
];

const BUTTON_VARIANTS = [
	{ label: __( 'Primary', 'mbn-theme' ), value: 'primary' },
	{ label: __( 'Secondary', 'mbn-theme' ), value: 'secondary' },
	{ label: __( 'Outline', 'mbn-theme' ), value: 'outline' },
];

function shadowFromPreset( shadow, custom ) {
	if ( shadow === 'custom' ) return custom || '';
	if ( shadow === 'sm' ) return '0 1px 2px rgba(0,0,0,.1)';
	if ( shadow === 'md' ) return '0 4px 10px rgba(0,0,0,.15)';
	if ( shadow === 'lg' ) return '0 12px 30px rgba(0,0,0,.2)';
	return '';
}

function baseButtonStyles( variant ) {
	if ( variant === 'secondary' ) {
		return { backgroundColor: '#f3f4f6', color: '#111827', border: '1px solid #d1d5db' };
	}
	if ( variant === 'outline' ) {
		return { backgroundColor: 'transparent', color: '#111827', border: '1px solid #111827' };
	}
	return { backgroundColor: '#111827', color: '#ffffff', border: '1px solid #111827' };
}

export default function Edit( { attributes, setAttributes, clientId } ) {
	useEffect( () => {
		if ( ! attributes.blockId ) {
			setAttributes( { blockId: `image-box-${ clientId.slice( 0, 8 ) }` } );
		}
	}, [ attributes.blockId, clientId, setAttributes ] );

	const wrapperStyle = {
		width: attributes.containerWidth || undefined,
		paddingTop: attributes.containerPaddingTop || undefined,
		paddingRight: attributes.containerPaddingRight || undefined,
		paddingBottom: attributes.containerPaddingBottom || undefined,
		paddingLeft: attributes.containerPaddingLeft || undefined,
		marginTop: attributes.containerMarginTop || undefined,
		marginRight: attributes.containerMarginRight || undefined,
		marginBottom: attributes.containerMarginBottom || undefined,
		marginLeft: attributes.containerMarginLeft || undefined,
		color: attributes.containerColor || undefined,
		background: attributes.containerBgType === 'gradient' ? attributes.containerBgGradient || undefined : undefined,
		backgroundColor: attributes.containerBgType === 'color' ? attributes.containerBgColor || undefined : undefined,
		borderRadius: attributes.containerBorderRadius || undefined,
		borderStyle: attributes.containerBorderStyle && attributes.containerBorderStyle !== 'none' ? attributes.containerBorderStyle : undefined,
		borderWidth: attributes.containerBorderWidth || undefined,
		borderColor: attributes.containerBorderColor || undefined,
		boxShadow: shadowFromPreset( attributes.containerShadow, attributes.containerShadowCustom ) || undefined,
	};

	const imageStyle = {
		width: attributes.imageWidth || undefined,
		height: attributes.imageHeight || undefined,
		objectFit: attributes.imageObjectFit || undefined,
		marginTop: attributes.imageMarginTop || undefined,
		marginRight: attributes.imageMarginRight || undefined,
		marginBottom: attributes.imageMarginBottom || undefined,
		marginLeft: attributes.imageMarginLeft || undefined,
	};

	const titleStyle = {
		maxWidth: attributes.titleMaxWidth || undefined,
		marginTop: attributes.titleMarginTop || undefined,
		marginRight: attributes.titleMarginRight || undefined,
		marginBottom: attributes.titleMarginBottom || undefined,
		marginLeft: attributes.titleMarginLeft || undefined,
		paddingTop: attributes.titlePaddingTop || undefined,
		paddingRight: attributes.titlePaddingRight || undefined,
		paddingBottom: attributes.titlePaddingBottom || undefined,
		paddingLeft: attributes.titlePaddingLeft || undefined,
		fontFamily: attributes.titleFontFamily || undefined,
		fontSize: attributes.titleFontSize || undefined,
		fontWeight: attributes.titleFontWeight || undefined,
		lineHeight: attributes.titleLineHeight || undefined,
		letterSpacing: attributes.titleLetterSpacing || undefined,
		color: attributes.titleColor || undefined,
		textAlign: attributes.titleAlign || undefined,
	};

	const contentStyle = {
		maxWidth: attributes.textareaMaxWidth || undefined,
		marginTop: attributes.textareaMarginTop || undefined,
		marginRight: attributes.textareaMarginRight || undefined,
		marginBottom: attributes.textareaMarginBottom || undefined,
		marginLeft: attributes.textareaMarginLeft || undefined,
		paddingTop: attributes.textareaPaddingTop || undefined,
		paddingRight: attributes.textareaPaddingRight || undefined,
		paddingBottom: attributes.textareaPaddingBottom || undefined,
		paddingLeft: attributes.textareaPaddingLeft || undefined,
		fontFamily: attributes.textareaFontFamily || undefined,
		fontSize: attributes.textareaFontSize || undefined,
		fontWeight: attributes.textareaFontWeight || undefined,
		lineHeight: attributes.textareaLineHeight || undefined,
		letterSpacing: attributes.textareaLetterSpacing || undefined,
		color: attributes.textareaColor || undefined,
		textAlign: attributes.textareaAlign || undefined,
	};

	const sharedButtonStyle = {
		marginTop: attributes.buttonMarginTop || undefined,
		marginRight: attributes.buttonMarginRight || undefined,
		marginBottom: attributes.buttonMarginBottom || undefined,
		marginLeft: attributes.buttonMarginLeft || undefined,
		paddingTop: attributes.buttonPaddingTop || undefined,
		paddingRight: attributes.buttonPaddingRight || undefined,
		paddingBottom: attributes.buttonPaddingBottom || undefined,
		paddingLeft: attributes.buttonPaddingLeft || undefined,
		fontFamily: attributes.buttonFontFamily || undefined,
		fontSize: attributes.buttonFontSize || undefined,
		fontWeight: attributes.buttonFontWeight || undefined,
		lineHeight: attributes.buttonLineHeight || undefined,
		letterSpacing: attributes.buttonLetterSpacing || undefined,
		borderRadius: attributes.buttonBorderRadius || undefined,
		borderStyle: attributes.buttonBorderStyle && attributes.buttonBorderStyle !== 'none' ? attributes.buttonBorderStyle : undefined,
		borderWidth: attributes.buttonBorderWidth || undefined,
		borderColor: attributes.buttonBorderColor || undefined,
	};

	const contentAlign = attributes.contentPosition || 'left';
	const blockClass = attributes.blockId || '';
	const blockProps = useBlockProps( {
		className: `mbn-image-box is-pos-${ contentAlign } ${ blockClass }`.trim(),
		style: wrapperStyle,
	} );

	const editorCustomCss = attributes.customCss && blockClass
		? attributes.customCss.replaceAll( '{{WRAPPER}}', `.${ blockClass }` )
		: '';

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={ __( 'Box Settings', 'mbn-theme' ) } initialOpen={ true }>
					<SelectControl label={ __( 'Alignment', 'mbn-theme' ) } value={ attributes.contentPosition } onChange={ ( contentPosition ) => setAttributes( { contentPosition } ) } options={ ALIGN_OPTIONS } />
					<SelectControl label={ __( 'Background Type', 'mbn-theme' ) } value={ attributes.containerBgType } onChange={ ( containerBgType ) => setAttributes( { containerBgType } ) } options={ [ { label: __( 'Color', 'mbn-theme' ), value: 'color' }, { label: __( 'Gradient', 'mbn-theme' ), value: 'gradient' } ] } />
					{ attributes.containerBgType === 'gradient' ? (
						<TextControl label={ __( 'Background Gradient', 'mbn-theme' ) } value={ attributes.containerBgGradient } onChange={ ( containerBgGradient ) => setAttributes( { containerBgGradient } ) } />
					) : (
						<TextControl label={ __( 'Background Color', 'mbn-theme' ) } value={ attributes.containerBgColor } type="color" onChange={ ( containerBgColor ) => setAttributes( { containerBgColor } ) } />
					) }
					<TextControl label={ __( 'Color', 'mbn-theme' ) } value={ attributes.containerColor } type="color" onChange={ ( containerColor ) => setAttributes( { containerColor } ) } />
					<SelectControl label={ __( 'Shadow', 'mbn-theme' ) } value={ attributes.containerShadow } onChange={ ( containerShadow ) => setAttributes( { containerShadow } ) } options={ [ { label: __( 'None', 'mbn-theme' ), value: 'none' }, { label: __( 'Small', 'mbn-theme' ), value: 'sm' }, { label: __( 'Medium', 'mbn-theme' ), value: 'md' }, { label: __( 'Large', 'mbn-theme' ), value: 'lg' }, { label: __( 'Custom', 'mbn-theme' ), value: 'custom' } ] } />
					{ attributes.containerShadow === 'custom' && <TextControl label={ __( 'Custom Shadow', 'mbn-theme' ) } value={ attributes.containerShadowCustom } onChange={ ( containerShadowCustom ) => setAttributes( { containerShadowCustom } ) } /> }
					<SelectControl label={ __( 'Border Style', 'mbn-theme' ) } value={ attributes.containerBorderStyle } onChange={ ( containerBorderStyle ) => setAttributes( { containerBorderStyle } ) } options={ [ { label: __( 'None', 'mbn-theme' ), value: 'none' }, { label: __( 'Solid', 'mbn-theme' ), value: 'solid' }, { label: __( 'Dashed', 'mbn-theme' ), value: 'dashed' }, { label: __( 'Dotted', 'mbn-theme' ), value: 'dotted' } ] } />
					<UnitControl label={ __( 'Border Width', 'mbn-theme' ) } value={ attributes.containerBorderWidth } onChange={ ( containerBorderWidth ) => setAttributes( { containerBorderWidth } ) } />
					<TextControl label={ __( 'Border Color', 'mbn-theme' ) } value={ attributes.containerBorderColor } type="color" onChange={ ( containerBorderColor ) => setAttributes( { containerBorderColor } ) } />
					<UnitControl label={ __( 'Border Radius', 'mbn-theme' ) } value={ attributes.containerBorderRadius } onChange={ ( containerBorderRadius ) => setAttributes( { containerBorderRadius } ) } />
					<BaseControl label={ __( 'Padding', 'mbn-theme' ) }>
						<BoxControl values={ { top: attributes.containerPaddingTop, right: attributes.containerPaddingRight, bottom: attributes.containerPaddingBottom, left: attributes.containerPaddingLeft } } onChange={ ( next ) => setAttributes( { containerPaddingTop: next?.top || '', containerPaddingRight: next?.right || '', containerPaddingBottom: next?.bottom || '', containerPaddingLeft: next?.left || '' } ) } />
					</BaseControl>
					<BaseControl label={ __( 'Margin', 'mbn-theme' ) }>
						<BoxControl values={ { top: attributes.containerMarginTop, right: attributes.containerMarginRight, bottom: attributes.containerMarginBottom, left: attributes.containerMarginLeft } } onChange={ ( next ) => setAttributes( { containerMarginTop: next?.top || '', containerMarginRight: next?.right || '', containerMarginBottom: next?.bottom || '', containerMarginLeft: next?.left || '' } ) } />
					</BaseControl>
					<UnitControl label={ __( 'Width', 'mbn-theme' ) } value={ attributes.containerWidth } onChange={ ( containerWidth ) => setAttributes( { containerWidth } ) } />
					<URLInputButton label={ __( 'Link (Optional)', 'mbn-theme' ) } url={ attributes.boxLinkUrl } onChange={ ( boxLinkUrl ) => setAttributes( { boxLinkUrl } ) } />
					<SelectControl label={ __( 'Link Target', 'mbn-theme' ) } value={ attributes.boxLinkTarget } onChange={ ( boxLinkTarget ) => setAttributes( { boxLinkTarget } ) } options={ TARGET_OPTIONS } />
				</PanelBody>

				<PanelBody title={ __( 'Image Settings', 'mbn-theme' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => setAttributes( { imageId: media?.id || 0, imageUrl: media?.url || '', imageAlt: media?.alt || attributes.imageAlt } ) }
							allowedTypes={ [ 'image' ] }
							value={ attributes.imageId }
							render={ ( { open } ) => <Button variant="secondary" onClick={ open }>{ attributes.imageUrl ? __( 'Replace Image', 'mbn-theme' ) : __( 'Upload Image', 'mbn-theme' ) }</Button> }
						/>
					</MediaUploadCheck>
					{ attributes.imageUrl && <img src={ attributes.imageUrl } alt={ attributes.imageAlt || '' } style={ { marginTop: '8px', width: '100%', borderRadius: '6px' } } /> }
					<SelectControl label={ __( 'Image Size', 'mbn-theme' ) } value={ attributes.imageSize } onChange={ ( imageSize ) => setAttributes( { imageSize } ) } options={ [ { label: __( 'Thumbnail', 'mbn-theme' ), value: 'thumbnail' }, { label: __( 'Medium', 'mbn-theme' ), value: 'medium' }, { label: __( 'Large', 'mbn-theme' ), value: 'large' }, { label: __( 'Full', 'mbn-theme' ), value: 'full' } ] } />
					<TextControl label={ __( 'Alt', 'mbn-theme' ) } value={ attributes.imageAlt } onChange={ ( imageAlt ) => setAttributes( { imageAlt } ) } />
					<BaseControl label={ __( 'Margin', 'mbn-theme' ) }>
						<BoxControl values={ { top: attributes.imageMarginTop, right: attributes.imageMarginRight, bottom: attributes.imageMarginBottom, left: attributes.imageMarginLeft } } onChange={ ( next ) => setAttributes( { imageMarginTop: next?.top || '', imageMarginRight: next?.right || '', imageMarginBottom: next?.bottom || '', imageMarginLeft: next?.left || '' } ) } />
					</BaseControl>
					<UnitControl label={ __( 'Width', 'mbn-theme' ) } value={ attributes.imageWidth } onChange={ ( imageWidth ) => setAttributes( { imageWidth } ) } />
					<UnitControl label={ __( 'Height', 'mbn-theme' ) } value={ attributes.imageHeight } onChange={ ( imageHeight ) => setAttributes( { imageHeight } ) } />
					<SelectControl label={ __( 'Object Fit', 'mbn-theme' ) } value={ attributes.imageObjectFit } onChange={ ( imageObjectFit ) => setAttributes( { imageObjectFit } ) } options={ [ { label: __( 'Cover', 'mbn-theme' ), value: 'cover' }, { label: __( 'Contain', 'mbn-theme' ), value: 'contain' }, { label: __( 'Fill', 'mbn-theme' ), value: 'fill' }, { label: __( 'None', 'mbn-theme' ), value: 'none' } ] } />
				</PanelBody>

				<PanelBody title={ __( 'Title Settings', 'mbn-theme' ) } initialOpen={ false }>
					<SelectControl label={ __( 'Heading', 'mbn-theme' ) } value={ attributes.titleTag } onChange={ ( titleTag ) => setAttributes( { titleTag } ) } options={ TITLE_TAG_OPTIONS } />
					<BaseControl label={ __( 'Margin', 'mbn-theme' ) }>
						<BoxControl values={ { top: attributes.titleMarginTop, right: attributes.titleMarginRight, bottom: attributes.titleMarginBottom, left: attributes.titleMarginLeft } } onChange={ ( next ) => setAttributes( { titleMarginTop: next?.top || '', titleMarginRight: next?.right || '', titleMarginBottom: next?.bottom || '', titleMarginLeft: next?.left || '' } ) } />
					</BaseControl>
					<BaseControl label={ __( 'Padding', 'mbn-theme' ) }>
						<BoxControl values={ { top: attributes.titlePaddingTop, right: attributes.titlePaddingRight, bottom: attributes.titlePaddingBottom, left: attributes.titlePaddingLeft } } onChange={ ( next ) => setAttributes( { titlePaddingTop: next?.top || '', titlePaddingRight: next?.right || '', titlePaddingBottom: next?.bottom || '', titlePaddingLeft: next?.left || '' } ) } />
					</BaseControl>
					<UnitControl label={ __( 'Max Width', 'mbn-theme' ) } value={ attributes.titleMaxWidth } onChange={ ( titleMaxWidth ) => setAttributes( { titleMaxWidth } ) } />
					<TextControl label={ __( 'Font Family', 'mbn-theme' ) } value={ attributes.titleFontFamily } onChange={ ( titleFontFamily ) => setAttributes( { titleFontFamily } ) } />
					<UnitControl label={ __( 'Font Size', 'mbn-theme' ) } value={ attributes.titleFontSize } onChange={ ( titleFontSize ) => setAttributes( { titleFontSize } ) } />
					<TextControl label={ __( 'Font Weight', 'mbn-theme' ) } value={ attributes.titleFontWeight } onChange={ ( titleFontWeight ) => setAttributes( { titleFontWeight } ) } />
					<UnitControl label={ __( 'Line Height', 'mbn-theme' ) } value={ attributes.titleLineHeight } onChange={ ( titleLineHeight ) => setAttributes( { titleLineHeight } ) } />
					<UnitControl label={ __( 'Letter Spacing', 'mbn-theme' ) } value={ attributes.titleLetterSpacing } onChange={ ( titleLetterSpacing ) => setAttributes( { titleLetterSpacing } ) } />
					<TextControl label={ __( 'Color', 'mbn-theme' ) } type="color" value={ attributes.titleColor } onChange={ ( titleColor ) => setAttributes( { titleColor } ) } />
					<SelectControl label={ __( 'Align', 'mbn-theme' ) } value={ attributes.titleAlign } onChange={ ( titleAlign ) => setAttributes( { titleAlign } ) } options={ [ { label: __( 'Default', 'mbn-theme' ), value: '' }, ...ALIGN_OPTIONS ] } />
				</PanelBody>

				<PanelBody title={ __( 'Content Settings', 'mbn-theme' ) } initialOpen={ false }>
					<BaseControl label={ __( 'Margin', 'mbn-theme' ) }>
						<BoxControl values={ { top: attributes.textareaMarginTop, right: attributes.textareaMarginRight, bottom: attributes.textareaMarginBottom, left: attributes.textareaMarginLeft } } onChange={ ( next ) => setAttributes( { textareaMarginTop: next?.top || '', textareaMarginRight: next?.right || '', textareaMarginBottom: next?.bottom || '', textareaMarginLeft: next?.left || '' } ) } />
					</BaseControl>
					<BaseControl label={ __( 'Padding', 'mbn-theme' ) }>
						<BoxControl values={ { top: attributes.textareaPaddingTop, right: attributes.textareaPaddingRight, bottom: attributes.textareaPaddingBottom, left: attributes.textareaPaddingLeft } } onChange={ ( next ) => setAttributes( { textareaPaddingTop: next?.top || '', textareaPaddingRight: next?.right || '', textareaPaddingBottom: next?.bottom || '', textareaPaddingLeft: next?.left || '' } ) } />
					</BaseControl>
					<UnitControl label={ __( 'Max Width', 'mbn-theme' ) } value={ attributes.textareaMaxWidth } onChange={ ( textareaMaxWidth ) => setAttributes( { textareaMaxWidth } ) } />
					<TextControl label={ __( 'Font Family', 'mbn-theme' ) } value={ attributes.textareaFontFamily } onChange={ ( textareaFontFamily ) => setAttributes( { textareaFontFamily } ) } />
					<UnitControl label={ __( 'Font Size', 'mbn-theme' ) } value={ attributes.textareaFontSize } onChange={ ( textareaFontSize ) => setAttributes( { textareaFontSize } ) } />
					<TextControl label={ __( 'Font Weight', 'mbn-theme' ) } value={ attributes.textareaFontWeight } onChange={ ( textareaFontWeight ) => setAttributes( { textareaFontWeight } ) } />
					<UnitControl label={ __( 'Line Height', 'mbn-theme' ) } value={ attributes.textareaLineHeight } onChange={ ( textareaLineHeight ) => setAttributes( { textareaLineHeight } ) } />
					<UnitControl label={ __( 'Letter Spacing', 'mbn-theme' ) } value={ attributes.textareaLetterSpacing } onChange={ ( textareaLetterSpacing ) => setAttributes( { textareaLetterSpacing } ) } />
					<TextControl label={ __( 'Color', 'mbn-theme' ) } type="color" value={ attributes.textareaColor } onChange={ ( textareaColor ) => setAttributes( { textareaColor } ) } />
					<SelectControl label={ __( 'Align', 'mbn-theme' ) } value={ attributes.textareaAlign } onChange={ ( textareaAlign ) => setAttributes( { textareaAlign } ) } options={ [ { label: __( 'Default', 'mbn-theme' ), value: '' }, ...ALIGN_OPTIONS ] } />
				</PanelBody>

				<PanelBody title={ __( 'Button Settings', 'mbn-theme' ) } initialOpen={ false }>
					<TextControl label={ __( 'Button 1 Text', 'mbn-theme' ) } value={ attributes.button1Text } onChange={ ( button1Text ) => setAttributes( { button1Text } ) } />
					<URLInputButton label={ __( 'Button 1 Link', 'mbn-theme' ) } url={ attributes.button1Url } onChange={ ( button1Url ) => setAttributes( { button1Url } ) } />
					<SelectControl label={ __( 'Button 1 Target', 'mbn-theme' ) } value={ attributes.button1Target } onChange={ ( button1Target ) => setAttributes( { button1Target } ) } options={ TARGET_OPTIONS } />
					<SelectControl label={ __( 'Button 1 Style', 'mbn-theme' ) } value={ attributes.button1Style } onChange={ ( button1Style ) => setAttributes( { button1Style } ) } options={ BUTTON_VARIANTS } />
					<TextControl label={ __( 'Button 2 Text', 'mbn-theme' ) } value={ attributes.button2Text } onChange={ ( button2Text ) => setAttributes( { button2Text } ) } />
					<URLInputButton label={ __( 'Button 2 Link', 'mbn-theme' ) } url={ attributes.button2Url } onChange={ ( button2Url ) => setAttributes( { button2Url } ) } />
					<SelectControl label={ __( 'Button 2 Target', 'mbn-theme' ) } value={ attributes.button2Target } onChange={ ( button2Target ) => setAttributes( { button2Target } ) } options={ TARGET_OPTIONS } />
					<SelectControl label={ __( 'Button 2 Style', 'mbn-theme' ) } value={ attributes.button2Style } onChange={ ( button2Style ) => setAttributes( { button2Style } ) } options={ BUTTON_VARIANTS } />
					<SelectControl label={ __( 'Border Style', 'mbn-theme' ) } value={ attributes.buttonBorderStyle } onChange={ ( buttonBorderStyle ) => setAttributes( { buttonBorderStyle } ) } options={ [ { label: __( 'None', 'mbn-theme' ), value: 'none' }, { label: __( 'Solid', 'mbn-theme' ), value: 'solid' }, { label: __( 'Dashed', 'mbn-theme' ), value: 'dashed' }, { label: __( 'Dotted', 'mbn-theme' ), value: 'dotted' } ] } />
					<UnitControl label={ __( 'Border Width', 'mbn-theme' ) } value={ attributes.buttonBorderWidth } onChange={ ( buttonBorderWidth ) => setAttributes( { buttonBorderWidth } ) } />
					<TextControl label={ __( 'Border Color', 'mbn-theme' ) } type="color" value={ attributes.buttonBorderColor } onChange={ ( buttonBorderColor ) => setAttributes( { buttonBorderColor } ) } />
					<UnitControl label={ __( 'Border Radius', 'mbn-theme' ) } value={ attributes.buttonBorderRadius } onChange={ ( buttonBorderRadius ) => setAttributes( { buttonBorderRadius } ) } />
					<BaseControl label={ __( 'Margin', 'mbn-theme' ) }>
						<BoxControl values={ { top: attributes.buttonMarginTop, right: attributes.buttonMarginRight, bottom: attributes.buttonMarginBottom, left: attributes.buttonMarginLeft } } onChange={ ( next ) => setAttributes( { buttonMarginTop: next?.top || '', buttonMarginRight: next?.right || '', buttonMarginBottom: next?.bottom || '', buttonMarginLeft: next?.left || '' } ) } />
					</BaseControl>
					<BaseControl label={ __( 'Padding', 'mbn-theme' ) }>
						<BoxControl values={ { top: attributes.buttonPaddingTop, right: attributes.buttonPaddingRight, bottom: attributes.buttonPaddingBottom, left: attributes.buttonPaddingLeft } } onChange={ ( next ) => setAttributes( { buttonPaddingTop: next?.top || '', buttonPaddingRight: next?.right || '', buttonPaddingBottom: next?.bottom || '', buttonPaddingLeft: next?.left || '' } ) } />
					</BaseControl>
					<TextControl label={ __( 'Font Family', 'mbn-theme' ) } value={ attributes.buttonFontFamily } onChange={ ( buttonFontFamily ) => setAttributes( { buttonFontFamily } ) } />
					<UnitControl label={ __( 'Font Size', 'mbn-theme' ) } value={ attributes.buttonFontSize } onChange={ ( buttonFontSize ) => setAttributes( { buttonFontSize } ) } />
					<TextControl label={ __( 'Font Weight', 'mbn-theme' ) } value={ attributes.buttonFontWeight } onChange={ ( buttonFontWeight ) => setAttributes( { buttonFontWeight } ) } />
					<UnitControl label={ __( 'Line Height', 'mbn-theme' ) } value={ attributes.buttonLineHeight } onChange={ ( buttonLineHeight ) => setAttributes( { buttonLineHeight } ) } />
					<UnitControl label={ __( 'Letter Spacing', 'mbn-theme' ) } value={ attributes.buttonLetterSpacing } onChange={ ( buttonLetterSpacing ) => setAttributes( { buttonLetterSpacing } ) } />
				</PanelBody>

			</InspectorControls>

			<InspectorAdvancedControls>
				<TextControl label={ __( 'Block ID', 'mbn-theme' ) } value={ attributes.blockId || '' } onChange={ ( blockId ) => setAttributes( { blockId } ) } help={ __( 'Used as custom CSS scope class.', 'mbn-theme' ) } />
				<TextareaControl label={ __( 'Custom CSS', 'mbn-theme' ) } value={ attributes.customCss || '' } onChange={ ( customCss ) => setAttributes( { customCss } ) } help={ __( 'Use {{WRAPPER}} in CSS selector. Example: {{WRAPPER}} .mbn-image-box__title { color: red; }', 'mbn-theme' ) } rows={ 8 } />
			</InspectorAdvancedControls>

			{ editorCustomCss && <style>{ editorCustomCss }</style> }

			<div { ...blockProps }>
				{ attributes.imageUrl && <div className="mbn-image-box__media"><img src={ attributes.imageUrl } alt={ attributes.imageAlt || '' } style={ imageStyle } /></div> }
				<div className={ `mbn-image-box__content has-align-${ contentAlign }` }>
					<RichText tagName={ attributes.titleTag || 'h2' } value={ attributes.title } onChange={ ( title ) => setAttributes( { title } ) } placeholder={ __( 'Write title...', 'mbn-theme' ) } className="mbn-image-box__title" style={ titleStyle } />
					<RichText tagName="div" value={ attributes.textarea } onChange={ ( textarea ) => setAttributes( { textarea } ) } placeholder={ __( 'Write content...', 'mbn-theme' ) } multiline="p" className="mbn-image-box__textarea" style={ contentStyle } />
					<div className="mbn-image-box__buttons">
						{ attributes.button1Text && <a href={ attributes.button1Url || '#' } target={ attributes.button1Target || '_self' } rel={ attributes.button1Target === '_blank' ? 'noopener noreferrer' : undefined } className={ `mbn-image-box__button btn-${ attributes.button1Style || 'primary' }` } style={ { ...baseButtonStyles( attributes.button1Style ), ...sharedButtonStyle } } onClick={ ( event ) => event.preventDefault() }>{ attributes.button1Text }</a> }
						{ attributes.button2Text && <a href={ attributes.button2Url || '#' } target={ attributes.button2Target || '_self' } rel={ attributes.button2Target === '_blank' ? 'noopener noreferrer' : undefined } className={ `mbn-image-box__button btn-${ attributes.button2Style || 'secondary' }` } style={ { ...baseButtonStyles( attributes.button2Style ), ...sharedButtonStyle } } onClick={ ( event ) => event.preventDefault() }>{ attributes.button2Text }</a> }
					</div>
				</div>
			</div>
		</Fragment>
	);
}
