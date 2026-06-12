import { 
  useBlockProps, 
  InspectorControls,
  MediaUpload,
  MediaUploadCheck 
} from '@wordpress/block-editor';
import { 
  PanelBody, 
  SelectControl, 
  Button,
  RangeControl,
  ToggleControl,
  TextControl,
  __experimentalVStack as VStack 
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  const {
    logos,
    displayType,
    gridColumns,
    gridGap,
    sliderAutoplay,
    sliderAutoplaySpeed,
    sliderSpeed,
    sliderInfinite,
    sliderDots,
    sliderArrows,
    slidesDesktop,
    slidesTablet,
    slidesMobile,
    logoHeight,
    grayscale,
    grayscaleHover
  } = attributes;

  const blockProps = useBlockProps({
    className: `logo-list display-${displayType}`,
  });

  const handleSelectImages = (images) => {
    const newLogos = images.map(img => ({
      id: img.id,
      url: img.url,
      alt: img.alt || ''
    }));
    setAttributes({ logos: newLogos });
  };

  const handleRemoveLogo = (index) => {
    const newLogos = [...logos];
    newLogos.splice(index, 1);
    setAttributes({ logos: newLogos });
  };

  const gridStyle = displayType === 'grid' ? {
    display: 'grid',
    gridTemplateColumns: `repeat(${gridColumns}, 1fr)`,
    gap: gridGap
  } : {};

  const logoStyle = {
    height: logoHeight,
    filter: grayscale ? 'grayscale(100%)' : 'none'
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Display Settings', 'mbn-theme')} initialOpen={true}>
          <SelectControl
            label={__('Display Type', 'mbn-theme')}
            value={displayType}
            options={[
              { label: __('Grid', 'mbn-theme'), value: 'grid' },
              { label: __('Slider', 'mbn-theme'), value: 'slider' }
            ]}
            onChange={(value) => setAttributes({ displayType: value })}
          />

          <TextControl
            label={__('Logo Height', 'mbn-theme')}
            value={logoHeight}
            onChange={(value) => setAttributes({ logoHeight: value })}
            help={__('Set logo height (e.g., 80px, 100px)', 'mbn-theme')}
          />

          <ToggleControl
            label={__('Grayscale Logos', 'mbn-theme')}
            checked={grayscale}
            onChange={(value) => setAttributes({ grayscale: value })}
          />

          <ToggleControl
            label={__('Color on Hover', 'mbn-theme')}
            checked={grayscaleHover}
            onChange={(value) => setAttributes({ grayscaleHover: value })}
            help={__('Remove grayscale on hover', 'mbn-theme')}
          />
        </PanelBody>

        {displayType === 'grid' && (
          <PanelBody title={__('Grid Settings', 'mbn-theme')} initialOpen={true}>
            <RangeControl
              label={__('Columns', 'mbn-theme')}
              value={gridColumns}
              onChange={(value) => setAttributes({ gridColumns: value })}
              min={2}
              max={8}
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
              </VStack>
            </PanelBody>

            <PanelBody title={__('Responsive Settings', 'mbn-theme')} initialOpen={true}>
              <VStack spacing={3}>
                <RangeControl
                  label={__('Slides - Desktop (>1024px)', 'mbn-theme')}
                  value={slidesDesktop}
                  onChange={(value) => setAttributes({ slidesDesktop: value })}
                  min={1}
                  max={8}
                />

                <RangeControl
                  label={__('Slides - Tablet (768-1024px)', 'mbn-theme')}
                  value={slidesTablet}
                  onChange={(value) => setAttributes({ slidesTablet: value })}
                  min={1}
                  max={6}
                />

                <RangeControl
                  label={__('Slides - Mobile (<768px)', 'mbn-theme')}
                  value={slidesMobile}
                  onChange={(value) => setAttributes({ slidesMobile: value })}
                  min={1}
                  max={4}
                />
              </VStack>
            </PanelBody>
          </>
        )}
      </InspectorControls>

      <div {...blockProps}>
        <div className="logo-list-controls">
          <MediaUploadCheck>
            <MediaUpload
              onSelect={handleSelectImages}
              allowedTypes={['image']}
              multiple
              gallery
              value={logos.map(logo => logo.id)}
              render={({ open }) => (
                <Button onClick={open} variant="primary">
                  {logos.length === 0 
                    ? __('Add Logos', 'mbn-theme')
                    : __('Edit Gallery', 'mbn-theme')
                  }
                </Button>
              )}
            />
          </MediaUploadCheck>
        </div>

        {logos.length > 0 && (
          <div className="logo-list-grid" style={gridStyle}>
            {logos.map((logo, index) => (
              <div key={logo.id} className="logo-list-item">
                <img 
                  src={logo.url} 
                  alt={logo.alt}
                  style={logoStyle}
                />
                <Button
                  onClick={() => handleRemoveLogo(index)}
                  variant="secondary"
                  isDestructive
                  className="logo-remove-button"
                  isSmall
                >
                  {__('Remove', 'mbn-theme')}
                </Button>
              </div>
            ))}
          </div>
        )}

        {logos.length === 0 && (
          <div className="logo-list-placeholder">
            <p>{__('Click "Add Logos" to get started', 'mbn-theme')}</p>
          </div>
        )}
      </div>
    </>
  );
}
