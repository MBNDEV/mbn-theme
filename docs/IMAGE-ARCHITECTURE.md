# Image Architecture Analysis & Recommendations

## Current State Analysis

### 📊 How Images Are Currently Handled

#### 1. **Theme Assets (`assets/images/`)**
```
Current Usage: Structural/Design Images
Storage: Git repository
Environments: Same across dev/staging/production
```

**✅ What Works:**
- Clear separation between theme and content images
- Ships via Git with code
- Has defined structure and documentation
- Page sync system supports this pattern
- Images are versioned with code

**Current Flow:**
1. Developer adds image to `assets/images/hero/background.jpg`
2. Uploads via WordPress Media Library
3. Uses as featured image or in blocks
4. Export creates: `'featured_image_path' => 'assets/images/hero/background.jpg'`
5. Import on staging/production finds the file (shipped via Git)
6. Creates attachment entry in database
7. Sets as featured image

#### 2. **WordPress Media Library (`wp-content/uploads/`)**
```
Current Usage: User-uploaded content
Storage: Server filesystem (not in Git)
Environments: Different per environment
```

**Current Implementation:**
- Standard WordPress upload directory
- Not synced across environments
- Stored in exports as `'featured_image_url'`
- Import attempts to download from URL

#### 3. **Block Images**
```
Current Pattern: MediaUpload component
Storage: WordPress Media Library (uploads)
Reference: Stores both ID and URL in attributes
```

**From `references/banner block/index.tsx`:**
```tsx
<MediaUpload
  onSelect={(media) => 
    setAttributes({
      logoId: media.id,
      logoUrl: media.url,
    })
  }
  allowedTypes={['image']}
  value={attributes.logoId}
/>
```

### 🔍 Current Capabilities

#### Image Sizes
**Registered:**
- `section-bg-tablet` (1024px width)
- `section-bg-mobile` (640px width)

**Missing:**
- ❌ No custom featured image sizes
- ❌ No blog post thumbnail sizes
- ❌ No support for `post-thumbnails` feature
- ❌ No content-specific image sizes

#### Responsive Images
**✅ Working:**
- Section backgrounds have mobile/tablet/desktop variants
- Automatic fallback to smaller sizes
- CSS-based responsive loading

**❌ Not Implemented:**
- No responsive `srcset` for content images
- No art direction support
- No modern format conversion (WebP, AVIF)

#### Optimization
**Current State:**
- No automatic image compression
- No lazy loading (relies on browser defaults)
- No CDN integration
- No format conversion

---

## 🎯 Architectural Recommendations

### Strategy: **Hybrid Approach**

Use **both** assets directory and WordPress uploads, with clear boundaries:

```
┌─────────────────────────────────────────────────────┐
│           THEME ASSETS (assets/images/)             │
│  ✅ Git-tracked                                     │
│  ✅ Version controlled                              │
│  ✅ Same across all environments                    │
├─────────────────────────────────────────────────────┤
│  USE FOR:                                           │
│  • Site structure (hero backgrounds, layouts)       │
│  • Branding (logos, icons, design elements)         │
│  • Default placeholders                             │
│  • Team/about page photos (if static)               │
│  • Landing page designs (Figma → blocks)            │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│         WORDPRESS UPLOADS (wp-content/uploads/)     │
│  ✅ User-manageable                                 │
│  ✅ Independent per environment                     │
│  ✅ Can be updated without deployment               │
├─────────────────────────────────────────────────────┤
│  USE FOR:                                           │
│  • Blog post images                                 │
│  • News/press releases                              │
│  • Dynamic content (frequently changing)            │
│  • User-generated content                           │
│  • Client-uploaded media                            │
│  • Gallery/portfolio images                         │
└─────────────────────────────────────────────────────┘
```

---

## 📋 Implementation Recommendations

### Priority 1: Core WordPress Features (CRITICAL)

#### 1.1 Enable Featured Images Support

**Current Issue:** Theme doesn't register featured image support

**Fix:**
```php
// In functions.php - blacklinesecurityops_theme_setup()
add_theme_support( 'post-thumbnails' );
```

**Impact:** Enables featured images for posts and pages

#### 1.2 Register Custom Image Sizes

