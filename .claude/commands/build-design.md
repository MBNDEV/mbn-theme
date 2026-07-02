---
description: Senior-level Figma→mbn-theme build. Section-by-section, chrome-devtools-verified against the Figma URL; every asset (images, backgrounds, videos, vectors, icons) exported to WP media; ZERO attribute styling (Tailwind utilities / CSS variables / <style> only). Drives the MBN Theme preset from the design.
argument-hint: <post_id> <figma_link> [--header] [--footer] [--mobile]
---

Arguments: `$ARGUMENTS`

**Parse the arguments first** (a Figma URL contains `?`/`&`/`=`, so do not rely on
positional `$1`/`$2` splitting): from `$ARGUMENTS`, take the first whitespace token
as the **post ID**, the `https://www.figma.com/...` token (kept whole, including any
`&t=`/`node-id`/`starting-point-node-id` suffix) as the **Figma link**, and note the
optional flags **`--header`**, **`--footer`**, and **`--mobile`**.

Build like a **senior web designer with an eye for detail**: the output must visually
match the Figma design **100%** — not "close". Build on the `figma` and `frontend`
skills and `rules/web-design.md`; this command adds the senior accuracy bar, the
design-system sync, the asset pipeline, and the **section-by-section,
chrome-devtools-verified** workflow.

## Non-negotiables (apply to every step)

- **ZERO attribute styling.** Never write `style="…"` on any element or rely on core
  blocks' inline style output. Style **only** with Tailwind utilities, the
  design-system classes (`bg-scheme-N`, `font-primary`, `text-mbn-h2`, …) and the
  `--mbn-*` CSS variables. If a utility cannot express it, add **real CSS in a
  `<style>` tag** inside the block's `render.php` (scoped by a `mbn-` class) — never an
  inline `style` attribute. This is why sections are authored as `mbn-ai-` blocks
  (their `render.php` is scanned by Tailwind), not as core blocks with inline styles.
- **Every asset lives in the WP media library.** Images, background images,
  background **videos**, vectors and **icons** are all pulled from Figma (via the
  **Figma MCP**) and exported to WP media, then referenced from there
  (`wp_get_attachment_image()` / attachment URLs) — never raw Figma URLs, never files
  committed in the theme. De-duplicate before uploading.
- **Implement the layout VISUALLY — not by Figma's width.** Reproduce the look,
  proportions, hierarchy and spacing, but build with the fluid shared container
  (`max-w-mbn-container` + responsive padding) — never copy Figma's fixed frame width
  onto a block. "As long as it is similar" is the bar for dimensions; it must be
  responsive/mobile-first.
- **Functional blocks.** Sliders, accordions, tabs, carousels, collapse → build the real
  interactive behavior; **libraries allowed** (jQuery loaded; Slick etc.), vendored
  locally under `assets/libs/`.
- **Half / partial backgrounds & gradients.** Reproduce split/half backgrounds and
  partial gradients exactly in CSS — never flatten to a solid fill.
- **Vectors 100% accurate.** Every vector/icon is exported from its Figma node to WP
  media and inlined so it matches the source exactly — never redraw, approximate, or
  strip a vector. Export grouped assets as one asset.
- **All text dynamic** — no hardcoded copy, links or media anywhere.
- **Observe EVERY node's stroke, fills, vectors and effects.** For each Figma node,
  read (`get_design_context` / `get_variable_defs`) and reproduce exactly: **fills**
  (solid/gradient colors → scheme utilities + `--mbn-*`; image fills → export to media
  and render as `<img>`/background), **strokes** (border width/color/gradient, dashed,
  corner accents — match precisely), **vectors** (every shape/line/icon/divider →
  export as **SVG**, **upload to WP media**, and reference it — never approximate with a
  box), and **effects** (drop/inner shadows, layer blur, background blur, opacity →
  Tailwind `shadow-*`/`blur`/`backdrop-blur`/`opacity-*` or scoped CSS). Any asset a
  stroke/fill/vector/effect needs (texture, gradient image, SVG) is uploaded to media
  and set on the block, not inlined as a raw URL or left as a flat color stand-in.
