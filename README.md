# MBN Theme

Default WordPress theme baseline for MBN projects.

## Theme Details

- Theme Name: `MBN Theme`
- Theme URI: https://github.com/MBNDEV/mbn-theme
- Description: `Default MBN WordPress theme with Gutenberg-first workflow`
- Version: `1.1.0`
- Author: `My Biz Niche`
- Author URI: https://www.mybizniche.com/
- License: `GPL2` - https://www.gnu.org/licenses/gpl-2.0.html
- Text Domain: `mbn-theme`

## Overview

This repository contains a WordPress theme built with:

- native Gutenberg block support
- Tailwind CSS styling
- Composer for PHP tooling
- npm for frontend tooling
- a Block Template sync system for version-controlled templates

## Latest Features

- Auto-discovery and registration of blocks from `build/blocks/` using `block.json`
- Unified sync tooling for templates, pages, and nav menus
- Safer imports with 3 import modes:
   - Skip existing
   - Update existing
   - Create copy
- Import safety improvements:
   - Nav import rollback/snapshot protection on destructive operations
   - Safer JSON/template parsing for file imports
- Sync import password protection enabled by default on `staging` and `production`
- Sync password source fallback order:
   - `CUSTOM_THEME_SYNC_PASSWORD` constant in `wp-config.php`
   - Environment variable `CUSTOM_THEME_SYNC_PASSWORD`
   - Theme root `.env` value (`CUSTOM_THEME_SYNC_PASSWORD=...`)
- Cleaner admin sync UIs focused on essential actions

## Requirements

- WordPress 5.8+ (or latest supported)
- PHP version compatible with your WordPress install
- Node.js and npm for asset builds
- Composer for PHP dependency management

## Installation

1. Copy or clone this theme into `wp-content/themes/mbn-theme`
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install Node dependencies:
   ```bash
   npm install
   ```
4. Build assets for production:
   ```bash
   npm run build
   ```
5. Activate the theme in WordPress Admin: **Appearance > Themes**

## Development

### Frontend Development

- Start the local development build/watch process:
  ```bash
  npm run start
  ```
- Build production assets:
  ```bash
  npm run build
  ```

### PHP / Theme Development

- Composer manages PHP tooling and packages.
- Autoloading is configured in `functions.php`.
- Theme logic and helpers are organized in `inc/`.

### Block Development

This theme ships with Gutenberg block support and a dedicated block folder.
See `blocks/README.md` for block-specific development details.

## Project Structure

- `assets/` - compiled CSS, JS, images, icons
- `blocks/` - Gutenberg block code and documentation
- `inc/` - PHP includes and theme helper files
- `template-parts/` - reusable template partials and block templates
- `page-templates/` - classic WordPress page templates
- `resources/css/` - source CSS assets
- `scripts/` - utility scripts for versioning and security

## Build & Linting

- Install dependencies: `composer install && npm install`
- Build assets: `npm run build`
- Start dev mode: `npm run start`
- Run PHP coding standards: `composer run lint`
- Fix linting issues: `composer run lint:fix`

## Block Template Sync System

The theme includes a template sync mechanism for keeping Block Templates in sync between database and files.

- `template-parts/` stores header, footer, and layout block templates
- `page-templates/` contains classic PHP page templates
- Sync tools are available in the WordPress admin to export/import templates

## Production Password Protection for Sync Imports

Import actions for Page Sync, Nav Menu Sync, and Template Sync support password protection on staging and production by default.

### Configure in `wp-config.php`

Add a strong secret for staging/production environments:

```php
define( 'CUSTOM_THEME_SYNC_PASSWORD', 'replace-with-a-strong-unique-password' );
```

You can also provide the value via environment variable:

```text
CUSTOM_THEME_SYNC_PASSWORD=replace-with-a-strong-unique-password
```

Or place it in a theme root `.env` file:

```text
CUSTOM_THEME_SYNC_PASSWORD=replace-with-a-strong-unique-password
```

### Default behavior

- Password is required by default when `wp_get_environment_type()` is `staging` or `production`.
- Import is blocked if password is missing or incorrect.
- If no password is configured while protection is required, imports are blocked.

### Related admin tools

- Tools -> Page Content Sync (import)
- Tools -> Nav Menu Sync (import)
- Block Templates -> Sync Tools (import)

### Optional customization

Developers can override whether password is required using the `custom_theme_sync_password_required` filter.

## Useful Links

- `CHANGELOG.md` - release notes and version history
- `docs/DEPLOYMENT.md` - deployment guide
- `docs/DEPLOYMENT_CHECKLIST.md` - deployment checklist
- `docs/VERSIONING.md` - versioning workflow
- `docs/RELEASE-CHECKLIST.md` - release process
- `SECURITY.md` - security policy and guidance
- `blocks/README.md` - block development documentation

## Notes

This README is intended for theme maintainers and developers working with the WordPress theme. For environment-specific deployment and sync workflows, refer to the `docs/` directory.
