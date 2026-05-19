# MBN Theme - Child Theme Quick Reference

## Create a New Child Theme

### Windows (PowerShell)
```powershell
.\scripts\create-child-theme.ps1 client-project-theme "Client Project Theme"
```

### Linux/Mac (PHP)
```bash
php scripts/create-child-theme.php client-project-theme "Client Project Theme"
```

### Manual Copy
```bash
cd wp-content/themes/
cp -r mbn-theme/child-theme-starter client-project-theme
```

---

## Set Up Parent Theme

```bash
cd mbn-theme
git fetch --all --tags
git checkout v1.1.0  # Use stable version
composer install --no-dev
npm install
npm run build
```

---

## Child Theme Structure

```
client-project-theme/
├── style.css          # Theme info + custom styles
├── functions.php      # Custom functions
├── screenshot.png     # Theme preview
└── README.md         # Documentation
```

---

## Common Customizations

### Custom CSS (style.css)
```css
.site-header {
    background-color: #custom-color;
}
```

### Custom Function (functions.php)
```php
function my_custom_post_type() {
    register_post_type('portfolio', array(
        'public' => true,
        'label'  => 'Portfolio'
    ));
}
add_action('init', 'my_custom_post_type');
```

### Override Parent Template
Create file with same path:
```
child-theme/template-parts/header-template.php
```

### Custom Page Template
```php
// template-landing.php
<?php
/**
 * Template Name: Landing Page
 */
get_header();
// Your code
get_footer();
?>
```

---

## Update Parent Version

```bash
cd mbn-theme
git fetch --all --tags
git checkout v1.2.0
composer install --no-dev
npm install
npm run build
```

---

## Documentation

- **Full Guide**: [docs/CHILD-THEME-GUIDE.md](CHILD-THEME-GUIDE.md)
- **Starter Template**: [child-theme-starter/](../child-theme-starter/)
- **Parent Versions**: [GitHub Releases](https://github.com/MBNDEV/mbn-theme/releases)