- **Editable like a visual builder.** Every `mbn-ai-` block must expose **all** its
  content as editor-editable attributes — headings, body copy, labels, links, media
  (image/icon/video), and **repeatable items** (cards, tabs, slides, list rows) with
  add / remove / reorder. Use `InspectorControls` (TextControl / TextareaControl /
  RichText / MediaUpload) and the shared `ItemsRepeater` for item arrays, with a live
  `ServerPreview`/`ServerSideRender` canvas so edits render immediately. **Never** bake
  a block's text or media so it can only be changed in the markup — the editor must be
  able to edit every piece of content like a page builder.
- **IMPORTANT: use `src/shared/` for reusable controls and options.** Every reusable
  editor control or option comes from `src/shared/` — `MediaPicker`
  (`media-controls.js`), `ItemsRepeater` (`items-repeater.js`), `controls.js`,
  `lcp-control.js`, `animation-control.js`, `tag-control.js`, `server-preview.js`,
  `layout.js`, `save.js`. Import them (`../shared/…`; from `src/components/<name>/`:
  `../../shared/…`) — **never re-implement a picker/repeater/control inline in a
  block's `edit.js`**. If a control is missing or lacks an option, add/extend it in
  `src/shared/` (backward-compatibly) so every block gets it.
- **End-to-end test the WHOLE post with the chrome-devtools MCP — not per section.**
  Build every section first, then run one comprehensive pass over the finished post:
  screenshot it top to bottom and compare to the Figma design at **desktop (1920×1080),
  tablet (768×1024) and mobile (360×640)**, fixing the design accuracy at each
  breakpoint until the whole page matches. Keep going until the entire post is
  implemented and verified (see Step 5).
- **One shared container.** The header, the footer and every post_content section
  must use the **same content container** so their left/right edges line up vertically
  down the page. Use the theme container — `.container mx-auto` (capped at
  `--mbn-container-width`, i.e. `max-w-mbn-container`) with the same horizontal padding
  as the body sections (`px-4 sm:px-6 lg:px-8`). Do **not** give the header/footer a
  wider/narrower max-width than the body. Verify alignment in chrome-devtools.
- **Modifying existing blocks is allowed.** When a section needs a setting, attribute,
  control or markup an existing block (`mbn-section`/`mbn-container`/`mbn-columns`/
  `mbn-column`, `mbn-logo`/`mbn-menu`, or an `mbn-ai-*` block) doesn't yet provide,
  **edit that block** to add it (new `block.json` attribute + `edit.js` control +
  `render.php` output, then `npm run build`) rather than working around it with inline
  styles or duplicate markup. Keep changes backward-compatible.
- **Eye for detail:** font families AND **weights**, border styling, vectors,
  gradients, overlays, textures, spacing/padding, line breaks, text accuracy,
  interactions (hover/focus/scroll), sticky-on-scroll behavior, and modules
  (sliders, patterns).

> **Starting a new project?** Run `scripts/reset.sh` first (it prompts for confirmation)
> to remove the previous project's `mbn-ai-` blocks/components (and optionally its
> content/media/menus/preset via `--content`/`--media`/`--menus`/`--preset`) so you build
> from a clean baseline. `scripts/reset.sh --dry-run` previews; `--yes` skips the prompt.

## Scope (flags)

- No flag → full build: **header + footer first**, then the body into the post.
- `--header` and/or `--footer` → reimplement only those block templates and stop.
  Both flags may be combined.
- `--mobile` → **responsiveness pass**: read the design's mobile/breakpoint frames
  from the Figma link and update the page's responsive behavior to match (see Step 6).
  Combine with a region flag to scope it, or run it alone to refine an existing build
  for mobile only.

## Preconditions

