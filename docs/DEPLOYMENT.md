# GitHub Actions Deployment Setup

## Overview

This theme uses GitHub Actions to automatically deploy to Staging and Production environments when code is pushed to their respective branches.

## Deployment Flow

```
develop branch → Staging environment
master branch  → Production environment
```

## What Gets Deployed

The deployment workflow:
1. ✅ Builds Gutenberg blocks (`npm run build`)
2. ✅ Compiles Tailwind CSS
3. ✅ Installs production Composer dependencies
4. ✅ Syncs files to WordPress theme directory via rsync
5. ✅ Excludes dev files (node_modules, source files, etc.)

## Required GitHub Secrets

Go to **Repository → Settings → Secrets and variables → Actions** and add:

### SSH Connection (shared across all environments)

| Secret Name | Description | Example |
|------------|-------------|---------|
| `DO_HOST` | Server hostname or IP | `123.45.67.89` or `mysite.com` |
| `DO_SSH_USER` | SSH username | `ubuntu` or `root` |
| `DO_SSH_KEY` | SSH private key | Contents of `~/.ssh/id_rsa` |
| `DO_SSH_PORT` | SSH port | `22` (default) |

### Environment-Specific Secrets

| Secret Name | Environment | Description | Example |
|------------|-------------|-------------|---------|

| `WP_STG_THEME_DIR` | Staging | Full path to theme directory | `/var/www/staging/wp-content/themes/mbn-theme` |
| `WP_PROD_THEME_DIR` | Production | Full path to theme directory | `/var/www/production/wp-content/themes/mbn-theme` |

## Setting Up SSH Access

### Option A: Using SiteGround SSH Keys (Recommended)

**1. Generate/Get SSH Key from SiteGround:**

- Login to **SiteGround → Site Tools → Dev → SSH Keys Manager**
- Click **"Generate New Key"** or use an existing key
- Download the **private key** file
- The **public key** is automatically added to your server

**2. Get SSH Connection Details:**

- Go to **Site Tools → Dev → SSH Keys Manager**
- Note your SSH hostname, username, and port
- Typical format: `username@serverXX.siteground.com`

**3. Test SSH Connection:**

```bash
ssh -i /path/to/downloaded-private-key username@serverXX.siteground.com -p 18765
```

**4. Add Private Key to GitHub Secrets:**

```bash
cat /path/to/downloaded-private-key
```

Copy the entire output (including `-----BEGIN` and `-----END` lines) and add to GitHub secret `DO_SSH_KEY`.

### Option B: Generate SSH Key Locally (Alternative)

