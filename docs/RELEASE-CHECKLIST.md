# Release Checklist

Use this checklist when preparing and publishing a new version of MBN Theme.

## Pre-Release Planning

### Determine Version Number

- [ ] Review changes since last release
- [ ] Determine version type:
  - [ ] **PATCH** (1.0.x) - Bug fixes only
  - [ ] **MINOR** (1.x.0) - New features, backward compatible
  - [ ] **MAJOR** (x.0.0) - Breaking changes
- [ ] Document chosen version: `________________`

### Code Quality

- [ ] All features are complete and merged
- [ ] All tests pass locally
- [ ] Code follows WordPress coding standards (`composer run lint`)
- [ ] No console errors or warnings
- [ ] PHP errors/warnings resolved
- [ ] Cross-browser testing completed:
  - [ ] Chrome
  - [ ] Firefox
  - [ ] Safari
  - [ ] Edge
- [ ] Mobile responsive testing completed
- [ ] Accessibility testing completed

### Testing Environments

- [ ] Tested on staging environment
- [ ] Tested with WordPress latest version
- [ ] Tested with PHP 8.2+
- [ ] Tested with common plugins:
  - [ ] Gravity Forms
  - [ ] WooCommerce (if applicable)
  - [ ] Yoast SEO
- [ ] No conflicts with other themes/plugins
- [ ] Fresh install test completed

---

## Documentation

### Update CHANGELOG.md

- [ ] Move unreleased items to new version section
- [ ] Add release date (YYYY-MM-DD format)
- [ ] Organize changes by category:
  - [ ] Added
  - [ ] Changed
  - [ ] Deprecated
  - [ ] Removed
  - [ ] Fixed
  - [ ] Security
- [ ] Update comparison links at bottom
- [ ] Verify no typos or formatting issues

### Update Other Documentation

- [ ] README.md reflects new features
- [ ] Update any tutorial/guide documents
- [ ] Update screenshots if UI changed
- [ ] Verify all documentation links work
- [ ] Update API documentation (if applicable)

---

## Version Bump Process

### Run Version Bump Script

```bash
# Dry run first to preview changes
php scripts/bump-version.php [VERSION] --dry-run

# Actually update files
php scripts/bump-version.php [VERSION]
```

### Verify Version Updates

- [ ] `style.css` - Theme header version updated
- [ ] `package.json` - Version field updated
- [ ] `README.md` - Version number updated
- [ ] `CHANGELOG.md` - Version section with date added

### Manual Verification

```bash
# Check all version references
grep -r "VERSION_NUMBER" style.css package.json README.md CHANGELOG.md
```

- [ ] All version numbers match
- [ ] No old version numbers remain

---

## Build & Commit

### Build Production Assets

```bash
# Clean previous builds
rm -rf assets/build/* build/*

# Install fresh dependencies
composer install --no-dev --optimize-autoloader
npm ci

# Build production assets
npm run build
```

- [ ] Tailwind CSS built successfully
- [ ] Gutenberg blocks compiled
- [ ] No build errors or warnings
- [ ] Verify build file sizes are reasonable

### Create Git Commit

```bash
git status
git add -A
git commit -m "chore: bump version to [VERSION]"
```

- [ ] Commit message follows format
- [ ] All changed files included
- [ ] No unintended files committed
- [ ] Commit pushed to correct branch

### Create Git Tag

```bash
git tag -a v[VERSION] -m "Release v[VERSION]"
git push origin main --tags
```

- [ ] Tag follows format `v[VERSION]` (e.g., v1.1.0)
- [ ] Tag message is descriptive
- [ ] Tag pushed to remote repository

---

## GitHub Release

### Automated Release (Recommended)

The tag push should automatically trigger the release workflow.