1. If the post ID or Figma link is missing from `$ARGUMENTS`, ask and stop.
2. `wp post get <post_id> --field=post_type` — confirm it exists, else stop.
3. Read the design via the Figma MCP. If Figma is not authenticated, prompt sign-in
   and stop. Parse the `node-id` and read that node (get_metadata → get_design_context
   → get_screenshot for ground truth) and `get_variable_defs` (fonts/weights, colors,
   sizes, spacing).
4. **Run the Figma prototype (Present flow).** Open the prototype from the provided
   link and navigate it to learn the intended interactions and to detect **which
   sections use a background video vs a background image** (and any sliders, hover
   states, scroll/sticky behavior). Note these before building.
5. **WordPress:** prefer the WordPress MCP; otherwise WP-CLI (`wp <cmd>`). If the
   active PHP CLI cannot reach the DB, run `wp` through whatever PHP runtime the site
   uses. Never store assets in the theme — media library only.

## Step 1 — Sync the MBN Theme preset from the design (do this BEFORE building)

The design system lives in the **Appearance → MBN Theme** preset, never hardcoded.

- **Fonts + weights (Figma → Fonts Library → preset).** Read the primary (heading) and
  secondary (body) font families **and the weights actually used** from Figma
  (`get_variable_defs`). Install each family in the **WordPress Fonts Library** if
  missing (include every weight the design uses, e.g. 400/600/700/800), then set the
  preset `font_primary`/`font_secondary`. Fallback metrics + the `h1–h6` rules are
  emitted in `wp_head` by the theme — keep them; do not duplicate.
- **Colors (Figma → preset).** Extract the palette. The **most-used accent** becomes
  **color scheme 1**; add every other distinct color as additional schemes (scheme 2,
  3, …) in order. They surface as `--mbn-color-scheme-N` + the `scheme-N` utilities.
- **Sizes (Figma → preset).** Map the Figma type scale to the preset `h1–h6` and body
  sizes. Headings/body then render at the design sizes site-wide.

Set these with the WordPress MCP or `wp option patch update mbn_settings <key> <value>`
(the option is an array). Re-build CSS after, since utilities reference the vars.

## Step 1b — Build the reusable components (run `/build-components` AFTER the preset)

Immediately after the preset is synced — and **before** the header/footer and body
sections — run **`/build-components <figma_link>`** to scan the design's reusable
components (buttons, icons, inputs, badges, vector-lines, patterns…) and generate the
generic `mbn-ai-` component blocks under `src/components/`. The header, footer and every
section then **compose** these components (`do_blocks('<!-- wp:mbn-theme/mbn-ai-button …
/-->')`) instead of re-implementing buttons/icons/etc. (preset → components → chrome →
sections). All icons/vectors are uploaded to the media library as **sanitized SVG** and
rendered through `mbn-ai-icon` (see the `components` skill).

## Step 2 — Header & footer FIRST (as dedicated blocks)

Create a **dedicated block per region** that matches the design and place it in the
corresponding block template — do not hand-stitch generic sections for chrome.

- Build `mbn-ai-header` and `mbn-ai-footer` blocks (see naming in Step 4), each
  reproducing that region's layout, logos, nav, CTAs, backgrounds and hovers.
- **Header behavior:** check the prototype — if the header is **sticky / changes on
  scroll** (shrinks, gains a background, hides/shows), replicate it (CSS `position`
  + a small scroll listener in the block's `view.js`).
- Put `<!-- wp:mbn-theme/mbn-ai-header /-->` into the `header` block template and
  `<!-- wp:mbn-theme/mbn-ai-footer /-->` into the `footer` template
  (`wp post update <template_id> …`). Reuse `mbn-logo`/`mbn-menu` inside them; set the
  template logo meta (`_mbn_template_logo_id`) and menus meta (`_mbn_template_menus`).
- **Menus (never hardcode link lists):** every nav and repeating link list — header
  nav, footer columns, **Social Menu**, **Footer Legal**, etc. — comes from Appearance
  → Menus, not a hardcoded array in the block. Create them (`wp menu create` /
  `wp menu item add-custom`) named `Header Menu`, `Footer Menu`, `Footer Services`,
  `Social Menu`, `Footer Legal`… as temporary `#` custom-link deadlinks with the
  design's labels; the block reads them by template-slot meta or menu name (social
  icons map by a slug derived from the label). Keep a default fallback only until the
  menu exists. **Do not create pages.**
