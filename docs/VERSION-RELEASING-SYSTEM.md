# MBN Theme Version Releasing System - Implementation Summary

## Overview

The MBN Theme now has a complete **Version Releasing System** that allows developers to use specific stable versions instead of always relying on the master branch. This system uses **Semantic Versioning**, **GitHub Releases**, and **automated workflows** to streamline the release process.

## What Was Implemented

### 1. Version Management Files

#### CHANGELOG.md
- **Purpose**: Track all changes between versions
- **Format**: Follows [Keep a Changelog](https://keepachangelog.com/) standard
- **Usage**: Updated with each release to document new features, changes, and fixes
- **Location**: Root of theme directory

#### scripts/bump-version.php
- **Purpose**: Automated script to update version numbers across all theme files
- **Features**:
  - Auto-increment versions (major, minor, patch)
  - Updates style.css, package.json, README.md, CHANGELOG.md
  - Dry-run mode for previewing changes
  - Colorized CLI output
  - Validation and error checking
- **Usage**:
  ```bash
  php scripts/bump-version.php 1.1.0
  php scripts/bump-version.php patch  # Auto-increment
  php scripts/bump-version.php minor --dry-run
  ```

### 2. GitHub Workflows

#### .github/workflows/release.yml
- **Purpose**: Automated release creation when tags are pushed
- **Triggers**: 
  - Push of version tags (v*.*.*)
  - Manual workflow dispatch
- **Process**:
  1. Validates version numbers
  2. Builds production assets (npm, composer)
  3. Creates release package ZIP
  4. Extracts changelog notes
  5. Creates GitHub Release with assets
- **Result**: Complete release with downloadable ZIP file

### 3. Documentation

#### docs/VERSIONING.md
- **Purpose**: Complete guide for creating and using version releases
- **Covers**:
  - How developers use specific versions
  - How to checkout and update versions
  - Complete release creation process
  - Branching strategy
  - Plugin Update Checker integration
  - Troubleshooting guide
  - Best practices

#### docs/RELEASE-CHECKLIST.md
- **Purpose**: Step-by-step checklist for creating releases
- **Covers**:
  - Pre-release planning
  - Code quality checks
  - Testing requirements
  - Documentation updates
  - Version bump process
  - Build and commit steps
  - GitHub release creation
  - Post-release verification
  - Communication steps
  - Rollback procedures

### 4. Version Updates

#### package.json
- Added `"version": "1.0.2"` field
- Enables npm version management
- Automatically updated by bump-version.php script

#### README.md
- Added comprehensive "Version Releasing" section
- Quick start commands for developers
- Links to detailed documentation
- Usage examples

## How It Works

### For Developers Using MBN Theme (Like Jonathan)

#### Initial Setup
```bash
# Clone the repository
git clone https://github.com/MBNDEV/mbn-theme.git
cd mbn-theme

# Checkout a specific stable version
git checkout v1.0.2

# Install dependencies
composer install --no-dev
npm install
npm run build
```

#### Staying Updated
```bash
# Fetch latest releases
git fetch --all --tags

# See available versions
git tag -l

# Checkout latest release
git checkout $(git describe --tags --abbrev=0)

# Rebuild
composer install --no-dev
npm install
npm run build
```

#### Benefits for Jonathan
- ✅ Works with stable, tested versions
- ✅ Controls when to update (not forced by master changes)
- ✅ Can test new versions before deploying
- ✅ Can easily rollback if issues occur
- ✅ Clear version history and changelogs

### For Theme Maintainers (You)

#### Creating a Release

**Step 1: Prepare Changes**
- Develop features on `develop` branch or feature branches
- Test thoroughly
- Update CHANGELOG.md with changes

**Step 2: Bump Version**
```bash
# Use the automated script
php scripts/bump-version.php 1.1.0

# Or auto-increment
php scripts/bump-version.php minor
```

**Step 3: Commit and Tag**
```bash
git add -A
git commit -m "chore: bump version to 1.1.0"
git tag -a v1.1.0 -m "Release v1.1.0"
git push origin main --tags
```

**Step 4: Automated Release**
- GitHub Actions automatically triggers
- Builds production assets
- Creates release with ZIP file
- WordPress sites get update notification

#### What Gets Updated Automatically
- ✅ style.css - WordPress theme version header
- ✅ package.json - NPM version field
- ✅ README.md - Version documentation
- ✅ CHANGELOG.md - Release date added

## Semantic Versioning

The theme follows [Semantic Versioning](https://semver.org/):

```
MAJOR.MINOR.PATCH (e.g., 1.2.3)
```

- **MAJOR** (2.0.0): Breaking changes, incompatible updates
- **MINOR** (1.1.0): New features, backward compatible
- **PATCH** (1.0.1): Bug fixes, backward compatible

### Examples
- `1.0.2 → 1.0.3`: Bug fix release
- `1.0.2 → 1.1.0`: New feature added
- `1.0.2 → 2.0.0`: Breaking changes introduced

## Plugin Update Checker Integration

The theme already has Plugin Update Checker configured:

```php
PucFactory::buildUpdateChecker(
    'https://github.com/MBNDEV/mbn-theme',
    get_theme_file_path('style.css'),
    'mbn-theme'
);
```

### How It Works
1. WordPress periodically checks GitHub for new releases
2. When a new tag/release is created, WordPress shows update notification
3. Users can click "Update" in WordPress Admin
4. Theme downloads and installs from GitHub Release ZIP

### Benefits
- ✅ Automatic update notifications
- ✅ One-click updates from WordPress admin
- ✅ No manual file copying needed
- ✅ Works like official WordPress.org themes

## Workflow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Development Workflow                      │
└─────────────────────────────────────────────────────────────┘

1. DEVELOP
   ├── Work on develop branch
   ├── Create feature branches
   ├── Test on staging
   └── Update CHANGELOG.md

2. RELEASE PREP
   ├── Run: php scripts/bump-version.php 1.1.0
   ├── Review changes: git diff
   ├── Update CHANGELOG with details
   └── Test final build

3. COMMIT & TAG
   ├── git add -A
   ├── git commit -m "chore: bump version to 1.1.0"
   ├── git tag -a v1.1.0 -m "Release v1.1.0"
   └── git push origin main --tags

4. AUTOMATED RELEASE (GitHub Actions)
   ├── Checkout code
   ├── Build assets (npm, composer)
   ├── Create release package
   ├── Extract changelog
   └── Publish GitHub Release

5. DISTRIBUTION
   ├── Release available on GitHub
   ├── ZIP file downloadable
   ├── WordPress sites get notification
   └── Developers can checkout tag

6. DEVELOPERS USE IT
   ├── git checkout v1.1.0
   ├── composer install --no-dev
   ├── npm install && npm run build
   └── Deploy to production
```

## File Structure

```
mbn-theme/
├── CHANGELOG.md                      # Version history (NEW)
├── README.md                         # Updated with versioning info
├── style.css                         # Theme version header
├── package.json                      # Added version field
├── composer.json                     # Existing
├── functions.php                     # Plugin Update Checker config
│
├── scripts/
│   └── bump-version.php              # Version bump script (NEW)
│
├── docs/
│   ├── VERSIONING.md                 # Complete versioning guide (NEW)
│   └── RELEASE-CHECKLIST.md          # Release checklist (NEW)
│
└── .github/
    └── workflows/
        ├── release.yml                # Release automation (NEW)
        ├── deploy.yml                 # Existing deployment
        └── lint.yml                   # Existing linting
```

## Benefits of This System

### For Developers (Jonathan, etc.)
1. ✅ **Stability**: Use tested, stable versions
2. ✅ **Control**: Choose when to update
3. ✅ **Predictability**: Know exactly what version is running
4. ✅ **Testing**: Test new versions before deploying
5. ✅ **Rollback**: Easy to revert to previous version
6. ✅ **Documentation**: Clear changelog of what changed

### For Theme Maintainers (You)
1. ✅ **Automation**: Automated release creation
2. ✅ **Consistency**: Standardized versioning process
3. ✅ **Documentation**: Automatic changelog tracking
4. ✅ **Distribution**: Easy distribution via GitHub Releases
5. ✅ **Quality**: Checklist ensures nothing is forgotten
6. ✅ **Updates**: Automatic WordPress update notifications

### For Projects
1. ✅ **Reliability**: Production sites use stable releases
2. ✅ **Auditing**: Clear version history for compliance
3. ✅ **Collaboration**: Multiple developers can work in sync
4. ✅ **Deployment**: Confident deployments with known versions
5. ✅ **Maintenance**: Easy to track which sites use which versions

## Next Steps

### Immediate Actions

1. **Push to GitHub** (if not already done):
   ```bash
   git add -A
   git commit -m "feat: implement version releasing system"
   git push origin version-releasing
   ```

2. **Create Pull Request**:
   - Merge `version-releasing` → `main`
   - Review changes
   - Merge when ready

3. **Create First Official Release**:
   ```bash
   git checkout main
   git pull
   php scripts/bump-version.php 1.1.0
   git add -A
   git commit -m "chore: bump version to 1.1.0"
   git tag -a v1.1.0 -m "Release v1.1.0 - Version releasing system"
   git push origin main --tags
   ```

4. **Verify Release**:
   - Check GitHub Actions workflow completes
   - Verify release appears on GitHub
   - Test downloading and installing the ZIP

### For Jonathan and Other Developers

**Send them this message:**

> Hi Jonathan,
> 
> Great news! MBN Theme now uses version releases. Instead of always pulling from master, you can now use specific stable versions.
> 
> **To use a specific version:**
> ```bash
> cd wp-content/themes/mbn-theme
> git fetch --all --tags
> git checkout v1.0.2  # Or latest: $(git describe --tags --abbrev=0)
> composer install --no-dev
> npm install
> npm run build
> ```
> 
> **To update to a new release:**
> ```bash
> git fetch --all --tags
> git checkout v1.1.0
> composer install --no-dev
> npm install
> npm run build
> ```
> 
> See the full guide: [docs/VERSIONING.md](docs/VERSIONING.md)

### Ongoing Maintenance

1. **Always update CHANGELOG.md** when making changes
2. **Create releases** for all significant updates
3. **Follow semantic versioning** rules
4. **Use the release checklist** to ensure quality
5. **Test releases** on staging before production

## Quick Reference

### Version Bump Commands
```bash
# Specific version
php scripts/bump-version.php 1.1.0

# Auto-increment patch (1.0.2 → 1.0.3)
php scripts/bump-version.php patch

# Auto-increment minor (1.0.2 → 1.1.0)
php scripts/bump-version.php minor

# Auto-increment major (1.0.2 → 2.0.0)
php scripts/bump-version.php major

# Preview changes without modifying files
php scripts/bump-version.php 1.1.0 --dry-run
```

### Release Commands
```bash
# Complete release process
php scripts/bump-version.php 1.1.0
git add -A
git commit -m "chore: bump version to 1.1.0"
git tag -a v1.1.0 -m "Release v1.1.0"
git push origin main --tags

# View releases
gh release list

# View specific release
gh release view v1.1.0
```

### Developer Commands
```bash
# Checkout latest release
git fetch --all --tags
git checkout $(git describe --tags --abbrev=0)

# List all versions
git tag -l | sort -V

# Checkout specific version
git checkout v1.0.2
```

## Troubleshooting

### Version Mismatch Error
If the release workflow fails with version mismatch:
```bash
php scripts/bump-version.php 1.1.0
git add style.css package.json README.md CHANGELOG.md
git commit --amend --no-edit
git tag -f v1.1.0
git push origin main --tags --force
```

### Release Workflow Fails
1. Check [GitHub Actions logs](https://github.com/MBNDEV/mbn-theme/actions)
2. Verify all files build locally: `npm run build`
3. Check PHP syntax: `composer run lint`
4. Manually trigger workflow from GitHub Actions UI

### Plugin Update Checker Not Working
1. Verify repository is public (or add GitHub token for private)
2. Check releases are published (not draft)
3. Verify version in style.css matches tag
4. Check WordPress debug.log for errors

## Resources

- [Semantic Versioning](https://semver.org/)
- [Keep a Changelog](https://keepachangelog.com/)
- [GitHub Releases Documentation](https://docs.github.com/en/repositories/releasing-projects-on-github)
- [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker)

## Support

For questions or issues:
1. Read [docs/VERSIONING.md](docs/VERSIONING.md)
2. Check [docs/RELEASE-CHECKLIST.md](docs/RELEASE-CHECKLIST.md)
3. Review GitHub Actions workflow logs
4. Contact the development team

---

**System Status**: ✅ Fully Implemented and Ready to Use

**Current Version**: 1.0.2  
**Next Recommended Version**: 1.1.0 (includes version releasing system)  
**Implementation Date**: May 19, 2026
