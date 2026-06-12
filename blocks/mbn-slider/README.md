# MBN Slider Block

A flexible container block that displays items as a responsive grid or interactive slider carousel. Add any WordPress blocks inside each item.

## Features

### Item Management
- **Add Any Blocks** - Each item can contain any WordPress blocks
- **Unlimited Items** - Add as many items as you need
- **Dedicated Slider Item Block** - Uses custom `mbn-theme/slider-item` block
- **Easy Management** - Add, remove, or reorder items in the editor

### Display Modes
- **Grid Layout** - Static responsive grid with customizable columns
- **Slider/Carousel** - Slick Slider with autoplay and navigation

### Editor Experience
- **Grid Display in Editor** - Editor always shows items in grid format for easier management
- **Slider Item Blocks** - Each item is a dedicated block that can contain any blocks
- **Visual Editing** - See all items at once while editing
- **Item Labels** - Each item shows "Slider Item" label for easy identification
- **Add Button Position** - The "+ Slider Item" button appears at the bottom of the slider for adding new items

### Grid Options
- **Columns** - 1-6 columns (adjustable)
- **Gap Control** - Custom spacing between items
- **Responsive** - Auto-adjusts on mobile and tablet

### Slider Options
- **Autoplay** - Auto-rotate items
- **Autoplay Speed** - 1-10 seconds interval
- **Transition Speed** - Slide animation speed
- **Infinite Loop** - Continuous scrolling
- **Navigation Dots** - Show/hide pagination
- **Navigation Arrows** - Show/hide prev/next arrows
- **Fade Effect** - Use fade transition (for 1 slide at a time)
- **Center Mode** - Center the active slide with partial view of adjacent slides

### Responsive Breakpoints
- **Desktop (>1024px)** - 1-6 items visible (default: 3)
- **Tablet (768-1024px)** - 1-4 items visible (default: 2)
- **Mobile (<768px)** - 1-3 items visible (default: 1)

### Styling Options
- **Item Min Height** - Set consistent minimum height for all items
- **Full/Wide Alignment** - Supports alignwide and alignfull
- **Spacing Controls** - Native margin and padding

## Requirements

**For Slider Mode:**
This block requires Slick Slider library (already loaded by your theme via the logo-list block).

## Usage Examples

### 1. Testimonial Slider
```
Display Type: Slider
Slides Desktop: 1
Slides Tablet: 1
Slides Mobile: 1
Autoplay: Yes
Show Dots: Yes
Fade Effect: Yes

Items: Add Group blocks with:
- Paragraph for quote
- Heading for author name
- Image for avatar
```

### 2. Feature Cards Grid
```
Display Type: Grid
Columns: 3
Grid Gap: 2rem

Items: Add Group blocks with:
- Image
- Heading
- Paragraph
- Button
```

### 3. Content Carousel
```
Display Type: Slider
Slides Desktop: 3
Slides Tablet: 2
Slides Mobile: 1
Autoplay: Yes
Center Mode: Yes

Items: Add Group blocks with any content
```

### 4. Hero Slider
```
Display Type: Slider
Slides Desktop: 1
Fade Effect: Yes
Autoplay: Yes
Show Arrows: Yes
Item Min Height: 600px

Items: Add Group blocks with:
- Cover block with image
- Heading + Button inside
```

## How to Add Items

1. **Add MBN Slider block** to your page
2. **Initial items** - Block starts with 3 Slider Item blocks
3. **Add content** - Click inside each Slider Item to add any blocks you want
4. **Add more items** - Click the + button and select "Slider Item"
5. **Remove items** - Select a Slider Item block and delete it
6. **Reorder items** - Drag and drop Slider Item blocks to reorder
7. **Editor displays as grid** - All items shown in grid format for easy editing
8. **Item settings** - Each Slider Item has its own min height setting

## Technical Details

**Block Name**: `mbn-theme/mbn-slider`  
**Category**: Layout  
**Supports**: Align, Spacing

## File Structure

```
blocks/mbn-slider/
├── block.json          # Block metadata
├── index.js            # Registration
├── edit.js             # Editor component with InnerBlocks
├── save.js             # Frontend output
├── style.css           # Styles + Slick overrides
├── view.js             # Frontend JS (Slick init)
├── README.md           # Documentation
└── slider-item/        # Nested slider-item block
    ├── block.json      # Slider item metadata
    ├── index.js        # Registration
    ├── edit.js         # Editor component
    ├── save.js         # Frontend output
    ├── style.css       # Item styles
    └── README.md       # Item documentation
```

## Block Organization

The Slider Item block is nested inside the MBN Slider folder structure. Both blocks are registered separately but are organized together for better code organization.

## Responsive Behavior

**Grid Mode:**
- Desktop: Uses your column setting
- Tablet: Auto-adjusts to fit screen
- Mobile: Responsive columns

**Slider Mode:**
- Desktop: Shows your configured number
- Tablet: Shows tablet setting
- Mobile: Shows mobile setting, may hide arrows

## Tips

### Best Practices
- Keep item content balanced for better grid/slider appearance
- Use min height to ensure consistent item sizes
- For hero sliders, use 1 slide at a time with fade effect
- For content carousels, show 3-4 items on desktop

### Performance
- Lazy load images inside items
- Limit autoplay speed to 3000ms minimum
- Use infinite loop sparingly with large content

### Styling
- Style Group blocks inside items for custom designs
- Use consistent padding inside Group blocks
- Add background colors/images to Group blocks for card effects

## Customization

### Custom Arrow Styles

Override in your theme CSS:

```css
.items-slider .slick-prev,
.items-slider .slick-next {
  background: rgba(255, 255, 255, 0.9);
  border: 2px solid #333;
}

.items-slider .slick-prev:before,
.items-slider .slick-next:before {
  color: #333;
}
```

### Custom Dot Styles

```css
.items-slider .slick-dots li button:before {
  color: #your-color;
  font-size: 16px;
}
```
