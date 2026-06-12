# Logo List Block

Display logos as a responsive grid or interactive slider carousel.

## Features

### Display Modes
- **Grid Layout** - Static responsive grid
- **Slider/Carousel** - Slick Slider with autoplay and controls

### Image Gallery
- **Multi-Upload** - Select multiple logos at once
- **WordPress Media Library** - Full integration
- **Easy Management** - Add/remove logos in editor
- **Lazy Loading** - Optimized performance

### Grid Options
- **Columns** - 2-8 columns (adjustable)
- **Gap Control** - Custom spacing between logos
- **Responsive** - Auto-adjusts on mobile

### Slider Options
- **Autoplay** - Auto-rotate logos
- **Autoplay Speed** - 1-10 seconds interval
- **Transition Speed** - Slide animation speed
- **Infinite Loop** - Continuous scrolling
- **Navigation Dots** - Show/hide pagination
- **Navigation Arrows** - Show/hide prev/next arrows

### Responsive Breakpoints
- **Desktop (>1024px)** - 1-8 logos visible
- **Tablet (768-1024px)** - 1-6 logos visible  
- **Mobile (<768px)** - 1-4 logos visible

### Styling Options
- **Logo Height** - Set consistent height
- **Grayscale** - Convert logos to grayscale
- **Hover Effect** - Color on hover (with grayscale)

## Requirements

**For Slider Mode:**
This block requires Slick Slider library. Add to your theme:

### 1. Enqueue Slick Slider

Add to `functions.php`:

```php
function enqueue_slick_slider() {
  // Slick CSS
  wp_enqueue_style(
    'slick-slider',
    'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css',
    array(),
    '1.8.1'
  );
  
  wp_enqueue_style(
    'slick-theme',
    'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css',
    array(),
    '1.8.1'
  );
  
  // Slick JS
  wp_enqueue_script(
    'slick-slider',
    'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
    array('jquery'),
    '1.8.1',
    true
  );
}
add_action('wp_enqueue_scripts', 'enqueue_slick_slider');
```

## Usage Examples

### 1. Static Logo Grid
```
Display Type: Grid
Columns: 4
Grid Gap: 2rem
Grayscale: Yes
Color on Hover: Yes
```

### 2. Auto-Rotating Slider
```
Display Type: Slider
Autoplay: Yes
Autoplay Speed: 3000ms
Slides Desktop: 5
Slides Tablet: 3
Slides Mobile: 2
Show Arrows: Yes
Show Dots: No
```

### 3. Manual Slider (No Autoplay)
```
Display Type: Slider
Autoplay: No
Slides Desktop: 4
Slides Tablet: 3
Slides Mobile: 2
Show Arrows: Yes
Show Dots: Yes
```

## Technical Details

**Block Name**: `mbn-theme/logo-list`  
**Category**: Media  
**Supports**: Align, Spacing

## File Structure

```
blocks/logo-list/
├── block.json          # Block metadata
├── index.js            # Registration
├── edit.js             # Editor component
├── save.js             # Frontend output
├── style.css           # Styles
├── view.js             # Frontend JS (Slick init)
└── README.md           # Documentation
```

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile browsers (iOS Safari, Chrome Mobile)
- Requires JavaScript for slider mode
- Degrades to grid if JS disabled

## Performance Notes

- Images use lazy loading
- Slider only loads when display type is "slider"
- Optimized for mobile with responsive settings
- Grayscale filter uses CSS (hardware accelerated)

## Customization

### Custom Slider Styles

Override in your theme CSS:

```css
.logo-slider .slick-prev:before,
.logo-slider .slick-next:before {
  color: #your-color;
}

.logo-slider .slick-dots li button:before {
  color: #your-color;
}
```

### Custom Grid Breakpoints

Modify responsive behavior in style.css:

```css
@media (max-width: 1024px) {
  .logo-grid {
    grid-template-columns: repeat(3, 1fr) !important;
  }
}
```
