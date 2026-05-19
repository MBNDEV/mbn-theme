# Version Releasing Guide

## Overview

MBN Theme uses **Semantic Versioning** and **GitHub Releases** to manage versions. This allows developers to use specific stable versions instead of always pulling from the master branch.

## Quick Start for Developers

### Using a Specific Version

```bash
# Clone the repository
git clone https://github.com/MBNDEV/mbn-theme.git

# List available versions
git tag -l

# Checkout a specific version
git checkout v1.0.3

# Or checkout the latest release
git checkout $(git describe --tags --abbrev=0)
```

### Updating to Latest Release

```bash
# Fetch all tags and updates
git fetch --all --tags

# Checkout the latest release
git checkout $(git describe --tags --abbrev=0)

# Install dependencies
composer install --no-dev
npm install
npm run build
```

### For WordPress Sites with Plugin Update Checker

WordPress sites using this theme with Plugin Update Checker enabled will **automatically receive notifications** when new versions are available. They can update directly from the WordPress admin dashboard.

---

## For Theme Maintainers: Creating a Release

### 1. Version Numbering (Semantic Versioning)

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR** (e.g., 2.0.0) - Incompatible changes, breaking changes
- **MINOR** (e.g., 1.1.0) - New features, backward-compatible  
- **PATCH** (e.g., 1.0.1) - Bug fixes, backward-compatible

### 2. Prepare the Release

#### Step 1: Update CHANGELOG.md

Edit `CHANGELOG.md` and move changes from `[Unreleased]` to a new version section:

```markdown
## [Unreleased]

## [1.1.0] - 2026-05-20

### Added
- New hero block with animations
- Custom post type for testimonials

### Fixed
- Mobile navigation toggle issue
- CSS build optimization
```

#### Step 2: Bump Version Numbers

Use the automated version bump script:

```bash
# Auto-increment patch (1.0.2 → 1.0.3)
php scripts/bump-version.php patch

# Auto-increment minor (1.0.2 → 1.1.0)
php scripts/bump-version.php minor

# Auto-increment major (1.0.2 → 2.0.0)
php scripts/bump-version.php major

# Or specify exact version
php scripts/bump-version.php 1.5.0

# Dry run to preview changes
php scripts/bump-version.php 1.5.0 --dry-run
```

This script automatically updates:
- `style.css` - Theme version header
- `package.json` - NPM version field
- `README.md` - Version documentation
- `CHANGELOG.md` - Release date

#### Step 3: Review Changes

```bash
# Review all changes
git diff

# Make sure version numbers are correct
grep -r "1.1.0" style.css package.json README.md CHANGELOG.md
```

#### Step 4: Commit and Tag

```bash
# Commit version bump
git add -A
git commit -m "chore: bump version to 1.1.0"

# Create annotated tag
git tag -a v1.1.0 -m "Release v1.1.0"

# Push to GitHub
git push origin main
git push origin v1.1.0
```

### 3. Create GitHub Release

#### Option A: Automatic (via GitHub Actions)

Pushing a tag automatically triggers the release workflow:

```bash
git push origin v1.1.0
```

The workflow will:
- ✅ Build production assets
- ✅ Create release package (ZIP file)
- ✅ Extract changelog notes
- ✅ Create GitHub Release with assets

#### Option B: Manual (via GitHub UI)

