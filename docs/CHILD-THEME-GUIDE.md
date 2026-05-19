# MBN Theme - Child Theme Development Guide

## Why Use Child Themes?

Child themes allow you to customize MBN Theme for specific projects while keeping the parent theme intact. This approach:

- ✅ **Preserves parent theme updates** - Get bug fixes and new features
- ✅ **Isolates customizations** - Project-specific changes stay separate
- ✅ **Enables version control** - Lock to specific parent versions
- ✅ **Simplifies maintenance** - Multiple projects, one parent theme
- ✅ **Reduces conflicts** - Changes don't affect other projects

## Quick Setup

### Step 1: Copy the Starter Template

```bash
# From your themes directory
cd wp-content/themes/

# Copy the starter template
cp -r mbn-theme/child-theme-starter client-project-theme

# Or on Windows:
# xcopy mbn-theme\child-theme-starter client-project-theme /E /I
```

### Step 2: Customize Theme Info

Edit `client-project-theme/style.css`:

```css
/*
Theme Name: Client Project Theme
Theme URI: https://www.clientsite.com/
Description: Custom theme for Client Project
Author: Your Name
Template: mbn-theme
Version: 1.0.0
Text Domain: client-project-theme
*/
```

### Step 3: Lock Parent Version

```bash
cd mbn-theme
git fetch --all --tags
git checkout v1.1.0  # Use stable version
composer install --no-dev
npm install
npm run build
```

### Step 4: Activate Child Theme

1. Go to WordPress Admin → Appearance → Themes
2. Activate your child theme
3. Parent theme loads automatically

## Development Workflow

### Project Structure

```
wp-content/themes/
├── mbn-theme/                    # Parent (v1.1.0)
│   ├── version locked via git tag
│   └── shared across all projects
│
└── client-project-theme/         # Child theme
    ├── style.css                 # Theme info + custom CSS
    ├── functions.php             # Custom functions
    ├── screenshot.png            # Theme preview
    │
    ├── assets/                   # Project-specific assets
    │   ├── css/
    │   ├── js/
    │   └── images/
    │
    ├── blocks/                   # Custom blocks
    │   └── client-custom-block/
    │
    ├── template-parts/           # Override parent templates
    │   └── header-template.php
    │
    └── page-templates/           # Custom page templates
        └── template-landing.php
```

### Customization Examples

#### 1. Custom Styles

Add to `style.css`:

```css
/* Brand colors */
:root {
    --brand-primary: #0066cc;
    --brand-secondary: #ff6600;
}

/* Custom header */
.site-header {
    background-color: var(--brand-primary);
}

/* Custom buttons */
.wp-block-button__link {
    background-color: var(--brand-secondary);
    border-radius: 25px;
}
```

#### 2. Custom Functions

Add to `functions.php`:

```php
// Custom post type
function client_custom_post_type() {
    register_post_type( 'portfolio', array(
        'label' => 'Portfolio',
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-portfolio',
        'supports' => array( 'title', 'editor', 'thumbnail' )
    ) );
}
add_action( 'init', 'client_custom_post_type' );

// Custom menu location
function client_register_menus() {
    register_nav_menus( array(
        'client-footer-menu' => __( 'Client Footer Menu', 'client-project-theme' )
    ) );
}
add_action( 'init', 'client_register_menus' );

// Custom image sizes
function client_custom_image_sizes() {
    add_image_size( 'portfolio-large', 1200, 800, true );
    add_image_size( 'portfolio-thumb', 400, 300, true );
}
add_action( 'after_setup_theme', 'client_custom_image_sizes', 11 );
```

#### 3. Override Parent Templates

Create `template-parts/header-template.php` in child theme:

```php
<?php
/**
 * Custom Header Template (overrides parent)
 */
?>
<header class="site-header custom-header">
    <!-- Your custom header markup -->
</header>
```

#### 4. Custom Page Template

Create `page-templates/template-landing.php`:

```php
<?php
/**
 * Template Name: Landing Page
 */

get_header();
?>

<div class="landing-page">
    <!-- Your custom landing page markup -->
</div>

<?php
get_footer();
?>
```

#### 5. Custom Gutenberg Block

Create `blocks/client-cta-block/`:

```
blocks/client-cta-block/
├── block.json
├── index.js
├── edit.js
├── save.js
└── style.css
```

Register in `functions.php`:

```php
function client_register_custom_blocks() {
    register_block_type( 
        get_stylesheet_directory() . '/blocks/client-cta-block' 
    );
}
add_action( 'init', 'client_register_custom_blocks' );
```

## Parent Theme Updates

### Check for Parent Updates

```bash
cd wp-content/themes/mbn-theme
git fetch --all --tags
git tag -l | sort -V
```

### Update Parent Version

```bash
# Switch to new version
git checkout v1.2.0

# Rebuild
composer install --no-dev
npm install
npm run build
```

### Testing After Update

1. **Staging first** - Always test on staging
2. **Check overrides** - Verify your template overrides still work
3. **Test functionality** - Check custom features
4. **Review changelog** - See what changed in parent
5. **Production deploy** - Only after thorough testing

## Multiple Child Themes

You can run multiple child themes from one parent:

```
wp-content/themes/
├── mbn-theme/                 # Parent (v1.1.0)
│
├── client-a-theme/            # Client A (uses v1.1.0)
├── client-b-theme/            # Client B (uses v1.1.0)
└── client-c-theme/            # Client C (uses v1.2.0)
```

Each child theme can use different parent versions as needed.

## Best Practices

### 1. Version Lock the Parent

Always use tagged versions, not master:

```bash
# ✅ Good
git checkout v1.1.0

# ❌ Bad
git checkout master
```

### 2. Minimize Overrides

Only override what you need:
- Use CSS for styling changes
- Use hooks/filters for functionality
- Override templates only when necessary

### 3. Document Customizations

Keep track of what you've customized in your child theme README:

```markdown
## Customizations

- Custom post type: Portfolio
- Override: header-template.php
- Custom blocks: CTA Block, Testimonial Slider
- Custom menu: Footer Secondary Menu
```

### 4. Separate Repositories

Version control child themes separately from parent:

```bash
cd client-project-theme
git init
git remote add origin https://github.com/yourorg/client-project-theme.git
```

### 5. Test Parent Updates

Before updating parent version in production:
1. Update on local development
2. Test thoroughly
3. Deploy to staging
4. Test again
5. Deploy to production

## Troubleshooting

### Styles Not Loading

Check that parent styles are enqueued in `functions.php`:

```php
wp_enqueue_style( 'mbn-parent-style', 
    get_template_directory_uri() . '/style.css' 
);
```

### Parent Functions Not Available

Ensure parent theme is installed and activated as template:

```css
/* In style.css */
Template: mbn-theme
```

### Build Assets Missing

Parent theme needs to be built:

```bash
cd ../mbn-theme
npm install
npm run build
```

### Version Conflicts

Lock parent to specific version:

```bash
cd ../mbn-theme
git checkout v1.1.0
```

## Resources

- **Parent Theme Repo**: https://github.com/MBNDEV/mbn-theme
- **Parent Versions**: https://github.com/MBNDEV/mbn-theme/releases
- **Parent Documentation**: `mbn-theme/docs/`
- **WordPress Child Themes**: https://developer.wordpress.org/themes/advanced-topics/child-themes/

## Support

- **Child theme issues**: Contact your development team
- **Parent theme issues**: Open issue on [GitHub](https://github.com/MBNDEV/mbn-theme/issues)
- **WordPress questions**: Check [WordPress Documentation](https://developer.wordpress.org/)
