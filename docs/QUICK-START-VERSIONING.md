# Quick Start: Version Releasing

## For Developers Using MBN Theme

### First Time Setup

```bash
# Clone repository
git clone https://github.com/MBNDEV/mbn-theme.git
cd mbn-theme

# Checkout latest stable version
git fetch --all --tags
git checkout $(git describe --tags --abbrev=0)

# Install and build
composer install --no-dev
npm install
npm run build
```

### Check for Updates

```bash
# Fetch latest tags
git fetch --all --tags

# List available versions
git tag -l | sort -V

# Checkout new version
git checkout v1.1.0

# Rebuild
composer install --no-dev
npm install
npm run build
```

---

## For Theme Maintainers

### Create a Release

```bash
# 1. Update CHANGELOG.md with your changes

# 2. Bump version (auto-updates all files)
php scripts/bump-version.php 1.1.0

# 3. Review changes
git diff

# 4. Commit and tag
git add -A
git commit -m "chore: bump version to 1.1.0"
git tag -a v1.1.0 -m "Release v1.1.0"

# 5. Push (triggers automated release)
git push origin main --tags
```

### Quick Commands

```bash
# Patch release (1.0.2 → 1.0.3)
php scripts/bump-version.php patch

# Minor release (1.0.2 → 1.1.0)
php scripts/bump-version.php minor

# Major release (1.0.2 → 2.0.0)
php scripts/bump-version.php major

# Preview changes (dry run)
php scripts/bump-version.php 1.1.0 --dry-run
```

---

## Documentation

- **[VERSIONING.md](VERSIONING.md)** - Complete guide
- **[RELEASE-CHECKLIST.md](RELEASE-CHECKLIST.md)** - Step-by-step checklist
- **[VERSION-RELEASING-SYSTEM.md](VERSION-RELEASING-SYSTEM.md)** - System overview
- **[CHANGELOG.md](../CHANGELOG.md)** - Version history

---

## Common Tasks

### Check Current Version
```bash
grep "Version:" style.css
```

### List All Releases
```bash
git tag -l | sort -V
```

### View Changelog for Version
```bash
sed -n "/## \[1.1.0\]/,/## \[/p" CHANGELOG.md
```

### Rollback to Previous Version
```bash
git checkout v1.0.2
composer install --no-dev
npm install
npm run build
```

### View Release on GitHub
```bash
gh release view v1.1.0
# Or visit: https://github.com/MBNDEV/mbn-theme/releases
```

---

## Need Help?

1. Read the [Versioning Guide](VERSIONING.md)
2. Check the [Release Checklist](RELEASE-CHECKLIST.md)
3. Review [System Documentation](VERSION-RELEASING-SYSTEM.md)
4. Contact the development team