**Add to functions.php:**
```php
function mbn_theme_register_image_sizes() {
	// Featured images for blog posts/pages
	add_image_size( 'post-thumbnail', 1200, 630, true );
	add_image_size( 'post-thumbnail-large', 1920, 1080, true );
	
	// Blog listing thumbnails
	add_image_size( 'blog-thumbnail', 400, 266, true );
	add_image_size( 'blog-thumbnail-2x', 800, 532, true );
	
	// Cards and widgets
	add_image_size( 'card-thumbnail', 600, 400, true );
	
	// Hero images (uncropped, width-based)
	add_image_size( 'hero-desktop', 1920, 0, false );
	add_image_size( 'hero-tablet', 1024, 0, false );
	add_image_size( 'hero-mobile', 640, 0, false );
	
	// OG images for social sharing
	add_image_size( 'og-image', 1200, 630, true );
}
add_action( 'after_setup_theme', 'mbn_theme_register_image_sizes' );
```

### Priority 2: Image Optimization

#### 2.1 Enable Responsive Images

WordPress has built-in `srcset` support. Ensure it's not disabled:

```php
// Verify srcset is enabled (it is by default)
add_filter( 'wp_calculate_image_srcset', function( $sources ) {
	MBN_Logger::debug( 'Generating srcset', array( 
		'sizes' => count( $sources ) 
	));
	return $sources;
});
```

#### 2.2 Add Lazy Loading

WordPress 5.5+ has native lazy loading. Ensure it's active:

```php
// Force lazy loading for content images
add_filter( 'wp_img_tag_add_loading_attr', function( $value, $image, $context ) {
	if ( 'the_content' === $context ) {
		return 'lazy';
	}
	return $value;
}, 10, 3 );
```

#### 2.3 Image Compression (Optional Plugin)

**Recommended Plugins:**
- **ShortPixel Image Optimizer** (freemium)
- **Imagify** (freemium)
- **EWWW Image Optimizer** (free)

**Or Manual with Node:**
```bash
npm install --save-dev imagemin imagemin-mozjpeg imagemin-pngquant
```

```js
// scripts/optimize-images.js
const imagemin = require('imagemin');
const imageminMozjpeg = require('imagemin-mozjpeg');
const imageminPngquant = require('imagemin-pngquant');

(async () => {
	await imagemin(['assets/images/**/*.{jpg,png}'], {
		destination: 'assets/images',
		plugins: [
			imageminMozjpeg({ quality: 80 }),
			imageminPngquant({ quality: [0.6, 0.8] })
		]
	});
	console.log('Images optimized');
})();
```

### Priority 3: Development Workflow

#### 3.1 Image Decision Tree

```
Is the image part of the site design/structure?
  ├─ YES → assets/images/ (Git)
  │   └─ Examples: hero backgrounds, logos, team photos, landing pages
  └─ NO → wp-content/uploads/ (Media Library)
      └─ Examples: blog posts, news, user content, galleries
```

#### 3.2 Block Image Guidelines

**For Gutenberg Blocks:**

```tsx
// ✅ RECOMMENDED: Store both ID and URL
interface BlockAttributes {
  backgroundImageId: number;
  backgroundImageUrl: string;
}

// ✅ GOOD: Fallback to theme asset
const backgroundUrl = attributes.backgroundImageUrl || 
  getThemeFileUri('assets/images/default-hero.jpg');

// ✅ GOOD: Validate on save
if (attributes.backgroundImageId > 0) {
  // Use WordPress attachment system (responsive images)
  const attachment = wp.media.attachment(attributes.backgroundImageId);
  // Can get different sizes: thumbnail, medium, large, full
}
```

### Priority 4: Production Considerations

#### 4.1 Environment-Specific Images

**Current System Supports:**

| Type | Dev | Staging | Production | Method |
|------|-----|---------|------------|--------|
| **Theme assets** | ✅ Same | ✅ Same | ✅ Same | Git |
| **Uploads** | ❌ Different | ❌ Different | ❌ Different | Per-environment |

**Recommendation for Uploads:**
- **Option A:** Accept differences (posts/content vary per environment)
- **Option B:** Use WP Migrate DB Pro with Media Files addon
- **Option C:** Sync via rsync/cloud storage (S3)

#### 4.2 CDN Integration (Future)

```php
// Add CDN rewrite for uploads
add_filter( 'wp_get_attachment_url', function( $url ) {
	$cdn_url = defined( 'CDN_URL' ) ? CDN_URL : '';
	if ( $cdn_url && strpos( $url, 'wp-content/uploads' ) !== false ) {
		return str_replace( 
			home_url( '/wp-content/uploads' ), 
			$cdn_url, 
			$url 
		);
	}
	return $url;
});
```

---

## 🚀 Recommended Action Plan

### Phase 1: Foundation (Do Now)

