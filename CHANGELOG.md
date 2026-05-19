# Changelog

All notable changes to the MBN Theme will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-05-19

### Added
- Version releasing system with semantic versioning support
- Automated GitHub Actions workflow for creating releases
- Version bump script (`scripts/bump-version.php`) for managing version updates
- Comprehensive versioning documentation:
  - `docs/VERSIONING.md` - Complete guide for creating and using releases
  - `docs/RELEASE-CHECKLIST.md` - Step-by-step release checklist
  - `docs/VERSION-RELEASING-SYSTEM.md` - System overview and implementation details
  - `docs/QUICK-START-VERSIONING.md` - Quick reference guide
- Version field in `package.json` for NPM compatibility

### Changed
- Updated README.md with version releasing instructions and workflow
- Enhanced CHANGELOG.md with proper semantic versioning structure

## [1.0.2] - 2026-05-19

### Features
- Custom Gutenberg blocks with React and Tailwind CSS
- Block Registry system for dynamic block loading
- Plugin Update Checker integration for automatic WordPress updates
- GitHub-based update detection system
- Responsive Tailwind CSS compilation with PostCSS
- Block template sync system for deploying templates across environments
- Automated deployment workflows (staging and production)
- Theme options page with native WordPress settings
- Custom post meta boxes
- Font presets and CSS variables system
- Custom HTML injection support
- Widget area auto-loader
- Block templates (header/footer) management system
- Page template synchronization
- Section background utilities with responsive images
- Reusable block patterns
- Template import/export tools
- Navigation menu sync via Git
- Scroll animation system with jQuery
- Gravity Forms integration with conditional CSS loading
- WordPress coding standards with PHPCS/WPCS
- Browser-sync for development hot-reload

### Blocks Included


### Technical Stack
- PHP 8.2+ compatible
- WordPress latest version support
- Node.js 20+ and NPM for asset building
- Composer for PHP dependency management
- Webpack for block compilation
- Tailwind CSS 3.4+ for styling
- React for block development
- WordPress Block Editor components
- Husky for Git hooks
- PHPCS/WPCS for code quality

### Documentation
- Deployment guide (`docs/DEPLOYMENT.md`)
- Deployment checklist (`docs/DEPLOYMENT_CHECKLIST.md`)
- Domain settings guide (`docs/DOMAIN-SETTINGS-GUIDE.md`)
- Image architecture documentation (`docs/IMAGE-ARCHITECTURE.md`)
- Logging documentation (`docs/LOGGING.md`)
- Scroll animations guide (`docs/SCROLL-ANIMATIONS.md`)
- Security documentation (`docs/SECURITY.md`)
- SOP guide (`docs/sop-guide.md`)
- URL domain management (`docs/URL-DOMAIN-MANAGEMENT.md`)
- URL quick reference (`docs/URL-QUICK-REFERENCE.md`)

### Configuration
- GitHub repository: `https://github.com/MBNDEV/mbn-theme`
- Text domain: `mbn-theme`
- License: GPL2

## Version Format

We use **Semantic Versioning** (MAJOR.MINOR.PATCH):

- **MAJOR** version when you make incompatible API changes
- **MINOR** version when you add functionality in a backward compatible manner  
- **PATCH** version when you make backward compatible bug fixes

## How to Update This Changelog

When creating a new release:

1. Move items from `[Unreleased]` to a new version section
2. Add the release date in YYYY-MM-DD format
3. Create a new `[Unreleased]` section at the top
4. Use these categories:
   - **Added** for new features
   - **Changed** for changes in existing functionality
   - **Deprecated** for soon-to-be removed features
   - **Removed** for now removed features
   - **Fixed** for any bug fixes
   - **Security** for vulnerability fixes

## Links

[Unreleased]: https://github.com/MBNDEV/mbn-theme/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/MBNDEV/mbn-theme/compare/v1.0.2...v1.1.0
[1.0.2]: https://github.com/MBNDEV/mbn-theme/releases/tag/v1.0.2
