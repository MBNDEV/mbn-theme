/**
 * MBN Slider Block - Frontend Script
 * Initializes Slick Slider for slider display mode
 */

document.addEventListener('DOMContentLoaded', function() {
  // Check if jQuery and Slick are loaded
  if (typeof jQuery === 'undefined' || typeof jQuery.fn.slick === 'undefined') {
    console.warn('MBN Slider: jQuery or Slick Slider not loaded');
    return;
  }

  const sliders = document.querySelectorAll('.mbn-slider.display-slider .items-slider');
  
  sliders.forEach((slider) => {
    // Check if already initialized
    if (jQuery(slider).hasClass('slick-initialized')) {
      return;
    }

    const autoplay = slider.dataset.autoplay === 'true';
    const autoplaySpeed = parseInt(slider.dataset.autoplaySpeed) || 3000;
    const speed = parseInt(slider.dataset.speed) || 500;
    const infinite = slider.dataset.infinite === 'true';
    const dots = slider.dataset.dots === 'true';
    const arrows = slider.dataset.arrows === 'true';
    const fade = slider.dataset.fade === 'true';
    const centerMode = slider.dataset.centerMode === 'true';
    const slidesDesktop = parseInt(slider.dataset.slidesDesktop) || 3;
    const slidesTablet = parseInt(slider.dataset.slidesTablet) || 2;
    const slidesMobile = parseInt(slider.dataset.slidesMobile) || 1;
    const itemMinHeight = slider.dataset.itemMinHeight || '';

    // Apply min height to items if specified
    if (itemMinHeight) {
      const items = slider.querySelectorAll('.slider-item, .mbn-slider-items > *');
      items.forEach(item => {
        item.style.minHeight = itemMinHeight;
      });
    }

    // Slick configuration
    const slickConfig = {
      slidesToShow: slidesDesktop,
      slidesToScroll: 1,
      autoplay: autoplay,
      autoplaySpeed: autoplaySpeed,
      speed: speed,
      infinite: infinite,
      dots: dots,
      arrows: arrows,
      fade: fade && slidesDesktop === 1, // Fade only works with 1 slide
      centerMode: centerMode,
      cssEase: 'ease-in-out',
      pauseOnHover: true,
      adaptiveHeight: true,
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: slidesTablet,
            slidesToScroll: 1,
            centerMode: centerMode && slidesTablet === 1,
            fade: fade && slidesTablet === 1,
          }
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: slidesMobile,
            slidesToScroll: 1,
            arrows: slidesMobile === 1 ? arrows : false,
            centerMode: centerMode && slidesMobile === 1,
            fade: fade && slidesMobile === 1,
          }
        }
      ]
    };

    // Initialize Slick Slider
    try {
      jQuery(slider).slick(slickConfig);
      
      // Add custom class for fade mode
      if (fade) {
        jQuery(slider).addClass('fade');
      }
      
      // Add custom class for center mode
      if (centerMode) {
        jQuery(slider).addClass('slick-center');
      }
    } catch (error) {
      console.error('MBN Slider: Failed to initialize slider', error);
    }
  });
});
