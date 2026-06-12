import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
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

  const blockProps = useBlockProps.save({
    className: `mbn-slider display-${displayType}`,
  });

  const sliderSettings = displayType === 'slider' ? {
    'data-autoplay': sliderAutoplay,
    'data-autoplay-speed': sliderAutoplaySpeed,
    'data-speed': sliderSpeed,
    'data-infinite': sliderInfinite,
    'data-dots': sliderDots,
    'data-arrows': sliderArrows,
    'data-fade': sliderFade,
    'data-center-mode': sliderCenterMode,
    'data-slides-desktop': slidesDesktop,
    'data-slides-tablet': slidesTablet,
    'data-slides-mobile': slidesMobile,
  } : {};

  const gridStyle = displayType === 'grid' ? {
    display: 'grid',
    gridTemplateColumns: `repeat(${gridColumns}, 1fr)`,
    gap: gridGap
  } : {};

  const innerBlocksProps = useInnerBlocksProps.save({
    className: `mbn-slider-items ${displayType === 'grid' ? 'items-grid' : 'items-slider'}`,
    style: gridStyle,
    ...sliderSettings,
    'data-item-min-height': itemMinHeight || undefined
  });

  return (
    <div {...blockProps}>
      <div {...innerBlocksProps} />
    </div>
  );
}