1. **Add featured image support**
   ```php
   add_theme_support( 'post-thumbnails' );
   ```

2. **Register image sizes**
   ```php
   add_image_size( 'post-thumbnail', 1200, 630, true );
   add_image_size( 'blog-thumbnail', 400, 266, true );
   // ... others
   ```

3. **Document image strategy**
   - Update `assets/images/README.md` with new sizes
   - Create workflow guide for content editors

4. **Regenerate thumbnails**
   ```bash
   wp media regenerate --yes
   # Or use Regenerate Thumbnails plugin
   ```

### Phase 2: Optimization (Near Term)

1. **Add image compression**
   - Install ShortPixel or similar
   - Or create npm script for assets

2. **Implement lazy loading**
   - Already native in WordPress
   - Verify it's working

3. **Add logging for images**
   ```php
   add_action( 'add_attachment', function( $attachment_id ) {
       MBN_Logger::info( 'Image uploaded', array(
           'id' => $attachment_id,
           'file' => get_attached_file( $attachment_id ),
           'url' => wp_get_attachment_url( $attachment_id )
       ));
   });
   ```

### Phase 3: Advanced (Future)

1. **WebP/AVIF Support**
   - PHP GD/Imagick with format conversion
   - Or plugin-based solution

2. **CDN Integration**
   - Cloudflare, AWS CloudFront, or KeyCDN
   - Rewrite upload URLs to CDN

3. **Object Storage**
   - AWS S3 or Digital Ocean Spaces
   - Offload uploads from web server

---

## 📝 Updated Guidelines

### For Theme Developers

```php
/**
 * ✅ DO: Use theme assets for structural images
 */
$hero_bg = get_theme_file_uri( 'assets/images/hero/home-hero.jpg' );

/**
 * ✅ DO: Use MediaUpload for dynamic content
 */
<MediaUpload
  onSelect={(media) => setAttributes({ imageId: media.id, imageUrl: media.url })}
  allowedTypes={['image']}
/>

/**
 * ✅ DO: Provide fallbacks
 */
const imageUrl = attributes.imageUrl || get_theme_file_uri('assets/images/placeholder.jpg');

/**
 * ❌ DON'T: Hardcode upload URLs
 */
const badUrl = 'http://example.com/wp-content/uploads/2026/04/image.jpg'; // ❌

/**
 * ✅ DO: Use attachment functions
 */
$url = wp_get_attachment_image_url( $attachment_id, 'large' );
$srcset = wp_get_attachment_image_srcset( $attachment_id, 'large' );
```

### For Content Editors

**When to use each:**

| Scenario | Use | Upload To |
|----------|-----|-----------|
| Homepage hero background | Theme asset | `assets/images/hero/` |
| Blog post feature image | Media Library | WordPress uploader |
| About page team photo | Theme asset (if static) | `assets/images/team/` |
| News article image | Media Library | WordPress uploader |
| Logo in header | Theme asset | `assets/images/branding/` |
| Product gallery | Media Library | WordPress uploader |

---

## 🔧 Code Implementation

### File: `inc/includes-media.php` (NEW)

