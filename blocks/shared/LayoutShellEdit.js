/**
 * Shared layout shell editor for inner-block container blocks.
 *
 * @package CustomTheme
 */

import { useEffect } from '@wordpress/element';
import { useBlockProps, useInnerBlocksProps, InnerBlocks } from '@wordpress/block-editor';
import LayoutControls from './LayoutControls';
import { getBlockElementId, getLayoutStyles } from './use-layout-styles';

/**
 * @param {Object}   props
 * @param {Object}   props.attributes
 * @param {Function} props.setAttributes
 * @param {string}   props.clientId
 * @param {string}   props.blockSlug
 * @param {string}   props.wrapperClassName
 * @param {string}   props.contentClassName
 * @return {JSX.Element} Layout shell editor.
 */
export default function LayoutShellEdit( {
	attributes,
	setAttributes,
	clientId,
	blockSlug,
	wrapperClassName,
	contentClassName,
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
			className: contentClassName,
		},
		{
			renderAppender: InnerBlocks.ButtonBlockAppender,
		}
	);

	const hasOverlay = overlayOpacity > 0 && overlayColor;

	return (
		<>
			<LayoutControls attributes={ attributes } setAttributes={ setAttributes } />

			{ customCss && (
				<style>
					{ `#${ elementId }{${ customCss }}` }
				</style>
			) }

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

				<div { ...innerBlocksProps } />
			</div>
		</>
	);
}
