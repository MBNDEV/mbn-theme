# MBN Theme Architecture & Workflow

## Overview

MBN Theme uses a **parent-child theme architecture** to separate global/shared functionality from client-specific customizations.

```
┌─────────────────────────────────────────────────────────────┐
│                        MBN THEME                             │
│                    (Parent Theme)                            │
│                                                               │
│  • Global infrastructure                                     │
│  • Reusable blocks library                                   │
│  • Shared components                                         │
│  • Build system                                              │
│  • Version control                                           │
└─────────────────────────────────────────────────────────────┘
                            ▲
                            │
                    (inherits from)
                            │
        ┌───────────────────┴───────────────────┐
        │                   │                   │
┌───────▼──────┐   ┌────────▼────────┐  ┌──────▼───────┐
│  Client A    │   │   Client B      │  │  Client C    │
│  Child Theme │   │   Child Theme   │  │  Child Theme │
│              │   │                 │  │              │
│ • Custom CSS │   │ • Custom blocks │  │ • Custom CPT │
│ • Branding   │   │ • Client colors │  │ • Features   │
└──────────────┘   └─────────────────┘  └──────────────┘
```

---

## MBN Theme (Parent) - Global Infrastructure

### Purpose
Provides the **foundation and shared components** for all client projects.

### Responsibilities

#### 1. **Global Infrastructure**
- Webpack build configuration
- Tailwind CSS framework
- WordPress block registration system
- Plugin Update Checker integration
- Automated deployment workflows
- Version releasing system
- WordPress coding standards enforcement

#### 2. **Reusable Blocks Library**
- Global navigation blocks (header, footer, mobile menu)
- Hero sections (simple, advanced)
- Contact form sections
- Column sections
- Image + text blocks
- Mission/intro sections
- Board members display
- Who we serve sections
- Any other blocks useful across multiple clients

#### 3. **Shared Functionality**
- Block template sync system (import/export)
- Page content sync tools
- Navigation menu sync
- Theme options framework
- Custom HTML injection
- Animation helpers
- Widget loader system
- Block patterns

#### 4. **Documentation**
- Development guides
- Versioning system
- Deployment procedures
- Child theme creation guides
- Block development documentation

### What Should NOT Be in Parent Theme

❌ **Client-specific code**
- Individual client branding
- Client-specific post types
- Client-only blocks
- Client business logic
- Hardcoded client content

❌ **Project-specific features**
- One-off customizations
- Client-requested features
- Project-specific integrations
- Client-specific templates

---

## Child Themes - Client-Specific Development

### Purpose
Customize mbn-theme for **specific client projects** without modifying the parent.

### Responsibilities

#### 1. **Client Branding**
```css
/* Child theme style.css */
:root {
    --client-primary: #client-color;
    --client-secondary: #client-color;
}

.site-header {
    background-color: var(--client-primary);
}
```

#### 2. **Custom Blocks for This Client**
```
child-theme/blocks/
├── client-specific-hero/
├── client-testimonials/
├── client-portfolio-grid/
└── client-pricing-table/
```

#### 3. **Client-Specific Post Types**
```php
// functions.php
register_post_type( 'client_portfolio', [...] );
register_post_type( 'client_services', [...] );
register_taxonomy( 'client_category', [...] );
```

#### 4. **Custom Navigation Menus**
```php
register_nav_menus( array(
    'client-mega-menu' => __( 'Client Mega Menu' ),
    'client-footer-secondary' => __( 'Client Footer Secondary' ),
) );
```

#### 5. **Page Templates**
```
template-parts/
├── client-landing-header.php
├── client-custom-footer.php
└── ...

page-templates/
├── template-client-services.php
├── template-client-portfolio.php
└── ...
```

---

## Development Workflow

### 1. Starting a New Client Project

```bash
# Step 1: Ensure mbn-theme is at stable version
cd wp-content/themes/mbn-theme
git fetch --all --tags
git checkout v1.1.0  # Use latest stable release
composer install --no-dev
npm install
npm run build

# Step 2: Create child theme for client
php scripts/create-child-theme.php mbn-child-theme-theme "Acme Company Theme"

# Step 3: Set up child theme
cd ../mbn-child-theme-theme
npm install
npm run start

# Step 4: Activate child theme
# WordPress Admin → Appearance → Themes → Activate
```

### 2. Developing for the Client

**Work in child theme:**
```bash
# Create custom blocks for this client
mkdir -p blocks/client-custom-hero
# Add block files...

# Build
npm run build

# Watch mode during development
npm run start
```

**Use parent blocks:**
- All parent theme blocks are automatically available
- Just use them in the editor - no registration needed
- Parent blocks update when you update mbn-theme version

### 3. Contributing Reusable Blocks Back to Parent

**When you build something useful for other projects:**

```bash
# Step 1: Identify reusable block in child theme
# Example: blocks/testimonial-slider/

# Step 2: Generalize the block
# - Remove client-specific branding
# - Make configurable via block attributes
# - Add clear documentation

# Step 3: Copy to mbn-theme
cd mbn-theme
mkdir -p blocks/testimonial-slider
# Copy files from child theme

# Step 4: Build and test in parent
npm run build
# Test on a demo site

# Step 5: Commit to mbn-theme
git add blocks/testimonial-slider
git commit -m "feat: add reusable testimonial slider block"

# Step 6: Create pull request
git push origin feature/testimonial-slider

# Step 7: After merge, create new release
# See docs/VERSIONING.md
```

### 4. Updating Parent Theme in Child Project

```bash
# In mbn-theme
git fetch --all --tags
git checkout v1.2.0

composer install --no-dev
npm install
npm run build

# Test child theme still works
# New parent blocks now available!
```

---

## Block Reusability Strategy

