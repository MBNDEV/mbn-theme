import { useBlockProps, useInnerBlocksProps, InnerBlocks } from '@wordpress/block-editor';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'mbn-column w-full' } );
	const innerBlocksProps = useInnerBlocksProps(
		{},
		{ renderAppender: InnerBlocks.ButtonBlockAppender }
	);

	return (
		<div { ...blockProps }>
			<div { ...innerBlocksProps } />
		</div>
	);
}
