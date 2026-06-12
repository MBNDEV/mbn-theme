import { 
  useBlockProps, 
  InspectorControls,
  InnerBlocks,
  useInnerBlocksProps
} from '@wordpress/block-editor';
import { 
  PanelBody, 
  SelectControl, 
  RangeControl,
  ToggleControl,
  TextControl,
  __experimentalVStack as VStack 
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ALLOWED_BLOCKS = ['mbn-theme/slider-item']; // Only allow slider-item blocks
const TEMPLATE = [
  ['mbn-theme/slider-item', {}],
  ['mbn-theme/slider-item', {}],
  ['mbn-theme/slider-item', {}]
];

export default function Edit({ attributes, setAttributes, clientId }) {
  const {
    displayType,
    gridColumns,
    gridGap,
    sliderAutoplay,
    sliderAutoplaySpeed,
    sliderSpeed,
    sliderInfinite,
    sliderDots,
    sliderArrows,
    sliderFade,
    sliderCenterMode,
    slidesDesktop,
    slidesTablet,
    slidesMobile,
    itemMinHeight
  } = attributes;

  const blockProps = useBlockProps({
    className: `mbn-slider display-${displayType}`,
  });

  // Always display as grid in editor for easier management
  const innerBlocksProps = useInnerBlocksProps(
    {
      className: `mbn-slider-items items-grid`,
      style: {
        display: 'grid',
        gridTemplateColumns: `repeat(${displayType === 'grid' ? gridColumns : slidesDesktop}, 1fr)`,
        gap: gridGap
      }
    },
    {
      allowedBlocks: ALLOWED_BLOCKS,
      template: TEMPLATE,
      renderAppender: InnerBlocks.ButtonBlockAppender
    }
  );

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Display Settings', 'mbn-theme')} initialOpen={true}>
          <SelectControl
            label={__('Display Type', 'mbn-theme')}
            value={displayType}
            options={[
              { label: __('Slider', 'mbn-theme'), value: 'slider' },
              { label: __('Grid', 'mbn-theme'), value: 'grid' }
            ]}
            onChange={(value) => setAttributes({ displayType: value })}
          />

          <TextControl
            label={__('Item Min Height', 'mbn-theme')}
            value={itemMinHeight}
            onChange={(value) => setAttributes({ itemMinHeight: value })}
            help={__('Set minimum height for items (e.g., 400px, 50vh)', 'mbn-theme')}
          />
        </PanelBody>

        {displayType === 'grid' && (
          <PanelBody title={__('Grid Settings', 'mbn-theme')} initialOpen={true}>
            <RangeControl
              label={__('Columns', 'mbn-theme')}
              value={gridColumns}
              onChange={(value) => setAttributes({ gridColumns: value })}
              min={1}
              max={6}
            />

            <TextControl
              label={__('Grid Gap', 'mbn-theme')}
              value={gridGap}
              onChange={(value) => setAttributes({ gridGap: value })}
              help={__('Set gap between items (e.g., 1rem, 20px)', 'mbn-theme')}
            />
          </PanelBody>
        )}

        {displayType === 'slider' && (
          <>
            <PanelBody title={__('Slider Settings', 'mbn-theme')} initialOpen={true}>
              <VStack spacing={3}>
                <ToggleControl
                  label={__('Autoplay', 'mbn-theme')}
                  checked={sliderAutoplay}
                  onChange={(value) => setAttributes({ sliderAutoplay: value })}
                />

                {sliderAutoplay && (
                  <RangeControl
                    label={__('Autoplay Speed (ms)', 'mbn-theme')}
                    value={sliderAutoplaySpeed}
                    onChange={(value) => setAttributes({ sliderAutoplaySpeed: value })}
                    min={1000}
                    max={10000}
                    step={500}
                  />
                )}

                <RangeControl
                  label={__('Transition Speed (ms)', 'mbn-theme')}
                  value={sliderSpeed}
                  onChange={(value) => setAttributes({ sliderSpeed: value })}
                  min={100}
                  max={2000}
                  step={100}
                />

                <ToggleControl
                  label={__('Infinite Loop', 'mbn-theme')}
                  checked={sliderInfinite}
                  onChange={(value) => setAttributes({ sliderInfinite: value })}
                />

                <ToggleControl
                  label={__('Show Dots', 'mbn-theme')}
                  checked={sliderDots}
                  onChange={(value) => setAttributes({ sliderDots: value })}
                />

                <ToggleControl
                  label={__('Show Arrows', 'mbn-theme')}
                  checked={sliderArrows}
                  onChange={(value) => setAttributes({ sliderArrows: value })}
                />

                <ToggleControl
                  label={__('Fade Effect', 'mbn-theme')}
                  checked={sliderFade}
                  onChange={(value) => setAttributes({ sliderFade: value })}
                  help={__('Use fade instead of slide', 'mbn-theme')}
                />

                <ToggleControl
                  label={__('Center Mode', 'mbn-theme')}
                  checked={sliderCenterMode}
                  onChange={(value) => setAttributes({ sliderCenterMode: value })}
                  help={__('Center current slide', 'mbn-theme')}
                />
              </VStack>
            </PanelBody>

            <PanelBody title={__('Responsive Settings', 'mbn-theme')} initialOpen={true}>
              <VStack spacing={3}>
                <RangeControl
                  label={__('Slides - Desktop (>1024px)', 'mbn-theme')}
                  value={slidesDesktop}
                  onChange={(value) => setAttributes({ slidesDesktop: value })}
                  min={1}
                  max={6}
                />

                <RangeControl
                  label={__('Slides - Tablet (768-1024px)', 'mbn-theme')}
                  value={slidesTablet}
                  onChange={(value) => setAttributes({ slidesTablet: value })}
                  min={1}
                  max={4}
                />

                <RangeControl
                  label={__('Slides - Mobile (<768px)', 'mbn-theme')}
                  value={slidesMobile}
                  onChange={(value) => setAttributes({ slidesMobile: value })}
                  min={1}
                  max={3}
                />
              </VStack>
            </PanelBody>
          </>
        )}
      </InspectorControls>

      <div {...blockProps}>
        <div {...innerBlocksProps} />
      </div>
    </>
  );
}
