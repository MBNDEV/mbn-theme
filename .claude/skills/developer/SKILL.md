---
name: developer
description: Senior WordPress/Gutenberg engineering practices for mbn-theme — how to design, build, and verify native no-build blocks and theme code to a senior standard. Use for any block, template, or theme-code work.
---

# Senior Developer — mbn-theme

Build like a senior engineer: understand the system first, reuse before adding,
and verify before claiming done. Optimize for the next developer reading the code.

This skill covers the engineering *method* and block/media specifics. It does not
restate the shared rules — defer to them:

- **Styling, responsive, accessibility, shallow DOM, preset `--mbn-*` variables:**
  `skills/frontend` + `rules/web-design.md`.
- **Security** (sanitize/escape, nonces, capabilities, never store secrets):
  `rules/security.md`.
- **Lint / commit / PR discipline** (no direct push, QA only via `/testing`):
  `rules/git-workflow.md`.

## Work method

1. **Understand before changing.** Read surrounding code; match its idioms, naming,
   and structure. Know how a block renders (`block.json` + `render.php`), how the
   editor mirrors it (each block's `edit.js`), and how presets flow
   (`--mbn-*` from the Appearance → MBN Theme settings page).
2. **Reuse first.** Prefer existing blocks (`mbn-section`/`mbn-container`/
   `mbn-columns`/`mbn-column`, `mbn-logo`, `mbn-menu`) and existing helpers. Add a
   new block only for a module the existing set cannot express (slider, video,
   gallery, tabs/accordion, other interactive).
3. **Smallest correct change.** No drive-by rewrites; keep diffs focused and remove
   dead code you create.
4. **Verify, then report.** Run `composer run lint`; for dynamic logic, exercise it
   (WordPress MCP, or WP-CLI `wp <cmd>` — if the active PHP CLI can't reach the DB,
   run `wp` via whatever PHP runtime the site uses). State what you tested; if a
   step was skipped, say so.

## Blocks (JSX, built with @wordpress/scripts)

- Structure: `src/<name>/` — `block.json` + `index.js` (registerBlockType) +
  `edit.js` (JSX `Edit`) + `render.php`; shared pieces in `src/shared/`. Import from
  `@wordpress/*` (wp-scripts externalizes them and writes deps into `*.asset.php`).
  `npm run build` compiles to `build/<name>/`; `block-registry.php` registers
  `build/<name>/`. Run `npm run build` (or `npm run dev` to watch) after edits.
- **AI-authored blocks use the `mbn-ai-` prefix.** New layout/interactive blocks
  only when existing blocks can't express the design.
- **Editor ↔ front-end parity is mandatory.** Static blocks produce identical
  markup/classes in `edit.js` and `render.php`. Dynamic blocks: `save` returns
  `null` and `edit.js` uses `ServerSideRender` so the canvas matches output.
- **No backend async** in render paths; keep `render.php` cheap and synchronous.
  Cache expensive per-request lookups with a static memo where safe.
- **jQuery is enqueued site-wide on the front end** (`custom_theme_enqueue_frontend_scripts`),
  so **jQuery-supported libraries are free to use** for interactive modules — sliders
  (Slick, Swiper-with-jQuery), lightboxes, etc. **Put third-party library assets (JS/CSS)
  in the theme's `libs/` directory**, then enqueue them from the block (declare
  `array( 'jquery' )` as the dependency) and keep the init in the block's `view.js`/asset
  (not inline); reserve space to avoid CLS. Prefer a small vanilla solution when one is
  trivial; reach for a library when it earns its weight.

## Media & video in blocks

- Assets live in the WP media library, never in theme files; icons are SVG.
- Video blocks expose a **preload/poster image** option and use a poster→video
  pattern (`wp_get_attachment_image()` for the still). Lazy-load video via
  `assets/js/mbn-video.js` (`data-src` → `src`) and reserve space to avoid CLS.
- **Every image needs a size setting + preview (ALL blocks, incl. header/footer).**
  Use the shared `MediaPicker` for every media field (never a bare `MediaUpload`) — it
  previews images **and** video and carries the **size dropdown** (`sizeValue`/
  `onSizeChange`, default `full`). Render via `wp_get_attachment_image()` at that size
  attribute (single → `imageSize`; logo/extra → `<name>Size`; repeater → `withSize:true`
  → `<key>Size`); WP still emits `srcset`+`sizes`. Full-bleed backgrounds add
  `'sizes' => '100vw'`. SVGs exempt (dropdown shows `full` only).
- **LCP.** Add the shared `LcpControl` (`lcp` boolean) to every image/background block;
  in render, merge `mbn_lcp_img_attrs( $is_lcp )` (→ `fetchpriority="high"` + eager)
  into the background or first/largest image's attrs. One LCP block per page.
- **Auto-WebP** is handled globally in `optimizations.php` (the
  `image_editor_output_format` filter + a WebP-sibling URL swap) — just render via
  `wp_get_attachment_image()` and uploads serve WebP. `wp media regenerate` for old media.
- **Per-item size dropdown.** For repeater image fields set `withSize: true`; the chosen
  size lands in `<key>Size` and the render passes it to `wp_get_attachment_image()`.

## Menus & link lists — never hardcode

Any nav or repeating link list (header/footer nav, footer columns, **social links**,
**legal links**) is driven from **Appearance → Menus**, not a hardcoded array. Resolve
by the template's menus meta slot (`mbn_get_template_menus()`) or by menu name
(`wp_get_nav_menu_object()` → `wp_get_nav_menu_items()`), flatten the top-level items to
`{label, url}`, and keep a tiny default set only as a fallback until the menu exists.
Social icons map by a slug derived from the item label (e.g. `Facebook` →
`mbn-social-facebook`). Create menus as `#` deadlinks (`wp menu create` /
`wp menu item add-custom`) — never pages.

## Speed & tokens — work cheap

Tokens are the scarce resource; the environment is set up to keep them low (see
`development.md` for the full setup, required plugins, and Claude MCP/tooling).

- **Move whole pages through the content endpoint, never paste them.** Upsert a post
  *and its media* in one call instead of streaming block JSON / base64 through the chat:
  `wp mbn-content export <id> --file=…` / `wp mbn-content import --file=…`, or the
  authorized REST routes `GET|POST /wp-json/mbn/v1/posts[/<id>]`
  (`inc/includes-content-io.php`). Upsert is keyed by `post_id`; media reuse is by
  filename (existing attachment reused, else the base64 uploaded), and content media
  ids / URLs are remapped to local ids. Auth: `edit_posts` (app password / logged-in)
  or an `X-MBN-Token` header matching the `MBN_IO_TOKEN` constant — never hardcode it.
- **Inspect with `get_skeleton` and `run_pipeline`, not `Read`.** Read full files only
  to edit an exact line. Don't fan out broad searches.
- **One `npm run build` after a batch of edits**, not after each one. Verify the whole
  post in one chrome-devtools pass at the end (and only on request via `/testing`).
- **Required plugins:** Gravity Forms + Gravity Forms SMTP (all forms go through GF,
  keeping the design; styled static markup is only the fallback).
