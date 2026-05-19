# MBN Child Theme Starter

This is a starter template for creating **client-specific** child themes based on MBN Theme.

## Purpose

Use this for **client projects only**. This child theme inherits all global functionality and reusable blocks from mbn-theme, and adds client-specific customizations.

**What Goes Here:**
- ✅ Client branding and colors
- ✅ Client-specific custom blocks
- ✅ Custom post types for this client
- ✅ Client-specific navigation menus
- ✅ Project-specific templates
- ✅ Client requirements and features

**What Stays in Parent (mbn-theme):**
- ⚠️ Global/reusable blocks
- ⚠️ Shared navigation components
- ⚠️ Infrastructure
- ⚠️ Build system

## Quick Start

### 1. Copy This Folder

Copy this entire `child-theme-starter` folder to your WordPress themes directory and rename it:

```bash
# From wp-content/themes/
cp -r mbn-theme/child-theme-starter ../client-project-theme
```

Or manually:
1. Copy the `child-theme-starter` folder
2. Paste it to `wp-content/themes/`
3. Rename to your project name (e.g., `acme-company-theme`)

### 2. Customize Theme Information

Edit `style.css` and update:
- **Theme Name**: Change to your project name (e.g., "Acme Company Theme")
- **Theme URI**: Your project URL
- **Author**: Your name or company
- **Description**: Describe your project
- **Text Domain**: Change to match your theme slug (e.g., `acme-company-theme`)

```css
/*
Theme Name: Acme Company Theme
Theme URI: https://www.acmecompany.com/
Description: Custom child theme for Acme Company
Author: Your Name
Author URI: https://yoursite.com/
Template: mbn-theme
Version: 1.0.0
Text Domain: acme-company-theme
*/
```

### 3. Ensure Parent Theme is Installed

Make sure `mbn-theme` is installed and at a specific version:

```bash
cd wp-content/themes/mbn-theme
git fetch --all --tags
git checkout v1.1.0  # Use a stable version
composer install --no-dev
npm install
npm run build
```

### 4. Install Child Theme Dependencies

```bash
cd ../your-child-theme
npm install
```

### 5. Build Child Theme Assets

```bash
npm run build  # Production build
# or
npm run dev    # Development with watch mode
```

### 6. Activate Your Child Theme

1. Go to WordPress Admin → Appearance → Themes
2. Find your child theme
3. Click **Activate**

## Development Workflow

### Building Custom Blocks

```bash
# Start development mode (watches for changes)
npm run start

# Or watch specific tasks
npm run watch:blocks  # Watch block changes
npm run watch:css     # Watch CSS changes

# Production build
npm run build
```

### Creating Custom Blocks

Create your custom Gutenberg blocks in the child theme:

1. **Create block directory and files:**
   ```
   blocks/my-custom-block/
   ├── block.json       # Block configuration
   ├── index.js         # Block registration
   ├── edit.js          # Editor component (React)
   ├── save.js          # Frontend save component
   └── style.css        # Block styles
   ```

2. **Configure block.json:**
   ```json
   {
     "name": "mbn-child-theme/my-custom-block",
     "title": "My Custom Block",
     "category": "widgets",
     "icon": "smiley"
   }
   ```

3. **Build the block:**
   ```bash
   npm run build
   ```

4. **Block is auto-registered!** The `mbn_child_register_blocks()` function automatically registers all blocks from `build/blocks/`.

See `blocks/example-block/` for a complete working example.

### Customizing Styles with Tailwind CSS

1. **Edit** `resources/css/app.css`:
   ```css
   @layer components {
     .btn-custom {
       @apply px-4 py-2 bg-blue-600 text-white rounded;
     }
   }
   ```

2. **Customize colors in** `tailwind.config.js`:
   ```js
   theme: {
     extend: {
       colors: {
         'brand-primary': '#0066cc',
       }
     }
   }
   ```

3. **Build:**
   ```bash
   npm run build:css
   ```

## Import/Export Tools

### Using Parent Theme's Sync Tools

The child theme has full access to the parent theme's import/export tools:

**Access:** WordPress Admin → Block Templates → Sync Tools

**Available Tools:**
- **Export Block Templates** - Save header/footer templates to PHP files
- **Import Block Templates** - Load templates from PHP files
- **Export Pages** - Save page content as JSON
- **Import Pages** - Restore page content from JSON
- **Export Navigation** - Save navigation menus as JSON
- **Import Navigation** - Restore menus from JSON

**Workflow:**
1. Create your templates/pages/menus in WordPress
2. Export to files (saved in `template-parts/` and `resources/`)
3. Commit to version control
4. Import on other environments (staging/production)

### Custom Sync Paths (Optional)

To add child-theme-specific sync paths, uncomment in `functions.php`:

```php
add_filter( 'mbn_sync_additional_paths', 'mbn_child_add_sync_paths' );
function mbn_child_add_sync_paths( $paths ) {
    $paths['child-templates'] = get_stylesheet_directory() . '/template-parts/custom';
    return $paths;
}
```

