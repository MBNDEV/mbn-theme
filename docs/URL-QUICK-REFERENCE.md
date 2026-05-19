# Quick Reference: Domain URL Management

## ✅ What's Already Working

**Automatic URL replacement is now enabled!** 

When you import pages from patterns, all image and content URLs are automatically updated to match your current environment.

---

## 🔧 Configuration

### WordPress Admin UI (Recommended)

1. Go to **Tools → Page Content Sync**
2. Scroll down to **"⚙️ Domain URL Settings"**
3. Enter your URLs in the two simple fields:
   - **🏠 Local Development URL**: `https://example.dev.local`
   - **🌐 Deployment Server URL**: `https://staging.example.com`
4. Click **"💾 Save Settings"**

**That's it!** The system automatically handles both http:// and https:// versions of each URL.

**Note:** Set this up once on each environment (local, staging, production).

---

## 📝 Workflow

### Setting Up Domain URLs (First Time)
1. Go to **WordPress Admin → Tools → Page Content Sync**
2. Scroll to **"⚙️ Domain URL Settings"**
3. Enter your two URLs:
   - **Local URL**: `https://example.dev.local`
   - **Deployment URL**: `https://staging.example.com`
4. Click "💾 Save Settings"

**Done!** Both http:// and https:// versions are handled automatically.

### Local → Staging/Production
1. **Local**: Export pages via Tools → Page Content Sync
2. **Local**: Commit files to Git
3. **Server**: Pull from Git
4. **Server**: Import pages
   - ✅ URLs automatically updated to match current site

---

## 🎯 What Gets Replaced

During import, these URLs are automatically updated:
- ✅ Image URLs in block attributes (`backgroundImageUrl`, `imageUrl`, etc.)
- ✅ Link URLs (`href="..."`)
- ✅ Any absolute URLs pointing to known domains

**Example:**
```
FROM: https://example.dev.local/wp-content/uploads/2026/04/hero.jpg
TO:   https://staging.example.com/wp-content/uploads/2026/04/hero.jpg
```

---

## 🛠️ Manual URL Replacement (if needed)

If you need to manually fix URLs in pattern files:

```bash
cd wp-content/themes/mbn-theme

# Dry run (preview only)
php scripts/replace-domains.php \
  --from="example.dev.local" \
  --to="staging.example.com" \
  --dry-run

# Apply changes
php scripts/replace-domains.php \
  --from="example.dev.local" \
  --to="staging.example.com"
```

---

## 💡 Best Practices

### For Template Images (Hero Backgrounds, Icons, etc.)
✅ **Store in theme assets** instead of uploads:
1. Place images in: `assets/images/`
2. They ship via Git automatically
3. No URL replacement needed!

### For User Content
✅ **Use Media Library** normally:
- Upload via WordPress admin
- URLs automatically replaced during import
- Works across all environments

### Adding New Environments
When you add a new staging or testing environment:
1. Add its URL to `inc/includes-page-sync-config.php`
2. Commit the config file
3. Import pages - URLs will be handled automatically

---

## 🔍 Troubleshooting

**URLs not being replaced?**
1. Check `inc/includes-page-sync-config.php` - is the source domain listed?
2. Check spelling - must match exactly (including http/https)
3. Look at import success message - does it mention URL replacement?

**Mixed content warnings?**
- Some URLs might still be http:// instead of https://
- The replacement system always uses https:// for security
- If issues persist, add both http and https versions to config

**Need to see what's being replaced?**
- Check the success message after import
- It will say "URLs automatically updated to current site domain"

---

## 📚 Full Documentation

For more details and alternative solutions, see:
```
docs/URL-DOMAIN-MANAGEMENT.md
```
