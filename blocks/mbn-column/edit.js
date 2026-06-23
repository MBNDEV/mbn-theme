/**
 * MBN Column child block editor component.
 *
 * @package CustomTheme
 */

import { useBlockProps, useInnerBlocksProps, InnerBlocks } from '@wordpress/block-editor';

/**
 * @return {JSX.Element} MBN Column block editor.
 */
export default function Edit() {
	const blockProps = useBlockProps( {
		className: 'mbn-column w-full',
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{},
		{
			renderAppender: InnerBlocks.ButtonBlockAppender,
		}
	);

	return (
		<div { ...blockProps }>
			<div { ...innerBlocksProps } />
		</div>
	);
}
