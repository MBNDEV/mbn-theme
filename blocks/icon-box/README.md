# Icon Box Block

A flexible icon box block that displays an icon (image or SVG) with title and description.

## Features

- **Icon Type**: Choose between uploaded image or custom SVG code
- **Icon Position**: Left, Center, or Right alignment
- **Icon Size**: Adjustable from 24px to 200px
- **Title Tag**: H3, H4, H5, or Span options
- **Description**: Rich text with simple HTML support (bold, italic, links)
- **Auto-hide**: Empty elements automatically hidden on frontend

## Attributes

- `iconType`: 'image' | 'svg' - Type of icon to display
- `iconImageUrl`: string - URL of uploaded image
- `iconImageId`: number - Media library ID
- `iconSvgCode`: string - Custom SVG code
- `iconPosition`: 'left' | 'center' | 'right' - Icon position
- `iconSize`: number - Icon size in pixels (default: 64)
- `title`: string - Title text
- `titleTag`: 'h3' | 'h4' | 'h5' | 'span' - HTML tag for title
- `description`: string - Description with HTML support

## Usage

1. Add the Icon Box block from the block inserter
2. Choose icon type (Image or SVG Code) in the sidebar
3. Upload an image or paste SVG code
4. Adjust icon position and size
5. Add title and description
6. Select appropriate heading level

## Example SVG Code

```svg
<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2"/>
  <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2"/>
  <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2"/>
</svg>
```

## Auto-hide Behavior

- If no icon and no content: Block doesn't render
- If no icon: Only content displays
- If empty title: Title doesn't render
- If empty description: Description doesn't render
- Responsive: Stacks vertically on mobile for left/right layouts