- Verify the header/footer per Step 4 before continuing.

## Step 3 — Export EVERY asset to the WP media library (before building sections)

Inventory every asset the design references and move it into WP media (no duplicates):

- **Images & background images** — export from Figma (`download_assets`), upload via
  the WordPress MCP (or `wp media import`), reference by attachment ID/URL.
- **Background videos** — if the prototype shows a video background, export the source
  video and upload it to media; reference the media URL. **A poster/preload image is
  mandatory** — also export a still frame and render it as an `<img>` (see Step 4).
- **Vectors & icons** — export as **SVG** and upload to media (or inline the SVG in
  the block render when it must inherit `currentColor`); custom vectors/patterns from
  the design must be reproduced exactly, not approximated.
- **De-duplicate first** (`wp media list` / search by filename) and reuse existing
  attachment IDs. Trim transparent padding where the design uses a tight crop.
- **Auto-generate SEO-friendly alt text on upload.** For every photographic/content
  image set a concise, descriptive, **AI-generated** alt that reflects the subject and
  page context (not the filename) — `wp post update <id> --post_excerpt='…'` sets an
  attachment's alt-equivalent, or set the `_wp_attachment_image_alt` meta
  (`wp post meta update <id> _wp_attachment_image_alt '…'`). Decorative
  vectors/patterns/textures get an empty alt (`alt=""`).
- **Responsive by default.** Images are rendered with `wp_get_attachment_image()` at a
  registered size (`'large'`, never `'full'`) so they ship `srcset`+`sizes`; new uploads
  auto-generate WebP. See `rules/web-design.md`.
- Goal: a single Figma→WP-media transfer of *all* media so nothing references Figma at
  runtime. (If a WordPress MCP asset-import tool is available, use it for this.)

## Step 4 — Build the body SECTION BY SECTION (build → verify → polish, then next)

Work **one section at a time**. Do not build the whole page and verify at the end.

For **each** section, in order, top to bottom:

1. **Build the section** as an `mbn-ai-<name>` block (`src/mbn-ai-<name>/`:
   `block.json` + `index.js` + `edit.js` JSX + `render.php`; dynamic = `save` returns
   null + `ServerSideRender` preview), then `npm run build`. Reuse the layout blocks
   (`mbn-section` → `mbn-container` → `mbn-columns`/`mbn-column`) where they fit.
   - **Columns:** match the design's column layout exactly. **If a ratio the existing
     columns cannot express is required (e.g. 1/3 + 2/3, 1/4s), create your own column
     block** for it — do not fake it with equal columns.
   - **Styling:** Tailwind utilities + design-system classes only (colors →
     `bg-scheme-N`/`text-scheme-N`/`border-scheme-N`; fonts → `font-primary`/
     `font-secondary` with the right **weight** utility; sizes → `text-mbn-h*`). Custom
     CSS only via a `<style>` tag in `render.php`. **No `style=""` anywhere.**
   - **Detail pass:** font weights, border styling, vectors/icons (from media),
     background image **or** video (+ preload `<img>`), gradients, overlays, textures,
     spacing/padding, line breaks, exact text, and `hover:`/`focus:` + scroll
     interactions with smooth transitions.
   - **Modules:** for sliders/carousels/modals, use a jQuery-supported library (e.g.
     Slick) — **jQuery is enqueued site-wide**. Drop the library's JS/CSS assets in the
     theme's **`libs/`** directory and enqueue them from the block (depend on `jquery`),
     with your init in the block's `view.js`; reproduce patterns/animated vectors
     faithfully.
   - **Background video** renders as: a `<video autoplay muted loop playsinline>` with
     a **mandatory `<img>` poster** (the preload still) layered so the image shows
     until the video is ready and on reduced-motion / no-autoplay.