### Decision Tree: Where Should This Block Go?

```
Will this block be useful for other clients?
│
├─ YES → Add to mbn-theme (parent)
│   │
│   ├─ Is it ready now?
│   │   ├─ YES → Add directly to parent
│   │   └─ NO → Build in child, contribute later
│   │
│   └─ Examples:
│       • Hero sections
│       • Contact forms
│       • Testimonial sliders
│       • Team member grids
│       • Pricing tables
│       • FAQs
│
└─ NO → Keep in child theme
    │
    ├─ Client-specific logic
    ├─ One-off customization
    ├─ Highly specific to client's business
    │
    └─ Examples:
        • Client's custom calculator
        • Client-specific data display
        • Integration with client's API
        • Client-only workflow blocks
```

---

## Example: Real-World Scenarios

### Scenario 1: Building a Hero Block

**First time building hero for Client A:**
1. Build in `client-a-theme/blocks/hero-section/`
2. Test and perfect for Client A
3. Client approves

**Making it reusable:**
1. Generalize the block (remove Client A colors, make configurable)
2. Move to `mbn-theme/blocks/hero-section/`
3. Commit to parent theme
4. Create release v1.2.0

**Using in Client B:**
1. Client B's theme inherits from mbn-theme v1.2.0
2. Hero block available out of the box
3. Client B customizes colors via theme settings
4. No need to rebuild the block!

### Scenario 2: Custom Navigation

**Client A needs mega menu:**
1. Register custom menu in `client-a-theme/functions.php`
2. Build custom mega menu block in child theme
3. This stays in child theme (client-specific)

**If other clients need mega menus:**
1. Generalize the mega menu block
2. Contribute to mbn-theme
3. Each client can now use it with their own menus

### Scenario 3: Global Navigation Update

**You improve the site navbar:**
1. Update in `mbn-theme/blocks/site-navbar/`
2. Create release v1.2.1
3. All child themes can upgrade to get improvements
4. Each client's navbar gets better automatically!

---

## Repository Structure

```
mbn-theme/  (Parent - version controlled)
├── blocks/                 # Shared blocks
│   ├── hero-section/
│   ├── contact-form-section/
│   ├── site-navbar/
│   └── ...
├── docs/                   # Global documentation
├── scripts/                # Helper scripts
└── child-theme-starter/    # Template for new projects

client-a-theme/  (Child - separate repo)
├── blocks/                 # Client A custom blocks
│   └── client-a-calculator/
├── style.css               # Client A branding
└── functions.php           # Client A features

client-b-theme/  (Child - separate repo)
├── blocks/                 # Client B custom blocks
│   └── client-b-portfolio/
├── style.css               # Client B branding
└── functions.php           # Client B features
```

---

## Version Management

### Parent Theme (mbn-theme)

```bash
# Release new version with new blocks
php scripts/bump-version.php 1.2.0
git commit -m "feat: add testimonial slider block"
git tag -a v1.2.0 -m "Release v1.2.0"
git push origin master --tags
```

### Child Themes

```bash
# Lock to specific parent version
cd mbn-theme
git checkout v1.1.0  # Stable version for this project

# When ready to upgrade
git checkout v1.2.0  # Get new features
npm run build
# Test thoroughly before deploying
```

---

## Best Practices

### For mbn-theme (Parent) Development

1. ✅ **Keep it generic** - No client-specific code
2. ✅ **Document blocks** - Clear usage examples
3. ✅ **Make blocks configurable** - Use attributes, not hardcoded values
4. ✅ **Test across projects** - Ensure compatibility
5. ✅ **Version properly** - Follow semantic versioning
6. ✅ **Code standards** - Run `composer run lint` before commit
7. ✅ **Build system** - Ensure blocks compile correctly

### For Child Theme Development

1. ✅ **Start from parent version tag** - Not master
2. ✅ **Use parent blocks first** - Don't rebuild what exists
3. ✅ **Override sparingly** - Only when necessary
4. ✅ **Consider contribution** - Can this be reused?
5. ✅ **Test parent updates** - On staging before production
6. ✅ **Document customizations** - In child theme README
7. ✅ **Version control separately** - Independent repo for each client

---

## Common Questions

### Q: I built a block for Client A. Should it go in parent?

**If it's generic enough to be used by other clients → YES**
- Example: Testimonial slider, pricing table, team grid

**If it's specific to Client A's business → NO**
- Example: Client A's custom calculator, their API integration

### Q: Can I modify parent blocks in child theme?

**Yes, through CSS overrides and hooks:**
```css
/* Child theme style.css */
.wp-block-mbn-theme-hero-section {
    /* Override parent styles */
}
```

**For major changes, consider:**
- Forking the block into child theme
- Or contributing improvements back to parent

### Q: When should I update the parent version?

**For child projects:**
- When you need new blocks from parent
- For bug fixes and improvements
- During major project phases (not mid-sprint)
- Always test on staging first

### Q: How do I share blocks between my child themes?

**Don't!** Share through parent theme:
1. Generalize the block
2. Add to mbn-theme
3. Create release
4. Both child themes inherit from parent

---

## Resources

- **[Version Releasing Guide](VERSIONING.md)** - Creating mbn-theme releases
- **[Child Theme Guide](CHILD-THEME-GUIDE.md)** - Complete child theme development
- **[Release Checklist](RELEASE-CHECKLIST.md)** - Pre-release verification
- **[Deployment Guide](DEPLOYMENT.md)** - Deploying child themes

---

## Summary

**Simple Rule:**

🌐 **Global & Reusable** → mbn-theme (parent)  
🎨 **Client-Specific** → child theme

Keep the parent clean and generic. Build client projects in child themes. Contribute reusable work back to the parent. Everyone wins! 🎉
