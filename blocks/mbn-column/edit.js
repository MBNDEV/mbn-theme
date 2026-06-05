/**
 * MBN Column child block editor component.
 *
 * @package CustomTheme
 */

import { useBlockProps, useInnerBlocksProps, InnerBlocks } from '@wordpress/block-editor';
import { COLUMN_CONTENT_CLASSES, COLUMN_WRAPPER_CLASSES } from '../shared/use-layout-styles';

/**
 * @return {JSX.Element} MBN Column block editor.
 */
export default function Edit() {
	const blockProps = useBlockProps( {
		className: COLUMN_WRAPPER_CLASSES,
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{
			className: COLUMN_CONTENT_CLASSES,
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
