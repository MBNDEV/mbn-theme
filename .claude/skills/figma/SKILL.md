---
name: figma
description: Convert a Figma design into native mbn-theme Gutenberg blocks — section by section, chrome-devtools-verified against the Figma URL. Exports EVERY asset (images, backgrounds, videos, vectors, icons) to the WP media library (no duplicates), uses ZERO attribute styling (Tailwind utilities / CSS vars / <style> only), and writes the result into a post's post_content. Use when turning a Figma frame/section into WordPress blocks or importing Figma media into WP.
---

# Figma → WordPress Blocks

Turn a Figma design into native Gutenberg blocks for this theme, with **every asset
in the WordPress media library** and the output stored as a post's `post_content`
(Figma design → specific post). **The output must match the Figma design 100% — not
"close".** Act as a senior designer with an eye for detail.

## Non-negotiables

- **ZERO attribute styling.** Never write `style="…"` and never rely on core blocks'
  inline-style output. Style only with Tailwind utilities, the design-system classes
  (`bg-scheme-N`, `font-primary`, `text-mbn-h2`, …) and `--mbn-*` variables. If a
  utility can't express it, put **real CSS in a `<style>` tag** inside the block's
  `render.php` (scoped by an `mbn-` class). This is why sections are authored as
  `mbn-ai-` blocks (their `render.php` is Tailwind-scanned), not core blocks.
- **Every asset lives in WP media** — images, background images, background videos,
  vectors and icons — referenced from media (`wp_get_attachment_image()` / attachment
  URLs), never raw Figma URLs, never committed in the theme.
- **Check EVERY node's fill — no exceptions (ENSURED).** Walk every node in the frame —
  `rectangle`, `frame`, `content`, `text`, `group`, `instance`, any module — and inspect
  its **Fill** (and stroke/effect). A fill is never "just a colour": it may be an **image
  fill**, a **video**, a **linear/radial gradient**, or a solid with **opacity**. For
  each, reproduce it exactly and **export every image/video fill to the WP media library**
  (deduped) and reference it from there — a node that looks like a flat panel often has a
  photo/texture/gradient fill that MUST be implemented, not flattened to a colour. Map:
  image fill → responsive CSS `background-image` (`mbn_responsive_bg()`) or
  `wp_get_attachment_image()`; video fill → `<video>` + mandatory poster `<img>`; gradient
  → Tailwind `bg-gradient-*` / `--mbn-*` or scoped CSS; opacity → `opacity-*` / `rgba`.
- **Read & reproduce each node's stroke, fills, vectors and effects.** Per node:
  **fills** (solid/gradient → scheme utilities + `--mbn-*`; image/video fills → export to
  media, then CSS `background-image`/`wp_get_attachment_image()`/`<video>`), **strokes**
  (border width/color/gradient, dashes, corner accents — exact), **vectors** (every
  shape/line/icon/divider → **export SVG → upload to WP media → reference**, never
  approximate), **effects** (drop/inner shadow, layer/background blur, opacity →
  `shadow-*`/`blur`/`backdrop-blur`/`opacity-*` or scoped CSS). Every asset a
  stroke/fill/vector/effect needs is uploaded to media and set on the block.
- **Validate with the chrome-devtools MCP (ENSURED).** After building, open the page and
  compare against the Figma frame with the chrome-devtools MCP — verify every fill
  actually renders (image/video/gradient present, not a flat colour), assets load (no
  4xx), and there are no console errors. Pay special attention to the **above-the-fold /
  hero section**: confirm its background image/video, gradient overlay, headline and CTA
  match the design and that the LCP background preloads.
- **Editable like a visual builder.** Each `mbn-ai-` block exposes ALL content
  (headings, text, labels, links, media, and repeatable items with add/remove/reorder)
  as editor controls — `InspectorControls` (Text/Textarea/RichText/MediaUpload) + the
  shared `ItemsRepeater` for arrays — with a live `ServerPreview` canvas. Never leave
  content editable only in the block markup.
- **Verify each section** against the Figma URL with the chrome-devtools MCP before
  moving to the next.
- **One shared container** — header, footer and post_content use the same content
  container (`.container mx-auto` / `max-w-mbn-container` + matching `px-4 sm:px-6
  lg:px-8`) so their edges align down the page; never a wider/narrower max-width for
  the chrome than the body.
