# MBN Theme - Child Theme Development Guide

## Why Use Child Themes?

Child themes allow you to customize MBN Theme for specific **client projects** while keeping the parent theme intact. This approach:

- ✅ **Preserves parent theme updates** - Get bug fixes and new features
- ✅ **Isolates client customizations** - Client-specific changes stay separate
- ✅ **Enables version control** - Lock to specific parent versions
- ✅ **Simplifies maintenance** - Multiple clients share one parent theme
- ✅ **Reduces conflicts** - Changes don't affect other projects

## Architecture: Parent vs Child

### MBN Theme (Parent) Contains:
- 🌐 **Global infrastructure** - Build system, WordPress integration
- 📦 **Reusable blocks** - Hero sections, contact forms, navigation
- 🔧 **Shared functionality** - Import/export tools, sync system
- 📚 **Documentation** - Guides, standards, workflows

### Child Theme (Your Client Project) Contains:
- 🎨 **Client branding** - Colors, fonts, custom CSS
- 📦 **Client-specific blocks** - Unique to this project
- ⚙️ **Custom post types** - Client's content types
- 🗂️ **Custom menus** - Client navigation structure
- 📄 **Custom templates** - Client-specific layouts

**Golden Rule:** If it's useful for other clients → contribute to parent. If it's specific to this client → keep in child theme.

## What Goes in the Child Theme?

### ✅ Always Create in Child Theme:
- **Custom Gutenberg blocks** - Project-specific blocks
- **Custom navigation menus** - Additional menu locations
- **Custom post types** - Portfolio, testimonials, case studies, etc.
- **Custom taxonomies** - Project-specific categories/tags
- **Project-specific styles** - Brand colors, custom CSS
- **Custom page templates** - Landing pages, special layouts
- **Custom functions** - Project-specific functionality
- **Theme customizer options** - Client-specific settings
- **Custom widgets** - Project-specific widget areas
- **Custom shortcodes** - Project-specific shortcodes

### ❌ Keep in Parent Theme:
- **Core functionality** - Features needed across all projects
- **Base blocks** - Reusable blocks for all sites
- **Framework features** - Theme architecture, build system
- **Bug fixes** - Issues affecting all projects
- **Performance improvements** - Optimizations for everyone

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
│   ├── shared across all projects
│   └── blocks/                   # Parent theme blocks (shared)
│       ├── hero-section/
│       ├── contact-form/
│       └── ...
│
└── client-project-theme/         # Child theme
    ├── style.css                 # Theme info + custom CSS
    ├── functions.php             # Custom functions & hooks
    ├── screenshot.png            # Theme preview
    │
    ├── assets/                   # Project-specific assets
    │   ├── css/
    │   │   └── custom.css        # Additional styles
    │   ├── js/
    │   │   └── custom.js         # Custom JavaScript
    │   └── images/
    │       └── logo.png          # Project images
    │
    ├── blocks/                   # 🎨 Custom blocks (child theme only)
    │   ├── custom-cta-block/     # Project-specific CTA block
    │   │   ├── block.json
    │   │   ├── index.js
    │   │   ├── edit.js
    │   │   ├── save.js
    │   │   └── style.css
    │   ├── testimonial-slider/   # Project testimonials
    │   └── team-member-grid/     # Project team display
    │
    ├── template-parts/           # Override parent templates
    │   └── header-template.php   # Custom header
    │
    └── page-templates/           # 📄 Custom page templates
        ├── template-landing.php  # Landing page
        └── template-portfolio.php # Portfolio page
```

**Key Points:**
- ✅ **Custom blocks go in child theme** (`blocks/` folder)
- ✅ **Custom menus registered in child `functions.php`**
- ✅ **Custom post types in child theme**
- ✅ **Project assets stay in child theme**
- ⚠️ **Parent blocks are inherited automatically**

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

---

## Contributing Blocks Back to Parent

If you build a block in your child theme that would be useful for other clients, contribute it back to mbn-theme!

### When to Contribute

**✅ Contribute if the block is:**
- Generic and reusable across projects
- Not tied to specific client branding
- Useful for other clients
- Examples: Hero sections, testimonial sliders, pricing tables, team grids, FAQ sections

**❌ Keep in child if the block is:**
- Specific to this client's business logic
- Contains hardcoded client data
- One-off customization
- Examples: Client-specific calculators, custom API integrations, unique workflows

### How to Contribute a Block

**Step 1: Identify a reusable block**
```bash
# You built this in your child theme
child-theme/blocks/testimonial-slider/
```

**Step 2: Generalize it**
- Remove client-specific branding
- Make colors/fonts configurable via attributes
- Remove hardcoded content
- Add clear documentation

```javascript
// ❌ Bad - Hardcoded client color
const blockStyle = { backgroundColor: '#ClientBlue' };

// ✅ Good - Configurable
const blockStyle = { 
    backgroundColor: attributes.backgroundColor || '#000' 
};
```

**Step 3: Copy to parent theme**
```bash
cd mbn-theme
mkdir -p blocks/testimonial-slider
# Copy generalized files
```

**Step 4: Test in parent**
```bash
npm run build
# Test on a demo site or staging environment
```

**Step 5: Create pull request**
```bash
git checkout -b feature/testimonial-slider
git add blocks/testimonial-slider
git commit -m "feat: add reusable testimonial slider block"
git push origin feature/testimonial-slider
# Open PR for review
```

**Step 6: After merge, create release**
```bash
# Once merged to master
php scripts/bump-version.php minor  # v1.2.0
git tag -a v1.2.0 -m "Release v1.2.0 - Add testimonial slider"
git push origin master --tags
```

**Step 7: Update child themes**

Now all your child themes can use the new block:
```bash
cd ../client-project-theme
cd mbn-theme
git checkout v1.2.0
npm run build
# Testimonial slider now available!
```

### Example: Real Workflow

**Scenario:** You built a custom "Team Grid" block for Client A.

1. **Built in child theme first:**
   ```
   client-a-theme/blocks/team-grid/
   ```

2. **Client A approves, it works great**

3. **You realize Client B could use this too**

4. **Generalize the block:**
   - Remove Client A's specific styling
   - Make avatar size configurable
   - Make colors customizable
   - Add documentation

5. **Move to parent:**
   ```bash
   cp -r client-a-theme/blocks/team-grid mbn-theme/blocks/
   cd mbn-theme
   # Edit to make generic
   git add blocks/team-grid
   git commit -m "feat: add reusable team grid block"
   ```

6. **Release new version:**
   ```bash
   php scripts/bump-version.php minor  # Now v1.3.0
   git tag -a v1.3.0 -m "Add team grid block"
   git push origin master --tags
   ```

7. **Update both client themes:**
   ```bash
   # Client B immediately benefits
   cd client-b-theme/mbn-theme
   git checkout v1.3.0
   npm run build
   # Team grid block now available!
   
   # Client A can also upgrade (optional)
   cd client-a-theme/mbn-theme
   git checkout v1.3.0
   # Can remove their custom version now
   ```

### Benefits of Contributing Back

- ✅ **Reuse across projects** - Build once, use everywhere
- ✅ **Shared maintenance** - Bug fixes benefit all projects
- ✅ **Team collaboration** - Others can improve your blocks
- ✅ **Faster development** - Less rebuilding for each client
- ✅ **Consistency** - Similar features across projects

---

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