1. Go to [GitHub Releases](https://github.com/MBNDEV/mbn-theme/releases)
2. Click **"Draft a new release"**
3. Choose tag: `v1.1.0` or create new tag
4. Release title: `MBN Theme v1.1.0`
5. Copy release notes from CHANGELOG.md
6. Attach ZIP file (if needed)
7. Click **"Publish release"**

#### Option C: Manual Workflow Dispatch

Trigger the release workflow manually from GitHub Actions:

1. Go to [Actions → Create Release](https://github.com/MBNDEV/mbn-theme/actions/workflows/release.yml)
2. Click **"Run workflow"**
3. Enter version number (e.g., `1.1.0`)
4. Click **"Run workflow"**

---

## Branching Strategy

```
main (stable, production-ready)
  ├── v1.0.0 (tag)
  ├── v1.0.1 (tag)
  └── v1.1.0 (tag)

develop (active development)
  └── feature branches
```

### Workflow

1. **Development**: Work on `develop` branch or feature branches
2. **Testing**: Test thoroughly on staging environment
3. **Release prep**: Bump version, update changelog
4. **Merge**: Merge `develop` → `main` (or create release branch)
5. **Tag**: Create version tag on `main`
6. **Release**: Push tag to trigger GitHub Release

---

## Version Verification

### Check Current Version

```bash
# From style.css
grep "Version:" style.css

# From package.json
node -p "require('./package.json').version"

# From git tags
git describe --tags --abbrev=0
```

### Verify Release Package

```bash
# List releases
gh release list

# Download latest release
gh release download

# View release details
gh release view v1.1.0
```

---

## For Site Developers (Jonathan's Workflow)

### Initial Setup

```bash
# Clone theme repository
cd wp-content/themes/
git clone https://github.com/MBNDEV/mbn-theme.git

# Checkout a stable version (recommended)
cd mbn-theme
git checkout v1.0.2

# Install dependencies
composer install --no-dev
npm install
npm run build
```

### Staying Updated

```bash
# Check for new releases
git fetch --all --tags
git tag -l | sort -V

# Update to latest release
git checkout main
git pull origin main
git checkout $(git describe --tags --abbrev=0)

# Rebuild assets
composer install --no-dev
npm install  
npm run build
```

### Lock to Specific Version

For production sites, it's recommended to lock to a specific version:

```bash
# Use a specific tested version
git checkout v1.0.2

# Stay on this version (don't pull updates)
# Only update when you're ready to test a new version
```

### Update to Newer Version

```bash
# List available versions
git tag -l | sort -V

# Checkout new version
git checkout v1.1.0

# Install dependencies (versions may have changed)
composer install --no-dev
npm install
npm run build

# Test thoroughly before deploying
```

---

## Plugin Update Checker Integration

MBN Theme includes [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) which automatically checks GitHub for new releases.

### How It Works

1. Theme checks GitHub for new releases periodically
2. When a new release is found, WordPress shows update notification
3. Users can update directly from WordPress Admin → Appearance → Themes
4. Update downloads the latest release ZIP from GitHub

### Configuration

The update checker is configured in `functions.php`:

```php
PucFactory::buildUpdateChecker(
    'https://github.com/MBNDEV/mbn-theme',
    get_theme_file_path('style.css'),
    'mbn-theme'
);
```

### For Private Repositories

If your repository is private, you'll need to add a GitHub token:

```php
$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/MBNDEV/mbn-theme',
    get_theme_file_path('style.css'),
    'mbn-theme'
);
$updateChecker->setAuthentication('your-github-token-here');
```

---

## Troubleshooting

### Version Mismatch

If versions don't match across files:

```bash
# Re-run version bump script
php scripts/bump-version.php 1.1.0

# Verify all files updated
git diff style.css package.json README.md CHANGELOG.md
```

### GitHub Release Failed

Check the [Actions logs](https://github.com/MBNDEV/mbn-theme/actions) for errors.

Common issues:
- Version already exists: Choose a new version number
- Build failed: Fix build errors locally first
- Missing CHANGELOG entry: Update CHANGELOG.md

### Update Checker Not Working

1. Verify GitHub repository is public (or token is configured)
2. Check that releases are published (not draft)
3. Verify version in `style.css` matches GitHub release tag
4. Check WordPress debug log for update checker errors

---

## Best Practices

1. **Always test** before creating a release
2. **Update CHANGELOG.md** with clear descriptions
3. **Use semantic versioning** consistently
4. **Tag releases** on stable commits only
5. **Never force-push** to main or tags
6. **Test updates** on staging before production
7. **Document breaking changes** clearly
8. **Keep release notes** user-friendly

---

## Additional Resources

- [Semantic Versioning](https://semver.org/)
- [Keep a Changelog](https://keepachangelog.com/)
- [GitHub Releases Docs](https://docs.github.com/en/repositories/releasing-projects-on-github)
- [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker)

---

## Quick Reference Commands

```bash
# Create a new release (full process)
php scripts/bump-version.php 1.1.0
git add -A
git commit -m "chore: bump version to 1.1.0"
git tag -a v1.1.0 -m "Release v1.1.0"
git push origin main --tags

# Checkout latest release
git checkout $(git describe --tags --abbrev=0)

# List all versions
git tag -l | sort -V

# View changelog for version
sed -n "/## \[1.1.0\]/,/## \[/p" CHANGELOG.md
```
