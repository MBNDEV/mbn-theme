# Custom Blocks Development

This directory contains custom Gutenberg blocks for your child theme.

## Creating a New Block

### 1. Create Block Directory Structure

```
blocks/your-block-name/
├── block.json       # Block configuration
├── index.js         # Block registration
├── edit.js          # Editor component
├── save.js          # Frontend save component
└── style.css        # Block styles
```

### 2. Update block.json

```json
{
	"name": "mbn-child-theme/your-block-name",
	"title": "Your Block Name",
	"category": "text",
	"icon": "smiley"
}
```

### 3. Build Your Block

```bash
npm run build         # Production build
npm run watch:blocks  # Development with watch
```

### 4. Register in functions.php

The block will be auto-registered by the `mbn_child_register_blocks_from_build()` function.

## Example Block

See `example-block/` for a complete working example with:
- RichText content editing
- Block props usage
- Inline styles
- Proper save function

## Block Development Resources

- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [Block Registration](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/)
- [Block Attributes](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/)
- [@wordpress/block-editor Components](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/)
