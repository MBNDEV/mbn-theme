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
		className: 'box-border min-w-0 w-full rounded-sm border border-dashed border-gray-300 p-4',
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{
			className: 'mbn-column__content',
		},
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
