# Black Line Security Ops

Custom WordPress theme for My Biz Niche.

## Theme Details

- Theme Name: `Black Line Security Ops`
- Description: `Custom Theme for MBN`
- Version: `1.1.0`
- Author: `My Biz Niche`
- Theme URI: [https://github.com/MBNDEV/mbn-theme](https://github.com/MBNDEV/mbn-theme)
- Author URI: [https://www.mybizniche.com/](https://www.mybizniche.com/)
- License: `GPL2` - [GPL-2.0](https://www.gnu.org/licenses/gpl-2.0.html)
- Text Domain: `mbn-theme`

## Requirements

- WordPress (current supported version)
- PHP compatible with WordPress requirements
- Node.js & npm (for building Gutenberg blocks)
- Composer (for development tooling)

## Installation

1. Copy or clone this theme into `wp-content/themes/mbn-theme`.
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install Node dependencies:
   ```bash
   npm install
   ```
4. Build Gutenberg blocks:
   ```bash
   npm run build
   ```
5. In WordPress Admin, go to **Appearance > Themes** and activate **Black Line Guardian Fund Theme**.

## Child Theme Support

**Recommended for client projects:** Use child themes to customize MBN Theme for specific projects while keeping the parent theme intact.

### Quick Start with Child Themes

```bash
# Copy the starter template
cd wp-content/themes/
cp -r mbn-theme/child-theme-starter client-project-theme

# Customize style.css with your project details
# Edit functions.php for custom functionality
# Activate child theme in WordPress Admin
```

### Benefits of Child Themes

- ✅ **Safe updates** - Get parent theme updates without losing customizations
- ✅ **Project isolation** - Each project has its own customizations
- ✅ **Version control** - Lock to specific parent theme versions
- ✅ **Easy maintenance** - Multiple projects share one parent theme

### Documentation

- **[Child Theme Guide](docs/CHILD-THEME-GUIDE.md)** - Complete guide for child theme development
- **[Child Theme Starter](child-theme-starter/)** - Ready-to-use starter template

## Development

### Block Development

This theme uses **native WordPress Gutenberg blocks** with React and Tailwind CSS.

**Start development server with hot reload:**
```bash
npm run start
```

**Build for production:**
```bash
npm run build
```

See [blocks/README.md](blocks/README.md) for detailed block development guide.

### Figma to Gutenberg Blocks

This theme supports **Figma MCP integration** for converting designs directly to blocks.

**Quick Setup:**
1. Get your Figma Personal Access Token: https://www.figma.com/developers/api#access-tokens
2. Copy `.vscode/mcp-settings.json.template` to your MCP settings
3. Add your token to the configuration
4. See [.github/FIGMA_MCP_SETUP.md](.github/FIGMA_MCP_SETUP.md) for complete instructions

**Usage:**
```
@wp-gutenberg-dev Create a hero block from this Figma design:
https://www.figma.com/file/YOUR_FILE_ID
```

### PHP Development

This theme uses Composer autoloading for vendor packages.

- Primary package in use:
  - `yahnis-elsts/plugin-update-checker`
- Autoload is conditionally loaded in `functions.php` to avoid duplicate class loading.

## Update Checker

The theme includes GitHub-based update checks through Plugin Update Checker.

- Repository configured in code:
  - [https://github.com/MBNDEV/mbn-theme](https://github.com/MBNDEV/mbn-theme)
- Slug configured in code:
  - `mbn-theme`

## Version Releasing

MBN Theme uses **Semantic Versioning** and **GitHub Releases** to manage versions. This allows developers to use specific stable versions instead of always pulling from the master branch.

### For Developers Using This Theme

**Checkout a specific version:**
```bash
# List available versions
git tag -l

# Checkout a specific stable version
git checkout v1.0.2

# Or checkout the latest release
git checkout $(git describe --tags --abbrev=0)
```

**Update to latest release:**
```bash
git fetch --all --tags
git checkout $(git describe --tags --abbrev=0)
composer install --no-dev
npm install
npm run build
```

### For WordPress Sites

WordPress sites using this theme will automatically receive update notifications through Plugin Update Checker. Simply update from **WordPress Admin → Appearance → Themes**.

### For Theme Maintainers

**Create a new release:**
```bash
# Bump version and update files
php scripts/bump-version.php 1.1.0

# Commit and tag
git add -A
git commit -m "chore: bump version to 1.1.0"
git tag -a v1.1.0 -m "Release v1.1.0"
git push origin main --tags
```

The GitHub Actions workflow will automatically create a release with built assets.

### Documentation

- **[Versioning Guide](docs/VERSIONING.md)** - Complete guide for creating and using releases
- **[Release Checklist](docs/RELEASE-CHECKLIST.md)** - Step-by-step release checklist
- **[CHANGELOG.md](CHANGELOG.md)** - Version history and release notes

## Linting

Run WordPress coding standards checks before committing:

- `composer run lint`
- `composer run lint:fix`
- `composer run lint:security`
- `composer run lint:run`

## Block Template Sync System

The theme includes a comprehensive template sync system for deploying Block Templates across environments.

### What Gets Synced

**System Templates** (`template-parts/`):
- `header-template.php` → Header Template Block
- `footer-template.php` → Footer Template Block

**Page Template Blocks** (`template-parts/layouts/`):
- `blank.php` → Blank Page Template blocks
- `sample.php` → Sample Page Template blocks
- `sidebar.php` → Sidebar Page Template blocks
- `single.php` → Single Post Template blocks

**Traditional WordPress Templates** (`page-templates/` - NOT synced):
- `template-blank.php`, `template-sample.php`, etc.
- These contain traditional WordPress template code (get_header(), get_footer(), etc.)
- Edited directly in PHP, tracked in Git normally
- Create corresponding Block Template posts automatically

### Workflow

**Local Development:**
1. Edit Block Templates in WordPress Admin → Block Templates
2. Go to **Block Templates → Sync Tools**
3. Click **"📤 Export to Files"** to save block content to PHP files
4. Commit files to Git:
   - `template-parts/*.php` (header/footer)
   - `template-parts/layouts/*.php` (page template blocks)
5. Push to GitHub

**Staging/Production Deployment:**
1. Pull latest code from Git
2. Go to **Block Templates → Sync Tools**
3. Click **"📥 Import from Files"** to overwrite database with file content
4. All template block content is now synced!

### Why This System?

Block Templates are stored in the WordPress database, but we need to:
- Version control template content
- Deploy template changes across environments
- Maintain consistency between local, staging, and production

The sync tools provide bi-directional sync between:
- **Files** (Git-tracked, version controlled)
- **Database** (Block Template posts, editable in WordPress)

## Deployment

This theme uses **GitHub Actions** for automated deployment to Staging and Production environments.

### Quick Start

```bash
# Deploy to Staging
git push origin develop

# Deploy to Production
git push origin master
```

### What Gets Deployed

Each deployment automatically:
- ✅ Builds Gutenberg blocks (`npm run build`)
- ✅ Compiles Tailwind CSS
- ✅ Installs production dependencies
- ✅ Syncs files via rsync
- ✅ Excludes dev files and dependencies

### Documentation

- **Setup Guide**: [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)
- **Setup Checklist**: [docs/DEPLOYMENT_CHECKLIST.md](docs/DEPLOYMENT_CHECKLIST.md)
- **Workflow File**: [.github/workflows/deploy.yml](.github/workflows/deploy.yml)

### Required Secrets

Configure in **Repository → Settings → Secrets**:

| Secret | Description |
|--------|-------------|
| `DO_HOST` | Server hostname or IP |
| `DO_SSH_USER` | SSH username |
| `DO_SSH_KEY` | SSH private key |
| `DO_SSH_PORT` | SSH port (default: 22) |
| `WP_STG_THEME_DIR` | Staging theme path |
| `WP_PROD_THEME_DIR` | Production theme path |

See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for detailed setup instructions.

## Security

Please review `SECURITY.md` for:

- supported versions
- vulnerability reporting process
- enforced secure coding standards
