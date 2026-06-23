import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

function shadowFromPreset( shadow, custom ) {
	if ( shadow === 'custom' ) return custom || '';
	if ( shadow === 'sm' ) return '0 1px 2px rgba(0,0,0,.1)';
	if ( shadow === 'md' ) return '0 4px 10px rgba(0,0,0,.15)';
	if ( shadow === 'lg' ) return '0 12px 30px rgba(0,0,0,.2)';
	return '';
}

export default function save( { attributes } ) {
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

	const blockProps = useBlockProps.save( {
		className: 'mbn-section',
		style: sectionStyle,
	} );

	return (
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
				<InnerBlocks.Content />
			</div>
		</div>
	);
}