- [ ] Monitor [GitHub Actions](https://github.com/MBNDEV/mbn-theme/actions)
- [ ] Workflow completes successfully
- [ ] Release appears in [Releases page](https://github.com/MBNDEV/mbn-theme/releases)
- [ ] ZIP file attached to release

### Manual Release (If Needed)

If automated release fails:

```bash
# Trigger workflow manually
gh workflow run release.yml -f version=[VERSION]
```

Or via GitHub UI:
1. [ ] Go to Actions → Create Release
2. [ ] Click "Run workflow"
3. [ ] Enter version number
4. [ ] Run workflow

### Verify Release

- [ ] Release appears on GitHub Releases page
- [ ] Release title: "MBN Theme v[VERSION]"
- [ ] Tag linked correctly
- [ ] Changelog notes included
- [ ] ZIP file attached and downloadable
- [ ] ZIP file contains correct files
- [ ] ZIP file size is reasonable

---

## Post-Release Verification

### Test Release Package

```bash
# Download and test the release ZIP
wget https://github.com/MBNDEV/mbn-theme/releases/download/v[VERSION]/mbn-theme-v[VERSION].zip
unzip mbn-theme-v[VERSION].zip
cd mbn-theme/

# Verify critical files exist
ls -la style.css functions.php assets/build/
```

- [ ] ZIP extracts without errors
- [ ] All required files present
- [ ] No development files included
- [ ] File permissions correct

### Test Plugin Update Checker

- [ ] Go to a test WordPress site using the theme
- [ ] Navigate to Appearance → Themes
- [ ] Force update check (or wait for automatic check)
- [ ] Verify update notification appears
- [ ] Click "Update" and verify it completes
- [ ] Verify site still works after update

### Git Repository Status

```bash
git status
git log --oneline -5
git tag -l | sort -V | tail -5
```

- [ ] Working directory clean
- [ ] Latest commit is version bump
- [ ] Tag exists and points to correct commit
- [ ] No uncommitted changes

---

## Communication

### Announce Release

- [ ] Post release announcement (if applicable):
  - [ ] Slack/Discord channel
  - [ ] Email to team
  - [ ] Client notification (if applicable)
- [ ] Highlight important changes
- [ ] Note any breaking changes
- [ ] Provide update instructions

### Developer Documentation

- [ ] Update internal wiki/docs (if applicable)
- [ ] Notify developers of new version
- [ ] Share migration guide (if breaking changes)

---

## For Major Releases Only

### Additional Major Release Steps

- [ ] Create migration guide for breaking changes
- [ ] Update all example/demo sites
- [ ] Record video tutorial (if needed)
- [ ] Update marketing materials
- [ ] Schedule release announcement
- [ ] Prepare support documentation for common issues
- [ ] Create rollback plan

### Deprecation Notices

- [ ] Add deprecation warnings for old features
- [ ] Document deprecated features clearly
- [ ] Provide alternative solutions
- [ ] Set timeline for removal

---

## Rollback Plan (If Issues Found)

### Immediate Actions

```bash
# Revert to previous version
git revert [COMMIT_HASH]
git push origin main

# Delete problematic tag
git tag -d v[VERSION]
git push origin :refs/tags/v[VERSION]

# Delete GitHub release
gh release delete v[VERSION] --yes
```

- [ ] Identify the issue
- [ ] Document the problem
- [ ] Notify users if release was already deployed
- [ ] Create hotfix branch
- [ ] Fix issue and create new release

---

## Release Metrics (Optional)

Track these metrics for continuous improvement:

- Release date: `________________`
- Time to prepare release: `________________`
- Build time: `________________`
- Any issues encountered: `________________`
- Number of changes: `________________`
- Lines of code changed: `________________`

---

## Sign-Off

Release prepared by: `________________`  
Date: `________________`  
Release version: `________________`  
GitHub release URL: `________________`

**Final Checklist:**
- [ ] All items above completed
- [ ] No known critical bugs
- [ ] Team notified
- [ ] Release published
- [ ] Documentation updated

---

## Quick Release Commands

```bash
# Complete release process in order:
php scripts/bump-version.php [VERSION]
git add -A
git commit -m "chore: bump version to [VERSION]"
git tag -a v[VERSION] -m "Release v[VERSION]"
git push origin main --tags

# Monitor release workflow
gh run watch

# View release
gh release view v[VERSION]
```

---

## Resources

- [Versioning Guide](VERSIONING.md)
- [GitHub Actions](https://github.com/MBNDEV/mbn-theme/actions)
- [GitHub Releases](https://github.com/MBNDEV/mbn-theme/releases)
- [Deployment Guide](DEPLOYMENT.md)
