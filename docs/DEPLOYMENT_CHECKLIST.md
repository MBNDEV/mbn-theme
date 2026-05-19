# Deployment Setup Checklist

Use this checklist to set up GitHub Actions deployment for the first time.

## Prerequisites

- [ ] Server access (SSH) to Staging and Production
- [ ] WordPress installed on both environments
- [ ] Git installed on your local machine
- [ ] Repository admin access on GitHub

## Step 1: Server Preparation

### Staging Server
- [ ] WordPress installed at: `/var/www/staging/`
- [ ] Theme directory exists: `/var/www/staging/wp-content/themes/mbn-theme/`
- [ ] Directory is writable by SSH user
- [ ] Test SSH access: `ssh user@staging-server.com`

### Production Server
- [ ] WordPress installed at: `/var/www/production/`
- [ ] Theme directory exists: `/var/www/production/wp-content/themes/mbn-theme/`
- [ ] Directory is writable by SSH user
- [ ] Test SSH access: `ssh user@production-server.com`

## Step 2: SSH Key Setup (Using SiteGround Keys)

### Option A: Get SSH Key from SiteGround (Recommended)

- [ ] Login to SiteGround control panel
- [ ] Go to: **Site Tools → Dev → SSH Keys Manager**
- [ ] Generate a new SSH key pair (or use existing)
- [ ] Download the **private key** file
- [ ] Copy the **public key** (already added to server automatically)

### Option B: Generate Locally (Alternative)

- [ ] Generate deployment SSH key pair:
  ```bash
  ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy
  ```

- [ ] Add public key to SiteGround:
  - Go to: **Site Tools → Dev → SSH Keys Manager**
  - Click "Import Key"
  - Paste public key content

### Test SSH Access

- [ ] Test SSH connection to staging:
  ```bash
  ssh -i /path/to/private-key user@staging-server.com
  ```

- [ ] Test SSH connection to production:
  ```bash
  ssh -i /path/to/private-key user@production-server.com
  ```

### Prepare Private Key for GitHub

- [ ] Open the private key file:
  ```bash
  cat /path/to/private-key
  ```

- [ ] Copy the entire content (including `-----BEGIN` and `-----END` lines)

## Step 3: GitHub Secrets Configuration

Go to: `Repository → Settings → Secrets and variables → Actions`

### SSH Connection Secrets
- [ ] `DO_HOST` = `your-server.com` (or IP address)
- [ ] `DO_SSH_USER` = `ubuntu` (or your SSH username)
- [ ] `DO_SSH_KEY` = (paste entire private key content)
- [ ] `DO_SSH_PORT` = `22` (or your SSH port)

### Staging Environment
- [ ] `WP_STG_THEME_DIR` = `/var/www/staging/wp-content/themes/mbn-theme`

### Production Environment
- [ ] `WP_PROD_THEME_DIR` = `/var/www/production/wp-content/themes/mbn-theme`

## Step 4: Test Deployment

### Test Staging Deployment
- [ ] Push to `develop` branch:
  ```bash
  git checkout develop
  git push origin develop
  ```

- [ ] Check GitHub Actions:
  - Go to `Actions` tab
  - Verify "Build & Deploy Theme" workflow runs
  - Check for success status

- [ ] Verify on Staging server:
  ```bash
  ssh user@staging-server.com
  ls -la /var/www/staging/wp-content/themes/mbn-theme/build/blocks
  ```

- [ ] Test Staging site:
  - Visit: `https://staging.yoursite.com`
  - Check theme is active
  - Verify blocks are working

### Test Production Deployment (Optional)
- [ ] Push to `staging` branch:
  ```bash
  git checkout staging
  git merge develop
  git push origin staging
  ```

- [ ] Check GitHub Actions for staging deployment
- [ ] Verify on Staging server
- [ ] Test Staging site: `https://staging.yoursite.com`

### Test Production Deployment (Optional)
- [ ] **WARNING:** Only do this if you're ready to deploy to production
- [ ] Push to `master` branch
- [ ] Verify deployment succeeds
- [ ] Test Production site

## Step 5: Post-Deployment Configuration

### Block Template Sync
- [ ] Login to WP Admin on Staging
- [ ] Go to: `Block Templates → Sync Tools`
- [ ] Click: `📥 Import from Files`
- [ ] Verify templates loaded successfully
- [ ] Repeat for Production environment

## Step 6: Validation

### Check Deployment Logs
- [ ] Go to GitHub `Actions` tab
- [ ] Click latest workflow run
- [ ] Expand `Deploy theme files via rsync` step
- [ ] Verify no errors

### Check Theme Files
- [ ] SSH to each server
- [ ] Verify files exist:
  ```bash
  cd /var/www/[env]/wp-content/themes/mbn-theme
  ls -la build/blocks/
  ls -la assets/build/
  cat style.css | head -n 20
  ```

### Check WordPress Admin
- [ ] Login to each environment
- [ ] Go to: `Appearance → Themes`
- [ ] Verify theme is active
- [ ] Check theme version matches

### Check Frontend
- [ ] Visit each site
- [ ] Verify blocks render correctly
- [ ] Check console for errors (F12)
- [ ] Test responsive design

## Common Issues & Fixes

### Issue: "Permission denied (publickey)"
**Fix:**
```bash
# On server
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

### Issue: "Directory not found"
**Fix:** Verify theme directory path in GitHub secrets matches server path

### Issue: "rsync: command not found"
**Fix:** Install rsync on server:
```bash
sudo apt-get install rsync  # Ubuntu/Debian
sudo yum install rsync      # CentOS/RHEL
```

### Issue: Deployment succeeds but theme not updated
**Fix:** 
1. Check file permissions on theme directory
2. Verify rsync is not being blocked by firewall
3. Check server logs: `tail -f /var/log/syslog`

## Optional: Enable WP-CLI Cache Clearing

Install WP-CLI on servers for automatic cache clearing:

```bash
# Install WP-CLI
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp

# Test
wp --info
```

## Documentation

- [ ] Read: [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)
- [ ] Bookmark: GitHub Actions dashboard
- [ ] Share credentials with team (securely)

## Rollback Plan

Document rollback procedure for emergencies:

1. **Quick Rollback:**
   ```bash
   git revert HEAD
   git push origin [branch]
   ```

2. **Manual Rollback:**
   ```bash
   ssh user@server
   cd /var/www/[env]/wp-content/themes
   cp -r mbn-theme-backup mbn-theme
   ```

## Team Notification

- [ ] Notify team deployment is active
- [ ] Share deployment workflow document
- [ ] Schedule training session if needed

---

**Setup Complete! 🎉**

Your GitHub Actions deployment is now active. Push to `develop`, `staging`, or `master` to trigger automatic deployments.