- **Modifying existing blocks is allowed** — if a block (`mbn-section`/`mbn-container`/
  `mbn-columns`/`mbn-column`, `mbn-logo`/`mbn-menu`, `mbn-ai-*`) lacks a setting,
  attribute or markup a section needs, edit that block to add it (then `npm run build`)
  rather than working around it. Keep it backward-compatible.

## Workflow

1. **Read the design.** Use the Figma MCP to pull the frame/section: structure, text,
   spacing, colors, **font families + weights** (`get_variable_defs`), borders,
   vectors and image/video nodes (get_metadata → get_design_context → get_screenshot).
   **Run the prototype (Present flow)** to learn interactions (hover/scroll/sticky,
   sliders) and to detect which sections use a **background video vs image**. Map the
   layout to the theme's blocks (`mbn-section` → `mbn-container` →
   `mbn-columns`/`mbn-column`). **Reuse first; if a layout — including a column ratio
   like 1/3 — the existing blocks can't express is required, create your own
   `mbn-ai-<name>` block (`src/mbn-ai-<name>/` → `npm run build`) — do not fake it.**
2. **Export EVERY asset → WP media (no duplicates).** For each image, background,
   video, vector and icon:
   - Export from Figma (`download_assets`); upload via the WordPress MCP (or
     `wp media import`). **Icons/vectors are uploaded to the media library as
     sanitized SVG** (ENSURED + NECESSARY — never committed theme files or hardcoded
     inline `<path>`; via WP-CLI pass `--user=<admin>` so the upload passes the SVG
     capability check). Render them through **`mbn-ai-icon`**, which inlines the media
     SVG via the shared `mbn_inline_svg_attachment()` helper so it inherits
     `currentColor`.
   - **De-duplicate first** (`wp media list` / search by filename); reuse the existing
     attachment ID if found.
   - **Auto-generate SEO-friendly alt text** (AI-generated, describes the subject +
     page context, not the filename) and set it on upload via the
     `_wp_attachment_image_alt` meta; decorative vectors/patterns get an empty alt.
   - Reference via the attachment and render at a **registered size (`'large'`, never
     `'full'`)** so `wp_get_attachment_image()` ships responsive `srcset`+`sizes` (and
     auto-WebP) — never raw external Figma URLs.
   - **Background video:** upload the video AND a still **poster** image; render the
     video with a **mandatory `<img>` preload** layered over it (shows until the video
     is ready and on reduced-motion / no-autoplay).
3. **Build section by section.** Author each section as an `mbn-ai-` block with Tailwind
   utilities + the preset CSS variables (`--mbn-color-scheme-*`, `--mbn-font-*`,
   `--mbn-size-*`, `--mbn-radius`). Match font weights, borders, vectors, gradients,
   overlays, textures, spacing/padding, exact text, and `hover:`/`focus:`/scroll
   interactions. Custom classes use the `mbn-` prefix, kebab-case.
4. **Write to post_content.** Output valid block markup and save it as the target
   post's `post_content` (WordPress MCP or `wp post update`/`wp post create`). The
   editor must round-trip the blocks. (When setting content via `wp eval`, pass it
   through `wp_slash()` so block-attribute unicode escapes survive.)
5. **Verify the WHOLE post end to end with the chrome-devtools MCP — not section by
   section.** After every section is built, open the post URL **and** the Figma design
   and compare the entire page at **desktop 1920×1080**, **tablet 768×1024** and
   **mobile 360×640** — spacing, colors, font family + weight, borders, vectors/icons,
   background image/video, text, hovers/scroll/sticky. Screenshot at each breakpoint and
   fix the design accuracy until the full page matches **100%**; confirm every asset
   loads (no missing/4xx via `list_network_requests`) and no console errors. Run the
   `quality-assurance` pass for the full checklist.

## Rules

- Match the Figma design 100% — verify **each section** with the chrome-devtools MCP
  against the design URL; iterate until it matches.
- No attribute styling — Tailwind utilities / design-system classes / `--mbn-*` vars,
  custom CSS only in a `<style>` tag. Never `style=""`.
- Every asset (images, backgrounds, videos, vectors, icons) in WP media; never
  duplicate; ensure all design assets are implemented and load. Background videos
  require a preload `<img>`.
- Reuse existing blocks where possible; create your own `mbn-ai-` block (including
  custom column ratios) when the existing blocks can't express the layout. Build with
  `npm run build`.
- Follow `rules/web-design.md` (Tailwind, mobile-first, accessibility, shallow DOM)
  and `rules/security.md` (escape output, sanitize input).
