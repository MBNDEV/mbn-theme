# Child Theme Development - Quick Start

**For client-specific development only.**

This guide shows you how to develop custom features for a specific client using mbn-theme as the foundation.

## Purpose

- 🎨 **Client branding** - Custom colors, fonts, styles
- 📦 **Client blocks** - Project-specific Gutenberg blocks
- ⚙️ **Client features** - Custom post types, menus, templates
- 🎯 **Project needs** - Client-specific requirements

**Remember:** Use parent theme blocks when possible. Only build custom blocks for client-specific needs.

## Initial Setup

### 1. Create Child Theme from Starter

```powershell
# Windows PowerShell
.\scripts\create-child-theme.ps1 my-project-theme "My Project Theme"

# Or Linux/Mac
php scripts/create-child-theme.php my-project-theme "My Project Theme"
```

### 2. Install Dependencies

```bash
cd my-project-theme
npm install
```

### 3. Lock Parent to Stable Version

```bash
cd ../mbn-theme
git checkout v1.1.0
composer install --no-dev
npm install
npm run build
```

### 4. Activate Theme

WordPress Admin → Appearance → Themes → Activate your child theme

---

## Development Commands

```bash
# Development mode (watches for changes)
npm run start

# Build for production
npm run build

# Watch CSS only
npm run watch:css

# Watch blocks only
npm run watch:blocks
```

---

## Creating a Custom Block

### 1. Create Block Files

```
blocks/my-hero-block/
├── block.json
├── index.js
├── edit.js
├── save.js
└── style.css
```

### 2. Configure block.json

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "mbn-child-theme/my-hero-block",
  "title": "My Hero Block",
  "category": "design",
  "icon": "cover-image",
  "attributes": {
    "title": {
      "type": "string",
      "default": "Hero Title"
    }
  }
}
```

### 3. Create edit.js (Editor View)

```javascript
import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	
	return (
		<div { ...blockProps }>
			<RichText
				tagName="h1"
				value={ attributes.title }
				onChange={ (title) => setAttributes({ title }) }
				placeholder="Enter title..."
			/>
		</div>
	);
}
```

### 4. Create save.js (Frontend Output)

```javascript
import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const blockProps = useBlockProps.save();
	
	return (
		<div { ...blockProps }>
			<RichText.Content tagName="h1" value={ attributes.title } />
		</div>
	);
}
```

### 5. Create index.js (Registration)

```javascript
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import save from './save';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: Edit,
	save,
} );
```

### 6. Build

```bash
npm run build
```

**Done!** Block is auto-registered and ready to use in the editor.

---

## Import/Export Tools

### Access Tools

WordPress Admin → Block Templates → Sync Tools

### Export Workflow

1. **Create content** in WordPress (pages, menus, templates)
2. **Export** using Sync Tools
3. **Commit files** to Git
4. **Deploy** to other environments
5. **Import** on staging/production

### What Can Be Exported?

- ✅ Block Templates (Header/Footer)
- ✅ Page Content
- ✅ Navigation Menus
- ✅ Page Template Blocks

### Example: Export Page Content

1. Go to Block Templates → Sync Tools
2. Click "Export Pages"
3. Files saved to `resources/pages/`
4. Commit: `git add resources/pages/ && git commit -m "Export pages"`
5. Push to repo
6. On other env: Import Pages

---

## Custom Menus

Uncomment in `functions.php`:

```php
// In mbn_child_setup() function
register_nav_menus(
	array(
		'child-custom-menu' => __( 'Child Custom Menu', 'mbn-child-theme' ),
		'child-footer-menu' => __( 'Child Footer Menu', 'mbn-child-theme' ),
	)
);
```

Use in templates:

```php
wp_nav_menu( array( 'theme_location' => 'child-custom-menu' ) );
```

---

## Custom Post Types

Uncomment in `functions.php`:

```php
// In mbn_child_register_post_types() function
register_post_type(
	'portfolio',
	array(
		'labels' => array(
			'name' => __( 'Portfolio', 'mbn-child-theme' ),
		),
		'public' => true,
		'has_archive' => true,
		'supports' => array( 'title', 'editor', 'thumbnail' ),
	)
);
```

---

## Tailwind CSS Customization

### Add Custom Colors

Edit `tailwind.config.js`:

```javascript
theme: {
	extend: {
		colors: {
			'brand-primary': '#0066cc',
			'brand-secondary': '#ff6600',
		}
	}
}
```

### Add Custom CSS

Edit `resources/css/app.css`:

```css
@layer components {
	.btn-hero {
		@apply px-6 py-3 bg-brand-primary text-white rounded-lg hover:bg-blue-700;
	}
}
```

### Build

```bash
npm run build:css
```

---

## Troubleshooting

### Blocks Not Appearing?

```bash
# Rebuild blocks
npm run build

# Check build directory
ls build/blocks/

# Clear WordPress cache
```

### Styles Not Loading?

```bash
# Rebuild CSS
npm run build:css

# Check output
ls assets/build/tailwind.css

# Hard refresh browser (Ctrl+Shift+R)
```

### Parent Theme Issues?

```bash
cd ../mbn-theme
git checkout v1.1.0
npm install
npm run build
```

---

## Documentation

- **Full README**: [README.md](README.md)
- **Block Development**: [blocks/README.md](blocks/README.md)
- **Parent Theme Guide**: [../docs/CHILD-THEME-GUIDE.md](../docs/CHILD-THEME-GUIDE.md)
- **WordPress Block Editor**: https://developer.wordpress.org/block-editor/

---

## Common Tasks Reference

```bash
# Create child theme
php scripts/create-child-theme.php my-theme

# Install dependencies
npm install

# Start development
npm run start

# Build for production
npm run build

# Create new block
mkdir -p blocks/my-block
# Add files: block.json, index.js, edit.js, save.js

# Build and test
npm run build

# Check WordPress Admin → Editor → Add Block
```
