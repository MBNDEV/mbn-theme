/**
 * Shared layout shell editor for inner-block container blocks.
 *
 * @package CustomTheme
 */

import { useEffect } from '@wordpress/element';
import { useBlockProps, useInnerBlocksProps, InnerBlocks } from '@wordpress/block-editor';
import LayoutControls from './LayoutControls';
import {
	getBlockElementId,
	getLayoutStyles,
	getScopedCustomCss,
} from './layout-helpers';

/**
 * @param {Object}        props
 * @param {Object}        props.attributes
 * @param {Function}      props.setAttributes
 * @param {string}        props.clientId
 * @param {string}        props.blockSlug
 * @param {string}        props.wrapperClassName
 * @param {string}        props.contentClassName
 * @param {string}        props.innerBlocksClassName Optional nested inner-blocks class.
 * @param {Object}        props.innerBlocksOptions   Optional inner blocks config overrides.
 * @param {JSX.Element}   props.innerContent         Optional custom content instead of inner blocks.
 * @return {JSX.Element} Layout shell editor.
 */
export default function LayoutShellEdit( {
	attributes,
	setAttributes,
	clientId,
	blockSlug,
	wrapperClassName,
	contentClassName,
	innerBlocksClassName = '',
	innerBlocksOptions = {},
	innerContent = null,
} ) {
	const {
		backgroundImageUrl,
		backgroundVideoUrl,
		overlayColor,
		overlayOpacity,
		customCss,
	} = attributes;

	const elementId = getBlockElementId( attributes, blockSlug );
	const layout = getLayoutStyles( attributes );
	const scopedCss = getScopedCustomCss( elementId, customCss );
	const hasNestedInnerBlocks = Boolean( innerBlocksClassName );
	const usesInnerBlocks = innerContent === null;

	useEffect( () => {
		if ( ! attributes.blockInstanceId ) {
			setAttributes( {
				blockInstanceId: `mbn-${ blockSlug }-${ clientId.replace( /-/g, '' ).slice( 0, 8 ) }`,
			} );
		}
	}, [ attributes.blockInstanceId, blockSlug, clientId, setAttributes ] );

	const blockProps = useBlockProps( {
		id: elementId,
		className: wrapperClassName,
		style: layout.style,
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{
			className: hasNestedInnerBlocks ? innerBlocksClassName : contentClassName,
		},
		{
			renderAppender: InnerBlocks.ButtonBlockAppender,
			...innerBlocksOptions,
		}
	);

	const hasOverlay = overlayOpacity > 0 && overlayColor;

	const renderContentArea = () => {
		if ( innerContent ) {
			return <div className={ contentClassName }>{ innerContent }</div>;
		}

		if ( hasNestedInnerBlocks ) {
			return (
				<div className={ contentClassName }>
					<div { ...innerBlocksProps } />
				</div>
			);
		}

		if ( usesInnerBlocks ) {
			return <div { ...innerBlocksProps } />;
		}

		return <div className={ contentClassName } />;
	};

	return (
		<>
			<LayoutControls attributes={ attributes } setAttributes={ setAttributes } />

			{ scopedCss && <style>{ scopedCss }</style> }

			<div { ...blockProps }>
				{ backgroundVideoUrl && (
					<video
						className="mbn-layout__video pointer-events-none absolute inset-0 z-0 h-full w-full object-cover"
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
						className="mbn-layout__image absolute inset-0 z-0 bg-cover bg-center bg-no-repeat"
						style={ { backgroundImage: `url(${ backgroundImageUrl })` } }
						aria-hidden="true"
					/>
				) }

				{ hasOverlay && (
					<div
						className="mbn-layout__overlay absolute inset-0 z-[1]"
						style={ {
							backgroundColor: overlayColor,
							opacity: overlayOpacity / 100,
						} }
						aria-hidden="true"
					/>
				) }

				{ renderContentArea() }
			</div>
		</>
	);
}