**1. Generate SSH Key Pair:**

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy
```

**2. Add Public Key to SiteGround:**

- Go to **SiteGround → Site Tools → Dev → SSH Keys Manager**
- Click **"Import Key"**
- Paste your public key:
  ```bash
  cat ~/.ssh/github_deploy.pub
  ```

**3. Test SSH Connection:**

```bash
ssh -i ~/.ssh/github_deploy username@serverXX.siteground.com -p 18765
```

**4. Add Private Key to GitHub Secrets:**

```bash
cat ~/.ssh/github_deploy
```

Add the entire output to GitHub secret `DO_SSH_KEY`.

### Important Notes

- SiteGround typically uses **non-standard SSH ports** (e.g., 18765)
- Make sure to set `DO_SSH_PORT` secret accordingly
- Private keys must include header/footer lines (`-----BEGIN OPENSSH PRIVATE KEY-----`)

## Directory Structure on Server

Each environment should have this structure:

```
/var/www/your-site/
├── wp-content/
│   └── themes/
│       └── mbn-theme/          ← Deployment target (WP_*_THEME_DIR)
│           ├── style.css
│           ├── functions.php
│           ├── build/          ← Built blocks
│           ├── assets/build/   ← Compiled CSS
│           └── vendor/         ← Composer deps
```

## Manual Deployment Trigger

You can manually trigger deployment from GitHub:

1. Go to **Actions** tab
2. Select **Build & Deploy Theme**
3. Click **Run workflow**
4. Choose the branch (develop/master)

## Post-Deployment Steps

### Block Template Sync

If you modified Block Templates locally and exported them:

1. Go to **WP Admin → Block Templates → Sync Tools**
2. Click **📥 Import from Files**
3. This syncs `template-parts/*.php` content to database

### Cache Clearing

The workflow attempts to clear WordPress cache automatically. If it fails:

```bash
ssh user@your-server.com
cd /var/www/your-site/wp-content/themes/mbn-theme
wp cache flush --path=/var/www/your-site
```

## Troubleshooting

### Deployment fails with "Permission denied"

**Issue:** SSH key not authorized on server

**Fix:**
```bash
# On your server
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

### Deployment succeeds but theme not updated

**Issue:** Wrong theme directory path

**Fix:** Verify the theme directory secret matches the actual path:
```bash
ssh user@your-server.com
ls -la /var/www/your-site/wp-content/themes/mbn-theme
```

### Missing built assets after deployment

**Issue:** Build failed silently

**Fix:** Check GitHub Actions logs:
1. Go to **Actions** tab
2. Click the failed workflow run
3. Expand **Build theme assets** step

### Block Templates not syncing

**Issue:** Block Templates are in database, not auto-synced

**Fix:** Manual sync required:
- Go to **WP Admin → Block Templates → Sync Tools**
- Click **Import from Files**

## Files Excluded from Deployment

The following are **NOT** deployed to production:

- `node_modules/` - Dev dependencies
- `.git/` - Git history
- `.github/` - GitHub workflows
- `resources/css/` - Source CSS (only built assets deployed)
- `blocks/*/src/` - Source JS/JSX
- Development config files (webpack, tailwind, etc.)
- `references/` - Reference documentation
- `composer.lock` - Dependency lock file
- `package-lock.json` - NPM lock file

## Monitoring Deployments

### GitHub Actions Dashboard

View all deployments:
1. Go to **Actions** tab
2. Filter by workflow: **Build & Deploy Theme**
3. See status for each environment

### Deployment Logs

Each deployment creates detailed logs:
- SSH connection status
- File sync progress
- Build verification
- Cache clearing results

### Slack/Email Notifications (Optional)

To get notified on deployment:

1. Go to **Repository → Settings → Notifications**
2. Configure webhooks for:
   - Deployment started
   - Deployment completed
   - Deployment failed

## Rollback Procedure

If deployment breaks the site:

### Option 1: Revert via Git

```bash
# Revert the commit locally
git revert HEAD

# Push to staging or production
git push origin develop  # for staging
git push origin master   # for production

# GitHub Actions will auto-deploy the reverted version
```

### Option 2: Manual Rollback

```bash
ssh user@your-server.com
cd /var/www/your-site/wp-content/themes

# Restore from backup
cp -r mbn-theme-backup mbn-theme
```

### Option 3: Emergency Hotfix

```bash
# Create hotfix directly on production (not recommended)
ssh user@your-server.com
cd /var/www/your-site/wp-content/themes/mbn-theme

# Make emergency fix
nano functions.php

# Commit and push from local later
```

## Best Practices

1. **Test on Staging first**: Always push to `develop` (staging) before `master` (production)
2. **Review build logs**: Check GitHub Actions logs after each deployment
3. **Backup before major changes**: Create theme backup before deploying big updates
4. **Use branches**: Create feature branches, then merge to develop
5. **Tag releases**: Tag production deployments for easy rollback:
   ```bash
   git tag -a v1.0.0 -m "Release 1.0.0"
   git push origin v1.0.0
   ```

## Development Workflow

```bash
# 1. Create feature branch
git checkout -b feature/new-block

# 2. Make changes and test locally
npm run dev

# 3. Build for production
npm run build

# 4. Commit changes
git add .
git commit -m "Add new hero block"

# 5. Push to develop (auto-deploys to Staging)
git checkout develop
git merge feature/new-block
git push origin develop

# 6. Test on Staging environment
# Visit: https://staging.mysite.com

# 7. Deploy to Production
git checkout master
git merge develop
git push origin master

# 8. Verify on Production
# Visit: https://mysite.com

# 9. Deploy to Production
git checkout master
git merge staging
git push origin master
```

## Security Notes

- ⚠️ Never commit SSH keys to the repository
- ⚠️ Use environment-specific secrets for each deployment target
- ⚠️ Restrict SSH key to specific IP ranges if possible
- ⚠️ Rotate SSH keys periodically (every 90 days)
- ⚠️ Use separate SSH keys for GitHub Actions (don't use your personal key)

## Need Help?

- 📖 [GitHub Actions Documentation](https://docs.github.com/actions)
- 📖 [rsync Deployment Guide](https://github.com/burnett01/rsync-deployments)
- 📖 [WordPress Theme Development](https://developer.wordpress.org/themes/)