2. **Save** the section into the post's `post_content` (WordPress MCP / `wp post
   update`). Blocks must round-trip in the editor.
3. **Verify the section with the chrome-devtools MCP — open BOTH the edited post URL
   and the Figma URL** and compare *this section* side by side at desktop (1920×1080)
   and Moto G Power (360×640). Check spacing, padding, font family + weight, colors,
   borders, vectors/icons, background image/video, text, and interactions
   (hover/scroll/sticky). Confirm every asset loads (no missing/4xx via
   `list_network_requests`) and there are no console errors.
4. **Polish** until the section matches the design **100%**, then move to the next
   section. Do not advance on "looks about right".

- **Heading hierarchy / SEO (whole page):** exactly one `h1`; nest `h2`–`h6` in order;
  meaningful `alt` text on every image; semantic landmarks.

## Step 5 — Final verification (whole page) + report

- Run `npm run build` if any classes/blocks changed.
- **Full-page QA with the chrome-devtools MCP** (the `quality-assurance` skill): open
  the post URL **and the Figma link** and compare end-to-end at desktop (1920×1080)
  and Moto G Power (360×640); also **re-run the Figma prototype** and walk the page's
  interactions (hovers, scroll, sticky header, sliders) against it. Confirm editor
  render === front-end render, all assets load, no console/4xx errors, no layout shift.
- `composer run lint` (PHP/JS). Report: preset changes (fonts/weights/colors/sizes),
  assets exported to media (IDs), menus created, blocks created (incl. any custom
  column block), templates + post updated, and the QA result with before/after
  screenshots per section.

## Step 6 — Responsiveness (`--mobile`)

When `--mobile` is passed, make the page's responsive behavior match the design's
mobile/breakpoint frames:

- Read the **mobile frame(s)** from the Figma link (`get_metadata` →
  `get_design_context` → `get_screenshot`). Capture the mobile layout, stacking order,
  spacing, font sizes/weights, hidden/shown elements, and any mobile-only nav
  (hamburger) or CTAs.
- Translate them into **mobile-first Tailwind utilities** (base = mobile, `md:`/`lg:`
  for larger) — column stacking, reordered content, adjusted paddings/margins, type
  scale, touch-friendly tap targets. No attribute styling; utilities or a `<style>`
  tag only. Update the `mbn-ai-` block source + `npm run build`.
- **Verify section by section with the chrome-devtools MCP at Moto G Power (360×640)**
  against the Figma mobile frame; iterate until each matches 100%, with no layout
  shift and all assets loading. Then re-check desktop (1920×1080) for no regression.

## Rules

- Pixel-accurate or it's not done; verify **every section** against the Figma URL with
  the chrome-devtools MCP, not just the finished page.
- **No attribute styling** — Tailwind utilities / design-system classes / `--mbn-*`
  vars, with custom CSS only in a `<style>` tag. Never `style=""`.
- **All media in WP media** — images, backgrounds, videos, vectors, icons; no
  duplicates; nothing referencing Figma at runtime; nothing committed in theme files.
  Background videos require a preload `<img>`.
- Reuse layout blocks; **modify an existing block** to add a missing setting/markup,
  or create `mbn-ai-` blocks (incl. custom column ratios) when the existing blocks
  can't express the design. Menus are `#` deadlinks; no pages.
- Header, footer and post_content share **one container** (`.container` /
  `max-w-mbn-container` + matching padding) so their edges align down the page.
- Follow `rules/web-design.md` + `rules/security.md`. `composer run lint` before any
  commit; never `git push` (open a PR); don't commit unless asked.
