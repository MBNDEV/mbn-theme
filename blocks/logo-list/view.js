/**
 * Logo List Block - Frontend Script
 * Initializes Slick Slider for logo carousel
 */

document.addEventListener('DOMContentLoaded', function() {
  // Check if jQuery and Slick are loaded
  if (typeof jQuery === 'undefined' || typeof jQuery.fn.slick === 'undefined') {
    console.warn('Logo List: jQuery or Slick Slider not loaded');
    return;
  }

  const sliders = document.querySelectorAll('.logo-slider');
  
  sliders.forEach((slider) => {
    const autoplay = slider.dataset.autoplay === 'true';
    const autoplaySpeed = parseInt(slider.dataset.autoplaySpeed) || 3000;
    const speed = parseInt(slider.dataset.speed) || 500;
    const infinite = slider.dataset.infinite === 'true';
    const dots = slider.dataset.dots === 'true';
    const arrows = slider.dataset.arrows === 'true';
    const slidesDesktop = parseInt(slider.dataset.slidesDesktop) || 5;
    const slidesTablet = parseInt(slider.dataset.slidesTablet) || 3;
    const slidesMobile = parseInt(slider.dataset.slidesMobile) || 2;
    const logoHeight = slider.dataset.logoHeight || '80px';

    // Set logo height via CSS variable
    slider.style.setProperty('--logo-height', logoHeight);

    // Initialize Slick Slider
    jQuery(slider).slick({
      slidesToShow: slidesDesktop,
      slidesToScroll: 1,
      autoplay: autoplay,
      autoplaySpeed: autoplaySpeed,
      speed: speed,
      infinite: infinite,
      dots: dots,
      arrows: arrows,
      cssEase: 'ease-in-out',
      pauseOnHover: true,
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: slidesTablet,
            slidesToScroll: 1,
          }
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: slidesMobile,
            slidesToScroll: 1,
            arrows: false,
          }
        }
      ]
    });

    // Apply logo height to images
    const images = slider.querySelectorAll('.logo-list-item img');
    images.forEach(img => {
      img.style.height = logoHeight;
      img.style.width = 'auto';
      img.style.maxWidth = '100%';
    });
  });
});
