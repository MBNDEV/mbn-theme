# Domain URL Settings - User Guide

## 📍 Location
**WordPress Admin → Tools → Page Content Sync → Domain URL Settings**

---

## 🎯 What It Does

This settings panel lets you configure which domain URLs are automatically replaced when you import page patterns.

**Simple Setup:**
- Enter your **local development URL**
- Enter your **deployment server URL** (staging or production)
- System automatically handles both http:// and https:// versions

**Example:**
- You export a page on local with images like: `https://blacklineguardianfund.dev.local/wp-content/uploads/hero.jpg`
- You import it on staging
- URLs automatically become: `https://staging2.blacklineguardianfund.com/wp-content/uploads/hero.jpg`

---

## 📝 How to Use

### Initial Setup (Do Once Per Environment)

1. **On Local Development:**
   - Navigate to Tools → Page Content Sync
   - Scroll to "⚙️ Domain URL Settings"
   - Enter:
     - **Local URL**: `https://blacklineguardianfund.dev.local`
     - **Deployment URL**: `https://staging2.blacklineguardianfund.com`
   - Click "💾 Save Settings"

2. **On Staging Server:**
   - Same steps as above
   - Use the same URLs

3. **On Production Server:**
   - Same steps as above
   - Update deployment URL to production if needed

---

## 🔍 What You'll See

### The Settings Interface

```
⚙️ Domain URL Settings

🏠 Local Development URL
[https://blacklineguardianfund.dev.local          ]
Your local development domain (e.g., Laragon, XAMPP, Local WP)

🌐 Deployment Server URL
[https://staging2.blacklineguardianfund.com       ]
Your staging or production server domain

[💾 Save Settings] [🗑️ Clear Settings]

📋 Current Active Domains for Replacement ▶
```

### Success Message
After saving:
```
✅ Domain URLs saved successfully!
```

---

## ✅ Valid Domain Format

**Valid:**
- ✅ `https://blacklineguardianfund.dev.local`
- ✅ `http://staging.example.com`
- ✅ `https://www.example.com`

**Invalid:**
- ❌ `blacklineguardianfund.dev.local` (missing http/https)
- ❌ `https://example.com/` (has trailing slash - will be auto-removed)
- ❌ `example.com` (missing protocol)

---

## 💡 Pro Tips

### Automatic Protocol Handling
You only need to enter one version:
- Enter: `https://yoursite.com`
- System handles: `https://yoursite.com` AND `http://yoursite.com`

### Universal Deployment Field
The "Deployment URL" field works for:
- ✅ Staging servers
- ✅ Production servers
- ✅ Any remote environment

Just enter the URL you're syncing to/from.

### Local Development URLs
Common local development domains:
- Laragon: `https://yoursite.local` or `https://yoursite.dev.local`
- XAMPP: `http://localhost/yoursite`
- Local WP: `http://yoursite.local`
- Docker: `http://localhost:8080`

### Clear Settings
Click "🗑️ Clear Settings" to remove both URLs and revert to defaults.

### View Active List
Click "📋 Current Active Domains for Replacement" to see exactly which domains will be replaced (includes both http/https versions).

---

## 🔧 Troubleshooting

**Q: I entered URLs but they're not being replaced?**
- Check spelling - domains must match exactly
- Make sure you clicked "💾 Save Settings"
- Click "📋 Current Active Domains" to verify they're saved
- Protocol (http/https) is handled automatically, so just one version is needed

**Q: Do I need to enter both http:// and https://?**
- No! Just enter one version (preferably https://)
- The system automatically handles both protocols

**Q: Can I add more than two URLs?**
- The UI is designed for simplicity with two fields
- For advanced multi-environment setups, edit `inc/includes-page-sync-config.php`

**Q: What if I have multiple staging servers?**
- Update the "Deployment URL" field as needed for each sync
- Or use the config file for permanent multi-domain setup

**Q: What happens if I clear both fields?**
- The system uses a default fallback (your local dev URL)
- You can always re-enter them later

**Q: Do I need to update this on all servers?**
- Yes, each environment stores its own settings
- But you only need to do it once per server
- Settings persist in the database

---

## 🎓 Advanced Usage

### Viewing Settings in Database
Settings are stored in WordPress options table:
```sql
SELECT * FROM wp_options WHERE option_name IN ('custom_theme_local_url', 'custom_theme_deployment_url');
```

### Programmatically Set URLs
```php
// Set local URL
update_option( 'custom_theme_local_url', 'https://blacklineguardianfund.dev.local' );

// Set deployment URL
update_option( 'custom_theme_deployment_url', 'https://staging2.blacklineguardianfund.com' );
```

### Check Current Settings via Code
```php
$domains = custom_theme_get_source_domains();
var_dump( $domains );
// Will show both http and https versions of configured URLs
```

### Multiple Environments (Advanced)
If you need more than two domains, edit:
```
inc/includes-page-sync-config.php
```

Modify the `custom_theme_get_source_domains()` function to add additional domains.

---

## 📚 Related Documentation

- [URL-QUICK-REFERENCE.md](URL-QUICK-REFERENCE.md) - Quick start guide
- [URL-DOMAIN-MANAGEMENT.md](URL-DOMAIN-MANAGEMENT.md) - Complete documentation
- [DEPLOYMENT.md](DEPLOYMENT.md) - Full deployment workflow

---

## 🆘 Need Help?

If URLs aren't being replaced correctly:
1. Check the import success message - it should say "URLs automatically updated"
2. Verify domain list in Settings → View Current Active Domains
3. Try a test import and check a page in WordPress editor
4. See [URL-DOMAIN-MANAGEMENT.md](URL-DOMAIN-MANAGEMENT.md) for troubleshooting