```php
<?php
/**
 * Media and Image Management
 *
 * @package MBNTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register image sizes
 */
function mbn_theme_register_image_sizes() {
	// Featured images
	add_image_size( 'post-thumbnail', 1200, 630, true );
	add_image_size( 'post-thumbnail-large', 1920, 1080, true );
	
	// Blog thumbnails
	add_image_size( 'blog-thumbnail', 400, 266, true );
	add_image_size( 'blog-thumbnail-2x', 800, 532, true );
	
	// Cards
	add_image_size( 'card-thumbnail', 600, 400, true );
	
	// Hero images (uncropped)
	add_image_size( 'hero-desktop', 1920, 0, false );
	add_image_size( 'hero-tablet', 1024, 0, false );
	add_image_size( 'hero-mobile', 640, 0, false );
	
	// Social sharing
	add_image_size( 'og-image', 1200, 630, true );
	
	MBN_Logger::info( 'Custom image sizes registered' );
}
add_action( 'after_setup_theme', 'mbn_theme_register_image_sizes' );

/**
 * Add featured image support
 */
function mbn_theme_enable_featured_images() {
	add_theme_support( 'post-thumbnails' );
	
	// Set default featured image size
	set_post_thumbnail_size( 1200, 630, true );
	
	MBN_Logger::info( 'Featured images enabled' );
}
add_action( 'after_setup_theme', 'mbn_theme_enable_featured_images' );

/**
 * Add custom image sizes to media library size dropdown
 */
function mbn_theme_custom_image_sizes( $sizes ) {
	return array_merge( $sizes, array(
		'post-thumbnail-large' => __( 'Featured Image Large', 'mbn-theme' ),
		'blog-thumbnail' => __( 'Blog Thumbnail', 'mbn-theme' ),
		'card-thumbnail' => __( 'Card Thumbnail', 'mbn-theme' ),
		'hero-desktop' => __( 'Hero Desktop', 'mbn-theme' ),
		'og-image' => __( 'Social Sharing', 'mbn-theme' ),
	));
}
add_filter( 'image_size_names_choose', 'mbn_theme_custom_image_sizes' );

/**
 * Force lazy loading for content images
 */
function mbn_theme_force_lazy_loading( $value, $image, $context ) {
	if ( 'the_content' === $context ) {
		return 'lazy';
	}
	return $value;
}
add_filter( 'wp_img_tag_add_loading_attr', 'mbn_theme_force_lazy_loading', 10, 3 );

/**
 * Log image uploads
 */
function mbn_theme_log_image_upload( $attachment_id ) {
	$file = get_attached_file( $attachment_id );
	$url = wp_get_attachment_url( $attachment_id );
	$type = get_post_mime_type( $attachment_id );
	
	MBN_Logger::info( 'Image uploaded', array(
		'id' => $attachment_id,
		'file' => basename( $file ),
		'type' => $type,
		'url' => $url,
		'user_id' => get_current_user_id()
	));
}
add_action( 'add_attachment', 'mbn_theme_log_image_upload' );

/**
 * Get theme image URL with fallback
 *
 * @param string $path Path relative to assets/images/
 * @param string $fallback Fallback image path
 * @return string Image URL
 */
function mbn_get_theme_image( $path, $fallback = 'placeholder.jpg' ) {
	$full_path = get_theme_file_path( 'assets/images/' . ltrim( $path, '/' ) );
	
	if ( file_exists( $full_path ) ) {
		return get_theme_file_uri( 'assets/images/' . ltrim( $path, '/' ) );
	}
	
	MBN_Logger::warning( 'Theme image not found', array( 'path' => $path ) );
	
	return get_theme_file_uri( 'assets/images/' . $fallback );
}

/**
 * Get responsive image HTML with srcset
 *
 * @param int $attachment_id Attachment ID
 * @param string $size Image size
 * @param array $attr Additional attributes
 * @return string Image HTML
 */
function mbn_get_responsive_image( $attachment_id, $size = 'large', $attr = array() ) {
	$default_attr = array(
		'loading' => 'lazy',
		'decoding' => 'async',
	);
	
	$attr = array_merge( $default_attr, $attr );
	
	return wp_get_attachment_image( $attachment_id, $size, false, $attr );
}
```

### Update `functions.php`

```php
// Add after logger include
require_once get_theme_file_path( 'inc/includes-media.php' );  // Media management
```

---

## 📊 Decision Matrix

### Should this image be in Git?

| Question | Yes → Git | No → Uploads |
|----------|-----------|--------------|
| Is it part of the site design? | ✅ | ❌ |
| Will it be the same on all environments? | ✅ | ❌ |
| Is it a logo, icon, or branding? | ✅ | ❌ |
| Is it a hero/landing page background? | ✅ | ❌ |
| Will it change frequently? | ❌ | ✅ |
| Is it blog/news content? | ❌ | ✅ |
| Is it user-generated? | ❌ | ✅ |
| Does the client manage it? | ❌ | ✅ |
| Is it specific to one environment? | ❌ | ✅ |

---

## 🎓 Summary

### Current Strengths
- ✅ Good separation of theme vs content images
- ✅ Page sync system supports both patterns
- ✅ Responsive section backgrounds work well

### Gaps to Fill
- ❌ Missing `post-thumbnails` support
- ❌ No custom image sizes for content
- ❌ No image optimization pipeline
- ❌ Limited responsive image usage

### Recommended Approach
**Hybrid Strategy:** Use both systems for their strengths
- **Git assets**: Structural, versioned, consistent
- **WordPress uploads**: Dynamic, user-managed, environment-specific

### Next Steps
1. Add featured image support (5 minutes)
2. Register custom image sizes (10 minutes)
3. Create `inc/includes-media.php` (30 minutes)
4. Document workflow for team (20 minutes)
5. Regenerate thumbnails (plugin or WP-CLI)

**Total Effort:** ~2 hours for Phase 1 implementation
