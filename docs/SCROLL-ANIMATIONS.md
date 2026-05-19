# Scroll Animations — Implementation Guide

The theme ships an Elementor-style entrance-animation system for blocks. The animation set, duration tiers, and delay control mirror what Elementor exposes under **Advanced → Motion Effects**, with keyframes derived from [Animate.css](https://animate.style/) (MIT, Daniel Eden) — the same library Elementor builds on.

Animations fire **once** when the block scrolls into view. Above-the-fold blocks are revealed instantly on first paint (no animation on refresh). **Note:** The system currently ignores OS-level "Reduce Motion" settings to ensure animations work consistently for all users.

---

## How It Works

| File | Role |
|---|---|
| [`assets/css/scroll-animations.css`](../assets/css/scroll-animations.css) | Hidden state, visible state, all keyframes, duration variants |
| [`assets/js/scroll-animations.js`](../assets/js/scroll-animations.js) | jQuery + IntersectionObserver — adds `.is-visible` to elements as they enter the viewport |
| [`blocks/shared/AnimationControls.js`](../blocks/shared/AnimationControls.js) | Reusable React component for the Motion Effects panel (editor UI) |
| [`inc/includes-animation-helpers.php`](../inc/includes-animation-helpers.php) | PHP helper function `mbn_get_animation_attrs()` for generating data attributes |
| [`functions.php`](../functions.php) | Enqueues both assets on `wp_enqueue_scripts` (frontend only), includes inline CSS override for animation-name rules |

The render layer puts three optional data attributes on the block wrapper:

| Attribute | Values | Effect |
|---|---|---|
| `data-animate` | `fadeInUp`, `zoomIn`, `bounceInLeft`, `rotateIn`, `lightSpeedIn`, `rollIn`, … (full list below) | Selects the keyframe |
| `data-animate-duration` | `slow` (2s), `fast` (0.75s), or omitted (1.25s normal) | Duration tier |
| `data-animate-delay` | Integer milliseconds, e.g. `200` | Wait before playing — useful for staggering siblings |

---

## Available Animations

| Group | Variants |
|---|---|
| **Fading** | `fadeIn`, `fadeInDown`, `fadeInLeft`, `fadeInRight`, `fadeInUp` |
| **Zooming** | `zoomIn`, `zoomInDown`, `zoomInLeft`, `zoomInRight`, `zoomInUp` |
| **Bouncing** | `bounceIn`, `bounceInDown`, `bounceInLeft`, `bounceInRight`, `bounceInUp` |
| **Sliding** | `slideInDown`, `slideInLeft`, `slideInRight`, `slideInUp` |
| **Rotating** | `rotateIn`, `rotateInDownLeft`, `rotateInDownRight`, `rotateInUpLeft`, `rotateInUpRight` |
| **Light Speed** | `lightSpeedIn` |
| **Specials** | `rollIn` |

**Total:** 27 animation variants

---

## Adding Animation Controls to a Block

The system uses **centralized shared components** to avoid code duplication. Follow this pattern for every block. The reference implementations are in [`blocks/intro-section`](../blocks/intro-section), [`blocks/board-members`](../blocks/board-members), and [`blocks/who-we-serve`](../blocks/who-we-serve).

### 1. `block.json` — register three attributes

```json
"animationType": {
  "type": "string",
  "default": ""
},
"animationDuration": {
  "type": "string",
  "default": ""
},
"animationDelay": {
  "type": "number",
  "default": 0
}
```

**Note:** `animationType` defaults to empty string (animations off by default). Users must explicitly choose an animation.

### 2. `edit.js` — use the shared AnimationControls component

```js
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import AnimationControls from '../shared/AnimationControls';

export default function Edit({ attributes, setAttributes }) {
  const { animationType, animationDuration, animationDelay } = attributes;

  return (
    <>
      <InspectorControls>
        {/* Your other panels here */}
        
        <AnimationControls
          animationType={animationType}
          animationDuration={animationDuration}
          animationDelay={animationDelay}
          setAttributes={setAttributes}
        />
      </InspectorControls>

      {/* Your block content */}
    </>
  );
}
```

The `AnimationControls` component handles all the UI (SelectControl for animation type, duration, and NumberControl for delay). No need to duplicate the 70+ lines of animation panel code in each block.

### 3. `render.php` — use the helper function

```php
<?php
// Get animation attributes using centralized helper
$anim_attrs = mbn_get_animation_attrs( $attributes );

$wrapper_attributes = get_block_wrapper_attributes(
  array_merge(
    array(
      'class' => 'your-block-classes',
      // other attributes
    ),
    $anim_attrs
  )
);
?>

<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
  <!-- Your block content -->
</section>
```

The `mbn_get_animation_attrs()` helper function automatically:
- Reads `animationType`, `animationDuration`, `animationDelay` from block attributes
- Returns an array of sanitized `data-*` attributes
- Only outputs attributes if `animationType` is set
- Validates duration values (`slow` or `fast`)

### 4. Build

```bash
npm run build
```

**Important:** The webpack config skips the `blocks/shared/` directory (not a block). The shared `AnimationControls.js` component is imported and bundled into each block's `index.js` automatically.

---

## Architecture Details

### Centralized Components

**React Component:** [`blocks/shared/AnimationControls.js`](../blocks/shared/AnimationControls.js)
- Exports a `<AnimationControls>` component
- Props: `animationType`, `animationDuration`, `animationDelay`, `setAttributes`
- Renders PanelBody with all 27 animation options grouped by type
- Shows duration and delay controls only when animation is selected

**PHP Helper:** [`inc/includes-animation-helpers.php`](../inc/includes-animation-helpers.php)
- Function: `mbn_get_animation_attrs( $attributes )`
- Returns array: `['data-animate' => 'fadeInUp', 'data-animate-duration' => 'slow', 'data-animate-delay' => '200']`
- Handles all sanitization and validation
- Returns empty array if no animation selected

### CSS Override

Due to specificity issues in the main CSS file, [`functions.php`](../functions.php) includes inline CSS that forces animation-name rules with `!important`:

```php
wp_add_inline_style(
  'blacklineguardianfund-scroll-animations',
  '[data-animate="fadeInUp"].is-visible{animation-name:fadeInUp!important}...'
);
```

This ensures all 27 animation types work correctly. The base @keyframes definitions come from `scroll-animations.css`, but the animation-name assignment happens via inline CSS.

### JavaScript Behavior

**Above-fold detection:** Elements in the viewport on page load get the `.no-anim.is-visible` classes instantly (no animation flash).

**Scroll detection:** Uses IntersectionObserver with:
- `threshold: 0.08` (triggers when 8% of element is visible)
- `rootMargin: '0px 0px -40px 0px'` (40px buffer at bottom)

**One-time animations:** Once `.is-visible` is added, the observer stops watching that element (animations don't repeat on subsequent scrolls).

**Reduced Motion:** Currently **ignored** (hardcoded to `false`). All users see animations regardless of OS accessibility settings. To respect user preferences, change this line in `scroll-animations.js`:

```js
// Current: Forces animations for all users
var prefersReducedMotion = false;

// To respect accessibility settings:
var prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
```

---

## Stagger Children

Add `data-animate-stagger` to the parent and `data-animate` (with any variant) to each child. Each child gets an additional `100ms × index` delay automatically. Children's own `data-animate-delay` value is added to the stagger delay, not replaced.

---

## Current Implementation Status

### Blocks with Animation Support (9 total)

All blocks fully integrated with centralized animation system:

1. **board-members** - ✅ Uses AnimationControls component + mbn_get_animation_attrs()
2. **column-sections** - ✅ Uses AnimationControls component + mbn_get_animation_attrs()
3. **contact-form-section** - ✅ Uses AnimationControls component + mbn_get_animation_attrs()
4. **donation-options** - ✅ Uses AnimationControls component + mbn_get_animation_attrs()
5. **image-text-bullets** - ✅ Uses AnimationControls component + mbn_get_animation_attrs()
6. **intro-section** - ✅ Uses AnimationControls component + mbn_get_animation_attrs()
7. **mission-section** - ✅ Uses AnimationControls component + mbn_get_animation_attrs()
8. **simple-hero-section** - ✅ Uses AnimationControls component + mbn_get_animation_attrs()
9. **who-we-serve** - ✅ Uses AnimationControls component + mbn_get_animation_attrs()

### Files Architecture

```
blocks/
├── shared/
│   └── AnimationControls.js          # Shared React component (NOT a block)
├── board-members/
│   ├── block.json                    # Has animation attributes
│   ├── edit.js                       # Imports AnimationControls
│   └── render.php                    # Uses mbn_get_animation_attrs()
├── intro-section/
│   ├── block.json                    # Has animation attributes
│   ├── edit.js                       # Imports AnimationControls
│   └── render.php                    # Uses mbn_get_animation_attrs()
└── ...                               # All 9 blocks follow same pattern

assets/
├── css/
│   └── scroll-animations.css         # Keyframes + base styles
└── js/
    └── scroll-animations.js          # jQuery + IntersectionObserver

inc/
└── includes-animation-helpers.php    # mbn_get_animation_attrs() function

functions.php                         # Enqueues CSS/JS + inline CSS override
webpack.config.js                     # Skips blocks/shared/ directory
```

---

## Troubleshooting

### Animations not working

**Check browser console for errors:**
```javascript
// Check if CSS file loaded
var css = Array.from(document.styleSheets).find(s => s.href && s.href.includes('scroll-animations.css'));
console.log('CSS loaded:', !!css);

// Check if JS file loaded
console.log('jQuery:', typeof jQuery);

// Check if elements have data-animate
console.log('Elements with [data-animate]:', document.querySelectorAll('[data-animate]').length);
```

**Common issues:**

1. **CSS file not loading** → Check `functions.php` enqueue function
2. **jQuery not loaded** → Animation script depends on jQuery
3. **Elements missing data-animate** → Block not saved after adding animation
4. **Animations play but immediately** → Element might be above-fold (instant reveal is intentional)
5. **IntersectionObserver callback not firing** → Check browser console for JavaScript errors

### CSS specificity issues

The main `scroll-animations.css` file's animation-name rules have specificity conflicts with other theme CSS. The workaround is inline CSS with `!important` in `functions.php`:

```php
$animation_css = '[data-animate="fadeInUp"].is-visible{animation-name:fadeInUp!important}';
// ... all 27 variants
wp_add_inline_style( 'blacklineguardianfund-scroll-animations', $animation_css );
```

This is intentional and ensures all animations work correctly.

### Build issues

**"Cannot find module 'AnimationControls'"**
- Ensure `blocks/shared/AnimationControls.js` exists
- Check import path: `import AnimationControls from '../shared/AnimationControls';`
- Run `npm run build` (not just `npm run build:blocks`)

**"Unable to locate block.json glob"**
- This happens if webpack tries to process `blocks/shared/` as a block
- Verify `webpack.config.js` has the skip logic:
  ```js
  if ( blockName === 'shared' ) {
    return;
  }
  ```

### Testing animations

**Force animation on element immediately (skip scroll detection):**
```javascript
document.querySelector('[data-animate]').classList.add('is-visible');
```

**Check if animation keyframe exists:**
```javascript
var sheets = Array.from(document.styleSheets);
var found = false;
sheets.forEach(sheet => {
  try {
    Array.from(sheet.cssRules || []).forEach(rule => {
      if (rule.name === 'fadeInUp') {
        console.log('Found @keyframes fadeInUp');
        found = true;
      }
    });
  } catch(e) {}
});
console.log('Keyframe exists:', found);
```

**Check computed styles:**
```javascript
var el = document.querySelector('[data-animate].is-visible');
var styles = getComputedStyle(el);
console.log('animation-name:', styles.animationName);
console.log('animation-duration:', styles.animationDuration);
```

---

## Performance Considerations

- **IntersectionObserver** is highly efficient (better than scroll event listeners)
- **One-time animations** - Observer stops watching after animation plays
- **Above-fold instant reveal** - No unnecessary animations on page load
- **CSS animations** - GPU-accelerated (better than JavaScript animations)
- **jQuery dependency** - ~30KB gzipped, likely already loaded by WordPress core or plugins

---

## Future Improvements

Potential enhancements for consideration:

1. **Respect prefers-reduced-motion** - Currently ignored for consistency, could be made configurable
2. **Animation repeat option** - Allow animations to play multiple times when scrolling in/out
3. **Custom animation timing** - Beyond slow/fast/normal presets
4. **Exit animations** - Animate when scrolling out of viewport
5. **Animation easing control** - Different timing functions (ease-in-out, cubic-bezier, etc.)
6. **Mobile-specific animations** - Different animations for mobile vs desktop
7. **Remove inline CSS workaround** - Fix specificity in main CSS file instead of using !important

```php
<div class="grid grid-cols-3" data-animate-stagger>
  <?php foreach ( $cards as $card ) : ?>
    <div data-animate="fadeInUp">
      <?php echo esc_html( $card['title'] ); ?>
    </div>
  <?php endforeach; ?>
</div>
```

The stagger parent itself should not have `data-animate` — only the children.

---

## Behavior Summary

| Scenario | Result |
|---|---|
| Refresh — block already on screen | Shown instantly, no animation |
| Scroll down — block enters viewport | Animation plays once |
| User has *Reduce Motion* enabled | Everything visible immediately, no transition |
| Browser without IntersectionObserver | Falls back to instant reveal of everything |

---

## Customising the CSS

`assets/css/scroll-animations.css` is the single source for all motion. Edit it directly to:

- Change the default duration: edit `--animation-duration` in `[data-animate].is-visible`
- Add a new keyframe: define a new `@keyframes` block + matching `[data-animate="<name>"].is-visible { animation-name: <name>; }` selector
- Soften the slide distance: replace `translate3d(0, 100%, 0)` with `translate3d(0, 30px, 0)` in the `fadeInUp` keyframe (see [Element.how guide](https://element.how/elementor-improve-entrance-animations/) — same trick, applies the moment you want subtler motion)

---

Sources:
- [Elementor — Add entrance animations](https://elementor.com/help/entrance-animations/)
- [Elementor — `animation.php` source (full variant list)](https://github.com/elementor/elementor/blob/master/includes/controls/animation.php)
- [Animate.css](https://animate.style/) — keyframe library
