# Web Design Rules

- Use Tailwind CSS utilities for both the WordPress admin/editor and the front end.
- Mobile-first, responsive design. Verify at desktop (1920×1080) and mobile
  (Moto G Power, 360×640).
- Use the preset CSS variables and utilities from the Customizer:
  `--mbn-color-scheme-1..N` (rgba), `--mbn-font-primary`, `--mbn-font-secondary`,
  `--mbn-font-fallback`, `--mbn-size-h1..h6`, `--mbn-size-body`,
  `--mbn-container-width`, `--mbn-radius` (and the `.mbn-radius` utility).
- Custom classes: `mbn-` prefix, kebab-case, no `__`/`_`. Prefer Tailwind utilities;
  add a custom class only when utilities cannot express it.
- **No attribute styling.** Never use `style="…"`. Style with Tailwind utilities, the
  design-system classes and `--mbn-*` variables; if custom CSS is unavoidable, put it
  in a `<style>` tag inside the block's `render.php` (scoped by an `mbn-` class).
- **Runtime-built arbitrary values.** Tailwind only compiles an arbitrary value
  (`bg-[#e50b07]`, `h-[3px]`, `opacity-[0.4]`) when it sees the exact literal in a
  scanned file, and safelist *regex patterns can't match arbitrary values*. When a
  `render.php` concatenates a class from a **dynamic** value (a hex, a px size, an
  opacity), the bounded ranges + the design palette are enumerated as explicit
  safelist strings by `mbnArbitrarySafelist` in `tailwind.config.js` — add a new hex to
  `MBN_ARBITRARY_PALETTE` (or widen a range) so it compiles automatically; never rely on
  a pattern. Literal arbitrary classes (e.g. `shadow-[0px_3px_0px_#d3110e]`) need no
  safelist because they appear verbatim in source.
- Match the design's **font weights**, border styling, vectors and column ratios
  (create a custom column block for ratios like 1/3 the existing columns can't express).
- **One shared container:** header, footer and post_content use the same content
  container (`.container` / `max-w-mbn-container` + matching `px-4 sm:px-6 lg:px-8`) so
  their edges align vertically down the page.
- **Modifying existing blocks is allowed** when a block lacks a needed setting/markup —
  edit the block (attribute + control + render, then `npm run build`), backward-
  compatibly, instead of inline styles or duplicate markup.
- Accessibility: semantic landmarks, labels/alt text, color contrast, keyboard focus.
- Images: render with `wp_get_attachment_image()` (responsive `srcset`, lazy-load).
- **All design assets** (images, backgrounds, videos, vectors, icons) live in the WP
  media library — referenced from media, never raw Figma URLs, never committed in the
  theme. Icons/vectors as SVG.
- **Every image has a size setting + responsive output (ENSURED for ALL sections,
  incl. header/footer).** Render every raster image with `wp_get_attachment_image()` /
  `get_the_post_thumbnail()` at a size taken from a **per-image size attribute**, and
  expose an **image-size dropdown** for it via the shared `MediaPicker`
  `sizeValue`/`onSizeChange` props. The dropdown **default is `full`** (lists every
  registered size of the selected image); `wp_get_attachment_image()` still emits
  `srcset`+`sizes` at any size, so output stays responsive. Storage convention: a single
  image → an `imageSize` attribute; a logo/extra image → `<name>Size` (e.g.
  `daLogoSize`); repeater images → set `withSize: true` on the `items-repeater` `media`
  field, stored in `<key>Size`. Full-bleed backgrounds also pass `'sizes' => '100vw'`.
  SVGs are exempt (the dropdown just shows `full`).
- **Every media attachment shows a preview in the block (ENSURED).** Use the shared
  `MediaPicker` for all media fields (never a bare `MediaUpload` button) so each shows a
  thumbnail for **images** and an inline `<video>` for **video** attachments, plus the
  URL + "View in Media Library" link.
- **Inspector controls are spaced.** Every control/option in a block's `InspectorControls`
  must have bottom spacing so they don't sit flush against each other. Standard WP
  controls (`TextControl`, `SelectControl`, `ToggleControl`, `RangeControl`,
  `TextareaControl`) already carry a bottom margin — **don't** pass
  `__nextHasNoMarginBottom`. Any custom control (media pickers, button rows, etc.) wraps
  its markup in `<div className="mbn-control" style={{ marginBottom: 24 }}>` (the shared
  `MediaPicker` and layout media buttons already do). Example: the "Select Background
  Image" picker sits above the next field with a clear gap.
- **Section backgrounds are a responsive CSS `background-image`, not an `<img>`.** Render
  full-bleed/section backgrounds with **`mbn_responsive_bg( $element_id, $attachment_id,
  $lcp, $position )`** (in `inc/block-layout-helpers.php`): it prints a scoped `<style>`
  that swaps `background-image` by breakpoint (mobile → `medium_large`, ≥768px → `large`,
  ≥1280px → `full`) so the browser downloads **only the size it needs** (non-matching
  media-query backgrounds are never fetched), with `background-size:cover`. Give the
  block wrapper a unique id (`wp_unique_id( 'mbn-…-' )`) + `bg-cover bg-center` and pass
  it to the helper. Decorative content images (inside a card/figure) stay as
  `wp_get_attachment_image()` `<img>` with `srcset`. **SVGs are inlined as `<svg>`** (see
  `components` skill), never a background. The editor preview must match.
- **LCP background.** Every section-background block has an **"LCP (above the fold)"
  toggle** (shared `LcpControl` → `lcp` attribute). When on, pass `$lcp = true` to
  `mbn_responsive_bg()` — it prints media-scoped
  `<link rel="preload" as="image" fetchpriority="high">` tags so the above-the-fold
  background paints sooner. Set it on the one block holding the page's biggest
  above-the-fold background — never more than one per page. (For a content `<img>` LCP,
  `mbn_lcp_img_attrs()` still adds `fetchpriority="high"` + eager.)
- **Auto-WebP.** New JPEG/PNG uploads generate WebP sub-sizes (`optimizations.php`), so
  `wp_get_attachment_image()` serves WebP automatically. Run `wp media regenerate` to
  convert existing media.
- **Per-item image size.** Repeater image fields take the size dropdown too — set
  `withSize: true` on the `items-repeater` `media` field; the choice is stored in a
  companion `<key>Size` item key and the render reads it (default `'large'`).
- **No hardcoded menus / link lists.** Any navigation or repeating link list (header
  nav, footer columns, social links, legal links, etc.) comes from **Appearance →
  Menus** (`wp_get_nav_menu_object()` / `wp_get_nav_menu_items()` by template-slot meta
  or menu name), **never** a hardcoded array of `{label, url}` in the block. Provide a
  small default set only as a fallback until the menu is created; create the menus as
  `#` deadlinks (`wp menu create` / `wp menu item add-custom`), never pages.
- **Background video:** a `<video autoplay muted loop playsinline>` with a **mandatory
  preload `<img>` poster** shown until the video is ready and on reduced-motion.
- Keep the DOM shallow — minimize wrapper/child nodes.
- Smooth, modern transitions/animations; avoid layout shift (reserve image/media space).
  Match the design's interactions: hovers, button states, scroll behavior, sticky header.
- The block editor render and the front-end `render.php` must match exactly.
