import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
  const { minHeight } = attributes;

  const blockProps = useBlockProps.save({
    className: 'slider-item',
    style: {
      minHeight: minHeight || undefined
    }
  });

  const innerBlocksProps = useInnerBlocksProps.save({
    className: 'slider-item-content'
  });

  return (
    <div {...blockProps}>
      <div {...innerBlocksProps} />
    </div>
  );
}
