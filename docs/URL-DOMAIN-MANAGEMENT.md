# Domain URL Management for Page Sync

## Problem
When syncing page patterns between environments (local → staging → production), image URLs and other absolute URLs contain the source domain and won't work on the target environment.

Example:
- Local: `https://blacklineguardianfund.dev.local/wp-content/uploads/...`
- Staging: `https://staging2.blacklineguardianfund.com/wp-content/uploads/...`

---

## ✅ Solution 1: Automatic URL Replacement on Import (IMPLEMENTED)

**How it works:**
- During import, all known source domain URLs are automatically replaced with the current site's URL
- Managed via WordPress Admin UI (no code editing needed!)
- Settings stored in WordPress database

**Configuration via UI:**
1. Go to **WordPress Admin → Tools → Page Content Sync**
2. Scroll to **"⚙️ Domain URL Settings"** section
3. Add all your environment URLs (one per line):
   ```
   https://blacklineguardianfund.dev.local
   https://staging2.blacklineguardianfund.com
   https://blacklineguardianfund.com
   ```
4. Click "Save Domain Settings"

**Alternative: Edit Config File:**
For advanced users or team defaults, edit `inc/includes-page-sync-config.php`

**Pros:**
- ✅ Automatic - no manual work needed
- ✅ Works for all URLs in content (images, links, etc.)
- ✅ Easy to manage via WordPress admin
- ✅ Safe - only replaces known domains
- ✅ No need to edit code files
- ✅ Changes persist across environments (store in database on each server)

**Cons:**
- ⚠️ Must configure domain list on each environment (one-time setup)

---

## Alternative Solution 2: Use Relative URLs in Content

**How it works:**
Instead of storing full URLs, store relative paths like `/wp-content/uploads/...`

**Implementation:**
Add this function to export process:

```php
function custom_theme_make_urls_relative( $content ) {
    $site_url = get_site_url();
    $content = str_replace( $site_url, '', $content );
    return $content;
}
```

**Pros:**
- ✅ Truly portable across any domain
- ✅ No configuration needed

**Cons:**
- ⚠️ WordPress blocks prefer absolute URLs
- ⚠️ May break some block editor features
- ⚠️ External URLs also become relative (not ideal)

---

## Alternative Solution 3: Placeholder-Based System

**How it works:**
Replace URLs with placeholders during export, then replace back on import.

**Export:**
```php
$content = str_replace( get_site_url(), '{{SITE_URL}}', $content );
```

**Import:**
```php
$content = str_replace( '{{SITE_URL}}', get_site_url(), $content );
```

**Pros:**
- ✅ Very explicit and clear
- ✅ Easy to debug
- ✅ Works across any environment

**Cons:**
- ⚠️ Files are not readable without importing
- ⚠️ Can't preview content in Git

---

## Alternative Solution 4: Database Search-Replace (External Tool)

**How it works:**
Use existing tools to search/replace URLs after syncing database.

**Tools:**
- **WP-CLI**: `wp search-replace 'oldurl.com' 'newurl.com'`
- **Better Search Replace** (plugin)
- **WP Migrate DB** (plugin)

**Pros:**
- ✅ Battle-tested, mature tools
- ✅ Handles serialized data properly
- ✅ Works for entire database, not just pages

**Cons:**
- ⚠️ Requires database access
- ⚠️ Separate step from page sync
- ⚠️ Overkill for just page patterns

---

## Alternative Solution 5: Use Theme Assets Instead of Uploads

**How it works:**
Store structural images in `assets/images/` instead of uploading them.

**Setup:**
1. Place images in `wp-content/themes/your-theme/assets/images/`
2. Use `get_theme_file_uri('assets/images/hero.jpg')` in blocks
3. Export will save as `'featured_image_path' => 'assets/images/hero.jpg'`
4. Import automatically finds the file via Git

**Pros:**
- ✅ No URL issues - files ship via Git
- ✅ Perfect for structural/template images
- ✅ Already implemented in page sync

**Cons:**
- ⚠️ Only works for images that are part of theme
- ⚠️ Not suitable for user-uploaded content

---

## Recommended Approach

**For your use case:**
1. ✅ **Use Solution 1 (Automatic Replacement)** - already implemented with UI
2. ✅ **Use Solution 5 (Theme Assets)** for template images like hero backgrounds, icons, etc.

**Setup (One-time per environment):**
1. On LOCAL: Go to Tools → Page Content Sync → Domain URL Settings
2. Add all your environment URLs and save
3. Repeat on STAGING and PRODUCTION (each server stores its own settings)

**Workflow:**
1. On LOCAL: Edit pages, use images from Media Library or theme assets
2. On LOCAL: Export pages via Tools → Page Content Sync
3. Commit exported files to Git
4. On STAGING: Pull from Git
5. On STAGING: Import pages - URLs automatically replaced ✅
6. On PRODUCTION: Same as staging

**Note:** Domain settings are stored in the database per environment, so each server needs to be configured once but remembers your settings.

---

## Testing URL Replacement

To verify URL replacement is working:

1. Export a page on local
2. Open `template-parts/page-patterns/[page].php`
3. You'll see URLs like: `https://blacklineguardianfund.dev.local/...`
4. Import on staging
5. Edit page in WordPress admin
6. URLs should now be: `https://staging2.blacklineguardianfund.com/...`

---

## Advanced: Manual URL Replacement Script

If you need to manually fix URLs in already-exported files:

```bash
# In theme directory
php scripts/replace-domains.php --from="blacklineguardianfund.dev.local" --to="staging2.blacklineguardianfund.com"
```

Create this script if needed (see next section).

---

## Future Enhancement Ideas

1. **Visual Indicator**: Show which URLs were replaced during import
2. **Dry Run Mode**: Preview URL changes before importing
3. **URL Mapping Report**: Generate report of all URL replacements
4. **Exclusion Patterns**: Don't replace certain external URLs
5. **Smart Detection**: Auto-detect source domain from file content
