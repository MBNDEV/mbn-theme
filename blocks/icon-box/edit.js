import {
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
	ToggleControl,
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
			setAttributes( { blockId: `icon-box-${ clientId.slice( 0, 8 ) }` } );
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
		backgroundColor: attributes.containerBgType === 'color' ? attributes.containerBgColor || undefined : undefined,
		background: attributes.containerBgType === 'gradient' ? attributes.containerBgGradient || undefined : undefined,
		backgroundImage: attributes.containerBgType === 'image' && attributes.containerBgImageUrl ? `url(${ attributes.containerBgImageUrl })` : undefined,
		backgroundSize: attributes.containerBgType === 'image' ? attributes.containerBgImageSize || undefined : undefined,
		backgroundPosition: attributes.containerBgType === 'image' ? attributes.containerBgImagePosition || undefined : undefined,
		borderRadius: attributes.containerBorderRadius || undefined,
		borderStyle: attributes.containerBorderStyle !== 'none' ? attributes.containerBorderStyle || undefined : undefined,
		borderWidth: attributes.containerBorderWidth || undefined,
		borderColor: attributes.containerBorderColor || undefined,
		boxShadow: shadowFromPreset( attributes.containerShadow, attributes.containerShadowCustom ) || undefined,
	};

	const iconStyle = {
		backgroundColor: attributes.iconBgColor || undefined,
		borderStyle: attributes.iconBorderStyle !== 'none' ? attributes.iconBorderStyle || undefined : undefined,
		borderWidth: attributes.iconBorderWidth || undefined,
		borderColor: attributes.iconBorderColor || undefined,
		borderRadius:
			attributes.iconShape === 'circle'
				? '999px'
				: attributes.iconShape === 'square'
				? '0'
				: attributes.iconBorderRadius || undefined,
		paddingTop: attributes.iconPaddingTop || undefined,
		paddingRight: attributes.iconPaddingRight || undefined,
		paddingBottom: attributes.iconPaddingBottom || undefined,
		paddingLeft: attributes.iconPaddingLeft || undefined,
		marginTop: attributes.iconMarginTop || undefined,
		marginRight: attributes.iconMarginRight || undefined,
		marginBottom: attributes.iconMarginBottom || undefined,
		marginLeft: attributes.iconMarginLeft || undefined,
		width: attributes.iconWidth || undefined,
		display: 'inline-flex',
		alignItems: 'center',
		justifyContent: 'center',
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

	const descriptionStyle = {
		maxWidth: attributes.descriptionMaxWidth || undefined,
		marginTop: attributes.descriptionMarginTop || undefined,
		marginRight: attributes.descriptionMarginRight || undefined,
		marginBottom: attributes.descriptionMarginBottom || undefined,
		marginLeft: attributes.descriptionMarginLeft || undefined,
		paddingTop: attributes.descriptionPaddingTop || undefined,
		paddingRight: attributes.descriptionPaddingRight || undefined,
		paddingBottom: attributes.descriptionPaddingBottom || undefined,
		paddingLeft: attributes.descriptionPaddingLeft || undefined,
		fontFamily: attributes.descriptionFontFamily || undefined,
		fontSize: attributes.descriptionFontSize || undefined,
		fontWeight: attributes.descriptionFontWeight || undefined,
		lineHeight: attributes.descriptionLineHeight || undefined,
		letterSpacing: attributes.descriptionLetterSpacing || undefined,
		color: attributes.descriptionColor || undefined,
		textAlign: attributes.descriptionAlign || undefined,
	};

	const buttonStyle = {
		...baseButtonStyles( attributes.buttonStyle ),
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
		borderStyle: attributes.buttonBorderStyle !== 'none' ? attributes.buttonBorderStyle || undefined : undefined,
		borderWidth: attributes.buttonBorderWidth || undefined,
		borderColor: attributes.buttonBorderColor || undefined,
		borderRadius: attributes.buttonBorderRadius || undefined,
		display: 'inline-flex',
		alignItems: 'center',
		justifyContent: 'center',
		textDecoration: 'none',
	};

	const contentAlign = attributes.contentPosition || 'center';
	const blockClass = attributes.blockId || '';
	const blockProps = useBlockProps( {
		className: `mbn-icon-box is-pos-${ contentAlign } ${ blockClass }`.trim(),
		style: wrapperStyle,
	} );

	const contentClass = `mbn-icon-box__content has-align-${ contentAlign }`;
	const editorCustomCss = attributes.customCss && blockClass
		? attributes.customCss.replaceAll( '{{WRAPPER}}', `.${ blockClass }` )
		: '';

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={ __( 'Box Settings', 'mbn-theme' ) } initialOpen={ true }>
					<SelectControl label={ __( 'Alignment', 'mbn-theme' ) } value={ attributes.contentPosition } onChange={ ( contentPosition ) => setAttributes( { contentPosition } ) } options={ ALIGN_OPTIONS } />
					<SelectControl label={ __( 'Background Type', 'mbn-theme' ) } value={ attributes.containerBgType } onChange={ ( containerBgType ) => setAttributes( { containerBgType } ) } options={ [ { label: __( 'Color', 'mbn-theme' ), value: 'color' }, { label: __( 'Gradient', 'mbn-theme' ), value: 'gradient' }, { label: __( 'Image', 'mbn-theme' ), value: 'image' } ] } />
					{ attributes.containerBgType === 'gradient' && <TextControl label={ __( 'Background Gradient', 'mbn-theme' ) } value={ attributes.containerBgGradient } onChange={ ( containerBgGradient ) => setAttributes( { containerBgGradient } ) } /> }
					{ attributes.containerBgType === 'color' && <TextControl label={ __( 'Background Color', 'mbn-theme' ) } type="color" value={ attributes.containerBgColor } onChange={ ( containerBgColor ) => setAttributes( { containerBgColor } ) } /> }
					{ attributes.containerBgType === 'image' && (
						<Fragment>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => setAttributes( { containerBgImageUrl: media?.url || '', containerBgImageId: media?.id || 0 } ) }
									allowedTypes={ [ 'image' ] }
									value={ attributes.containerBgImageId }
									render={ ( { open } ) => <Button onClick={ open } variant="secondary">{ attributes.containerBgImageUrl ? __( 'Replace Background Image', 'mbn-theme' ) : __( 'Select Background Image', 'mbn-theme' ) }</Button> }
								/>
							</MediaUploadCheck>
							<SelectControl label={ __( 'Background Size', 'mbn-theme' ) } value={ attributes.containerBgImageSize } onChange={ ( containerBgImageSize ) => setAttributes( { containerBgImageSize } ) } options={ [ { label: 'cover', value: 'cover' }, { label: 'contain', value: 'contain' }, { label: 'auto', value: 'auto' } ] } />
							<TextControl label={ __( 'Background Position', 'mbn-theme' ) } value={ attributes.containerBgImagePosition } onChange={ ( containerBgImagePosition ) => setAttributes( { containerBgImagePosition } ) } placeholder="center center" />
						</Fragment>
					) }
					<SelectControl label={ __( 'Shadow', 'mbn-theme' ) } value={ attributes.containerShadow } onChange={ ( containerShadow ) => setAttributes( { containerShadow } ) } options={ [ { label: __( 'None', 'mbn-theme' ), value: 'none' }, { label: __( 'Small', 'mbn-theme' ), value: 'sm' }, { label: __( 'Medium', 'mbn-theme' ), value: 'md' }, { label: __( 'Large', 'mbn-theme' ), value: 'lg' }, { label: __( 'Custom', 'mbn-theme' ), value: 'custom' } ] } />
					{ attributes.containerShadow === 'custom' && <TextControl label={ __( 'Custom Shadow', 'mbn-theme' ) } value={ attributes.containerShadowCustom } onChange={ ( containerShadowCustom ) => setAttributes( { containerShadowCustom } ) } /> }
					<SelectControl label={ __( 'Border Style', 'mbn-theme' ) } value={ attributes.containerBorderStyle } onChange={ ( containerBorderStyle ) => setAttributes( { containerBorderStyle } ) } options={ [ { label: __( 'None', 'mbn-theme' ), value: 'none' }, { label: __( 'Solid', 'mbn-theme' ), value: 'solid' }, { label: __( 'Dashed', 'mbn-theme' ), value: 'dashed' }, { label: __( 'Dotted', 'mbn-theme' ), value: 'dotted' } ] } />
					<UnitControl label={ __( 'Border Width', 'mbn-theme' ) } value={ attributes.containerBorderWidth } onChange={ ( containerBorderWidth ) => setAttributes( { containerBorderWidth } ) } />
					<TextControl label={ __( 'Border Color', 'mbn-theme' ) } type="color" value={ attributes.containerBorderColor } onChange={ ( containerBorderColor ) => setAttributes( { containerBorderColor } ) } />
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

				<PanelBody title={ __( 'Icon Settings', 'mbn-theme' ) } initialOpen={ false }>
					<SelectControl label={ __( 'Icon Source', 'mbn-theme' ) } value={ attributes.iconType } onChange={ ( iconType ) => setAttributes( { iconType } ) } options={ [ { label: __( 'Image', 'mbn-theme' ), value: 'image' }, { label: __( 'SVG Code', 'mbn-theme' ), value: 'svg' } ] } />
					{ attributes.iconType === 'image' ? (
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) => setAttributes( { iconImageUrl: media?.url || '', iconImageId: media?.id || 0, iconImageAlt: media?.alt || attributes.iconImageAlt } ) }
								allowedTypes={ [ 'image' ] }
								value={ attributes.iconImageId }
								render={ ( { open } ) => <Button onClick={ open } variant="secondary">{ attributes.iconImageUrl ? __( 'Replace Icon Image', 'mbn-theme' ) : __( 'Select Icon Image', 'mbn-theme' ) }</Button> }
							/>
						</MediaUploadCheck>
					) : (
						<TextControl label={ __( 'SVG Code', 'mbn-theme' ) } value={ attributes.iconSvgCode } onChange={ ( iconSvgCode ) => setAttributes( { iconSvgCode } ) } />
					) }
					{ attributes.iconImageUrl && <img src={ attributes.iconImageUrl } alt={ attributes.iconImageAlt || '' } style={ { width: '80px', height: 'auto', marginTop: '8px' } } /> }
					<TextControl label={ __( 'Icon Alt', 'mbn-theme' ) } value={ attributes.iconImageAlt } onChange={ ( iconImageAlt ) => setAttributes( { iconImageAlt } ) } />
					<TextControl label={ __( 'Background Color', 'mbn-theme' ) } type="color" value={ attributes.iconBgColor } onChange={ ( iconBgColor ) => setAttributes( { iconBgColor } ) } />
					<SelectControl label={ __( 'Shape', 'mbn-theme' ) } value={ attributes.iconShape } onChange={ ( iconShape ) => setAttributes( { iconShape } ) } options={ [ { label: __( 'None', 'mbn-theme' ), value: 'none' }, { label: __( 'Circle', 'mbn-theme' ), value: 'circle' }, { label: __( 'Square', 'mbn-theme' ), value: 'square' } ] } />
					<SelectControl label={ __( 'Border Style', 'mbn-theme' ) } value={ attributes.iconBorderStyle } onChange={ ( iconBorderStyle ) => setAttributes( { iconBorderStyle } ) } options={ [ { label: __( 'None', 'mbn-theme' ), value: 'none' }, { label: __( 'Solid', 'mbn-theme' ), value: 'solid' }, { label: __( 'Dashed', 'mbn-theme' ), value: 'dashed' }, { label: __( 'Dotted', 'mbn-theme' ), value: 'dotted' } ] } />
					<UnitControl label={ __( 'Border Width', 'mbn-theme' ) } value={ attributes.iconBorderWidth } onChange={ ( iconBorderWidth ) => setAttributes( { iconBorderWidth } ) } />
					<TextControl label={ __( 'Border Color', 'mbn-theme' ) } type="color" value={ attributes.iconBorderColor } onChange={ ( iconBorderColor ) => setAttributes( { iconBorderColor } ) } />
					<UnitControl label={ __( 'Border Radius', 'mbn-theme' ) } value={ attributes.iconBorderRadius } onChange={ ( iconBorderRadius ) => setAttributes( { iconBorderRadius } ) } />
					<BaseControl label={ __( 'Padding', 'mbn-theme' ) }>
						<BoxControl values={ { top: attributes.iconPaddingTop, right: attributes.iconPaddingRight, bottom: attributes.iconPaddingBottom, left: attributes.iconPaddingLeft } } onChange={ ( next ) => setAttributes( { iconPaddingTop: next?.top || '', iconPaddingRight: next?.right || '', iconPaddingBottom: next?.bottom || '', iconPaddingLeft: next?.left || '' } ) } />
					</BaseControl>
					<BaseControl label={ __( 'Margin', 'mbn-theme' ) }>
						<BoxControl values={ { top: attributes.iconMarginTop, right: attributes.iconMarginRight, bottom: attributes.iconMarginBottom, left: attributes.iconMarginLeft } } onChange={ ( next ) => setAttributes( { iconMarginTop: next?.top || '', iconMarginRight: next?.right || '', iconMarginBottom: next?.bottom || '', iconMarginLeft: next?.left || '' } ) } />
					</BaseControl>
					<UnitControl label={ __( 'Width', 'mbn-theme' ) } value={ attributes.iconWidth } onChange={ ( iconWidth ) => setAttributes( { iconWidth } ) } />
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
						<BoxControl values={ { top: attributes.descriptionMarginTop, right: attributes.descriptionMarginRight, bottom: attributes.descriptionMarginBottom, left: attributes.descriptionMarginLeft } } onChange={ ( next ) => setAttributes( { descriptionMarginTop: next?.top || '', descriptionMarginRight: next?.right || '', descriptionMarginBottom: next?.bottom || '', descriptionMarginLeft: next?.left || '' } ) } />
					</BaseControl>
					<BaseControl label={ __( 'Padding', 'mbn-theme' ) }>
						<BoxControl values={ { top: attributes.descriptionPaddingTop, right: attributes.descriptionPaddingRight, bottom: attributes.descriptionPaddingBottom, left: attributes.descriptionPaddingLeft } } onChange={ ( next ) => setAttributes( { descriptionPaddingTop: next?.top || '', descriptionPaddingRight: next?.right || '', descriptionPaddingBottom: next?.bottom || '', descriptionPaddingLeft: next?.left || '' } ) } />
					</BaseControl>
					<UnitControl label={ __( 'Max Width', 'mbn-theme' ) } value={ attributes.descriptionMaxWidth } onChange={ ( descriptionMaxWidth ) => setAttributes( { descriptionMaxWidth } ) } />
					<TextControl label={ __( 'Font Family', 'mbn-theme' ) } value={ attributes.descriptionFontFamily } onChange={ ( descriptionFontFamily ) => setAttributes( { descriptionFontFamily } ) } />
					<UnitControl label={ __( 'Font Size', 'mbn-theme' ) } value={ attributes.descriptionFontSize } onChange={ ( descriptionFontSize ) => setAttributes( { descriptionFontSize } ) } />
					<TextControl label={ __( 'Font Weight', 'mbn-theme' ) } value={ attributes.descriptionFontWeight } onChange={ ( descriptionFontWeight ) => setAttributes( { descriptionFontWeight } ) } />
					<UnitControl label={ __( 'Line Height', 'mbn-theme' ) } value={ attributes.descriptionLineHeight } onChange={ ( descriptionLineHeight ) => setAttributes( { descriptionLineHeight } ) } />
					<UnitControl label={ __( 'Letter Spacing', 'mbn-theme' ) } value={ attributes.descriptionLetterSpacing } onChange={ ( descriptionLetterSpacing ) => setAttributes( { descriptionLetterSpacing } ) } />
					<TextControl label={ __( 'Color', 'mbn-theme' ) } type="color" value={ attributes.descriptionColor } onChange={ ( descriptionColor ) => setAttributes( { descriptionColor } ) } />
					<SelectControl label={ __( 'Align', 'mbn-theme' ) } value={ attributes.descriptionAlign } onChange={ ( descriptionAlign ) => setAttributes( { descriptionAlign } ) } options={ [ { label: __( 'Default', 'mbn-theme' ), value: '' }, ...ALIGN_OPTIONS ] } />
				</PanelBody>

				<PanelBody title={ __( 'Button Settings', 'mbn-theme' ) } initialOpen={ false }>
					<TextControl label={ __( 'Button Text', 'mbn-theme' ) } value={ attributes.buttonText } onChange={ ( buttonText ) => setAttributes( { buttonText } ) } />
					<URLInputButton label={ __( 'Button Link', 'mbn-theme' ) } url={ attributes.buttonUrl } onChange={ ( buttonUrl ) => setAttributes( { buttonUrl } ) } />
					<ToggleControl label={ __( 'Open in New Tab', 'mbn-theme' ) } checked={ attributes.buttonTarget === '_blank' } onChange={ ( open ) => setAttributes( { buttonTarget: open ? '_blank' : '_self' } ) } />
					<SelectControl label={ __( 'Button Style', 'mbn-theme' ) } value={ attributes.buttonStyle } onChange={ ( buttonStyle ) => setAttributes( { buttonStyle } ) } options={ BUTTON_VARIANTS } />
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
				</PanelBody>
			</InspectorControls>

			{ editorCustomCss && <style>{ editorCustomCss }</style> }

			<div { ...blockProps }>
				<div className={ contentClass }>
					{ ( attributes.iconImageUrl || attributes.iconSvgCode ) && (
						<div className="mbn-icon-box__icon" style={ iconStyle }>
							{ attributes.iconType === 'image' && attributes.iconImageUrl && <img src={ attributes.iconImageUrl } alt={ attributes.iconImageAlt || '' } /> }
							{ attributes.iconType === 'svg' && attributes.iconSvgCode && <span dangerouslySetInnerHTML={ { __html: attributes.iconSvgCode } } /> }
						</div>
					) }
					<RichText tagName={ attributes.titleTag || 'h3' } value={ attributes.title } onChange={ ( title ) => setAttributes( { title } ) } placeholder={ __( 'Write title...', 'mbn-theme' ) } className="mbn-icon-box__title" style={ titleStyle } />
					<RichText tagName="div" value={ attributes.description } onChange={ ( description ) => setAttributes( { description } ) } placeholder={ __( 'Write description...', 'mbn-theme' ) } multiline="p" className="mbn-icon-box__description" style={ descriptionStyle } />
					{ attributes.buttonText && <a href={ attributes.buttonUrl || '#' } target={ attributes.buttonTarget || '_self' } rel={ attributes.buttonTarget === '_blank' ? 'noopener noreferrer' : undefined } className={ `mbn-icon-box__button btn-${ attributes.buttonStyle || 'primary' }` } style={ buttonStyle } onClick={ ( e ) => e.preventDefault() }>{ attributes.buttonText }</a> }
				</div>
			</div>
		</Fragment>
	);
}
