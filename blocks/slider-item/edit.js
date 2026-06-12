import { 
  useBlockProps, 
  InnerBlocks,
  useInnerBlocksProps,
  InspectorControls
} from '@wordpress/block-editor';
import { 
  PanelBody, 
  TextControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  const { minHeight } = attributes;

  const blockProps = useBlockProps({
    className: 'slider-item',
    style: {
      minHeight: minHeight || undefined
    }
  });

  const innerBlocksProps = useInnerBlocksProps(
    {
      className: 'slider-item-content'
    },
    {
      template: [
        ['core/paragraph', { 
          placeholder: __('Add content to this slider item...', 'mbn-theme')
        }]
      ],
      templateLock: false
    }
  );

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Item Settings', 'mbn-theme')} initialOpen={true}>
          <TextControl
            label={__('Min Height', 'mbn-theme')}
            value={minHeight}
            onChange={(value) => setAttributes({ minHeight: value })}
            help={__('Set minimum height (e.g., 400px, 50vh)', 'mbn-theme')}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div {...innerBlocksProps} />
      </div>
    </>
  );
}
