import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
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

  if (!logos || logos.length === 0) {
    return null;
  }

  const blockProps = useBlockProps.save({
    className: `logo-list display-${displayType} ${grayscale ? 'grayscale' : ''} ${grayscaleHover ? 'grayscale-hover' : ''}`,
  });

  const gridStyle = displayType === 'grid' ? {
    display: 'grid',
    gridTemplateColumns: `repeat(${gridColumns}, 1fr)`,
    gap: gridGap
  } : {};

  const sliderSettings = displayType === 'slider' ? {
    'data-autoplay': sliderAutoplay,
    'data-autoplay-speed': sliderAutoplaySpeed,
    'data-speed': sliderSpeed,
    'data-infinite': sliderInfinite,
    'data-dots': sliderDots,
    'data-arrows': sliderArrows,
    'data-slides-desktop': slidesDesktop,
    'data-slides-tablet': slidesTablet,
    'data-slides-mobile': slidesMobile,
  } : {};

  return (
    <div {...blockProps}>
      <div 
        className={`logo-list-container ${displayType === 'slider' ? 'logo-slider' : 'logo-grid'}`}
        style={gridStyle}
        {...sliderSettings}
        data-logo-height={logoHeight}
      >
        {logos.map((logo) => (
          <div key={logo.id} className="logo-list-item">
            <img 
              src={logo.url} 
              alt={logo.alt || ''}
              loading="lazy"
            />
          </div>
        ))}
      </div>
    </div>
  );
}