### Adding Custom Navigation Menus

Register project-specific menus in the child theme:

1. Uncomment in `functions.php` (within `mbn_child_setup` function):
   ```php
   register_nav_menus(
       array(
           'child-custom-menu' => __( 'Child Custom Menu', 'mbn-child-theme' ),
           'child-footer-menu' => __( 'Child Footer Menu', 'mbn-child-theme' ),
       )
   );
   ```

2. Use in your templates:
   ```php
   wp_nav_menu( array( 'theme_location' => 'child-custom-menu' ) );
   ```

### Adding Custom Post Types

Create project-specific post types in the child theme:

1. Uncomment in `functions.php` (within `mbn_child_register_post_types` function):
   ```php
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

### Adding Custom Styles

Add your custom CSS to `style.css`:

```css
/* Custom header color */
.site-header {
    background-color: #custom-color;
}

/* Custom button styles */
.wp-block-button__link {
    background-color: #your-brand-color;
}
```

### Adding Custom Functions

Add custom PHP functions to `functions.php`:

```php
// Custom post type
function my_custom_post_type() {
    register_post_type( 'projects', array(
        'public' => true,
        'label'  => 'Projects'
    ) );
}
add_action( 'init', 'my_custom_post_type' );
```

### Creating Custom Templates

Create custom page templates in your child theme:

```php
// template-custom-page.php
<?php
/**
 * Template Name: Custom Page
 */

get_header();

// Your custom template code

get_footer();
?>
```

### Overriding Parent Theme Files

You can override parent theme templates by creating files with the same name:

```
child-theme/
├── template-parts/
│   └── header-template.php  (overrides parent)
├── page-templates/
│   └── template-blank.php    (overrides parent)
└── blocks/
    └── hero-section/         (overrides parent block)
```

## File Structure

```
your-child-theme/
├── style.css                  # Theme info & custom CSS overrides
├── functions.php              # Custom functions, hooks & block registration
├── screenshot.png             # Theme screenshot (880x660px recommended)
├── package.json               # NPM dependencies & build scripts
├── webpack.config.js          # Webpack configuration for blocks
├── tailwind.config.js         # Tailwind CSS configuration
├── postcss.config.js          # PostCSS configuration
├── README.md                  # This file
├── .gitignore                 # Git ignore patterns
│
├── assets/                    # Custom assets
│   ├── build/                 # 🔨 Compiled Tailwind CSS (generated)
│   │   └── tailwind.css
│   ├── css/                   # Additional CSS files (optional)
│   ├── js/                    # Custom JavaScript (optional)
│   └── images/                # Project images
│
├── blocks/                    # 📦 Custom Gutenberg blocks (source)
│   ├── README.md              # Block development guide
│   ├── example-block/         # Example block (can be deleted)
│   │   ├── block.json         # Block configuration
│   │   ├── index.js           # Block registration
│   │   ├── edit.js            # Editor component
│   │   ├── save.js            # Frontend save
│   │   └── style.css          # Block styles
│   └── your-custom-block/     # Your custom blocks here
│
├── build/                     # 🔨 Compiled blocks (generated by webpack)
│   └── blocks/
│       └── example-block/
│           ├── index.js       # Compiled JS
│           ├── block.json     # Copied config
│           └── style.css      # Copied styles
│
├── resources/                 # Source files for build process
│   └── css/
│       └── app.css            # Tailwind CSS entry point
│
├── template-parts/            # Override parent templates (optional)
│   ├── header-template.php    # Custom header
│   └── footer-template.php    # Custom footer
│
└── page-templates/            # Custom page templates (optional)
    ├── template-landing.php   # Landing page template
    └── template-custom.php    # Other custom templates
```

**Key Directories:**
- 🔨 **Generated files** - Created by `npm run build`, don't edit directly
- 📦 **Source blocks** - Edit these, then run build
- **Parent blocks** - Inherited automatically from mbn-theme

## Parent Theme Version

This child theme is built for **MBN Theme v1.1.0**

To update the parent theme version:

```bash
cd wp-content/themes/mbn-theme
git fetch --all --tags
git checkout v1.2.0  # New version
composer install --no-dev
npm install
npm run build
```

## Best Practices

1. **Always use a stable parent version** (tag like v1.1.0, not master)
2. **Test after parent theme updates** before deploying to production
3. **Keep child theme lightweight** - only override what's necessary
4. **Document your customizations** in this README
5. **Version control your child theme** separately from parent

## Parent Theme Documentation

- [Parent Theme GitHub](https://github.com/MBNDEV/mbn-theme)
- [Versioning Guide](../docs/VERSIONING.md)
- [Block Development](../blocks/README.md)

## Support

For parent theme issues, check the [MBN Theme repository](https://github.com/MBNDEV/mbn-theme/issues).

For child theme customization help, contact your development team.
