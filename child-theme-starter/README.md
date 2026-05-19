# MBN Child Theme Starter

This is a starter template for creating child themes based on MBN Theme.

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

### 4. Activate Your Child Theme

1. Go to WordPress Admin → Appearance → Themes
2. Find your child theme
3. Click **Activate**

## Customization

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
├── style.css              # Theme info & custom styles
├── functions.php          # Custom functions & hooks
├── screenshot.png         # Theme screenshot (880x660px recommended)
├── README.md             # This file
│
├── assets/               # Custom assets (optional)
│   ├── css/
│   ├── js/
│   └── images/
│
├── blocks/               # Custom blocks (optional)
│   └── custom-block/
│
├── template-parts/       # Override parent templates (optional)
│   └── header-template.php
│
└── page-templates/       # Custom page templates (optional)
    └── template-custom.php
```

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
