# MBN Section Block

A flexible container block with comprehensive layout and background controls.

## Features

### Layout Panel
- **Alignment**: Text alignment (left, center, right)
- **Justify Content**: Flex justify options (start, center, end, space-between, space-around)
- **Align Items**: Flex align options (start, center, end, stretch)
- **Shadow**: Pre-defined shadow presets (none, small, medium, large) or custom shadow
- **Border**: Style, width, color, and radius controls
- **Padding**: Individual controls for top, right, bottom, left
- **Margin**: Individual controls for top, right, bottom, left
- **Width**: Width, max-width, min-width controls
- **Height**: Height, max-height, min-height controls

### Background Panel
- **None**: No background
- **Color**: Solid color with alpha channel support
- **Gradient**: CSS gradient input
- **Image**: Background image with size, position, repeat, and attachment controls
- **Video**: Background video with opacity control

## Usage

1. Add the "MBN Section" block to your page
2. Configure layout options in the "Layout" panel
3. Configure background options in the "Background" panel
4. Add other blocks inside the section using the + button

## Editor Features

- Dotted outline in the editor for easy identification
- Outline becomes solid blue when selected
- Outline darkens on hover
- Minimum height ensures the section is always visible

## Technical Details

- Supports InnerBlocks for nesting other blocks
- Supports align wide and align full
- All styles are applied inline for maximum flexibility
- Video backgrounds are positioned absolutely with z-index -1
- Content is positioned relatively to appear above backgrounds
