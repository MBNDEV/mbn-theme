---
name: components
description: Reusable mbn-theme component blocks captured from Figma's reusable components (buttons, patterns, vector-lines, strokes, badges, cards, inputs, icons). Defines how to scan Figma for components, dedupe against existing mbn-ai- blocks, and build generic attribute-driven component blocks that sections and /build-design compose. Use whenever building or reusing a repeated design element, or running /build-components.
---

# Components — reusable mbn-ai- building blocks

Repeated design elements (buttons, background patterns, divider/accent lines, border
strokes, badges, cards, inputs, icons) are built **once** as generic `mbn-ai-`
component blocks and **reused** everywhere — never re-implemented per section. This is
the foundation `/build-components` produces and `/build-design` composes.

## Principles

- **Reuse first, never reimplement.** Before building, check the **`ai-blocks`
  registry** (`.claude/skills/ai-blocks/SKILL.md`) and
  `src/components/mbn-ai-*`. If a component exists, use it; if it needs a new variant,
  **extend that `mbn-ai-` block** (add an attribute/variant) — modifying `mbn-ai-`
  blocks is allowed; never edit `mbn-` blocks. Sections **compose** components via
  `do_blocks('<!-- wp:mbn-theme/mbn-ai-<name> ' . wp_json_encode( $attrs ) . ' /-->')`
  — never hand-roll a component's markup inside a section (e.g. the header CTA renders
  the `mbn-ai-button` block, it does not duplicate the button markup).
- **Generic + attribute-driven.** No site-specific values baked in. Variations
  (label, href, variant, size, color, image id, orientation, style) are block
  attributes with sensible defaults; the design's actual values are passed at the call
  site.
- **No hardcoded labels — they live on the block.** CTA button text and links (and any
  other copy) are **block attributes** edited in that block's own settings (e.g. the
  header's Call-to-action panel: `ctaLabel` / `ctaUrl`), never hardcoded in `render.php`
  and never a global option. Give each attribute a sensible default; the editor changes
  it per instance.
- **ZERO attribute styling.** Tailwind utilities / design-system classes / `--mbn-*`
  vars only; custom CSS in a `<style>` tag in `render.php`. Never `style=""`.
- **Assets → WP media** (deduped); icons/vectors/patterns as SVG/exported images
  referenced from media, never raw Figma URLs.
- **Observe each node's stroke, fills, vectors, effects.** Reproduce fills (color/
  gradient/image), strokes (border width/color/gradient, corner accents), vectors
  (export every shape/line/icon as SVG → upload to media → reference) and effects
  (shadows, blur, opacity) exactly; upload any asset they need to media and set it.
- **Editable like a visual builder.** Every component/section block exposes ALL its
  content (text, labels, links, media, and repeatable items with add/remove/reorder)
  as editor controls — `InspectorControls` (Text/Textarea/RichText/MediaUpload) + the
  shared `ItemsRepeater` for arrays — with a live `ServerPreview`. No content that can
  only be edited in the block markup.
- **ALL shared controls — ENSURED.** Every editor control comes from `src/shared/` —
  never re-implemented inline in a block's `edit.js`: `MediaPicker` for every media
  field (with `sizeValue`/`onSizeChange` for rasters), `ItemsRepeater` for every
  repeatable list (multiple repeaters per block via its `attribute` prop),
  `TagControl`/`SizeControl` (`tag-control.js`) for semantic tags and text-size
  utilities, `LcpControl` for above-the-fold backgrounds, `AnimationControl` for
  reveals, and `ServerPreview` for the live canvas. A missing option is added to the
  shared control (backward-compatibly), never forked into one block.
- **Heading tags via TagControl — ENSURED.** Every heading a block renders is
  tag-selectable: a `*Tag` attribute (`titleTag`, `itemTitleTag`, `headingTag`…) +
  the shared `TagControl` in `edit.js`, rendered through
  `mbn_tag( $attributes['titleTag'] ?? '', 'h2' )` (in `inc/block-layout-helpers.php`)
  so only allowed elements (h1–h6/p/div/span) are emitted. Defaults keep the semantic
  outline — exactly one `h1` per page (hero), `h2` sections, `h3` card titles.
- **Verify** each component against its Figma component with the chrome-devtools MCP.

## Scanning Figma for components

- Enumerate `instance` nodes (each points at a main component); group by main
  component so each is built once. `get_design_context` + `get_screenshot` per unique
  component for ground truth (variants, font weights, colors, strokes/gradients).
- Classify into a canonical `mbn-ai-` type (see the `ai-blocks` registry). Add new canonical names for
  genuinely new kinds.

## Canonical component vocabulary

- **background-pattern** → `mbn-ai-pattern`: a fill image/texture used as a background
  (attrs: imageId, opacity, repeat, position).
- **vector-lining** → `mbn-ai-vector-line`: divider/accent line, `linear` (gradient) or
  `plain` (solid) (attrs: orientation, style, color/from-to, thickness).
- **border-stroke-pattern** → `mbn-ai-stroke`: element border, `linear` or `plain`
  (attrs: style, color/gradient, width, radius).
- **button** → `mbn-ai-button` (attrs: label, href, variant, size, iconId).
- badge/tagline → `mbn-ai-badge`; card → `mbn-ai-card`; input → `mbn-ai-input`;
  icon → `mbn-ai-icon`.

## Authoring a component block

Reusable components live under **`src/components/mbn-ai-<name>/`** (sections/composite
blocks stay at `src/mbn-ai-<name>/`). A component folder has: `block.json` (category
`mbn-blocks`, attributes = the variations) + `index.js` (`registerBlockType` with
`NullSave`, importing shared from `../../shared/…`) + `edit.js` (`InspectorControls`
for the attributes + `ServerPreview`) + `render.php` (Tailwind + `--mbn-*`; scoped
`<style>` only if needed). Then `npm run build` — `block-registry.php` scans `build/`
**recursively**, so `build/components/<name>/` auto-registers. Background images render
as an `<img>` behaving like cover/center (with a preload `<img>` poster for video
backgrounds).

## Registry → the `ai-blocks` skill

The live list of which `mbn-ai-` blocks/components exist lives in the **`ai-blocks`**
skill (`.claude/skills/ai-blocks/SKILL.md`) — the one mutable AI-state file. **Read it
before building** to reuse/extend an existing component; **update only its table** when
you create or extend one. This `components` skill (and all skill/command definitions)
stays **locked (read-only) while a command runs** — never edit it mid-build.

### Icons & SVGs — ENSURED + NECESSARY

- **Every icon/SVG is uploaded to the WP media library as a sanitized SVG** (the theme
  allows SVG upload and sanitizes it on upload via `mbn_sanitize_svg_upload`; via WP-CLI
  pass `--user=<admin>` so the `upload_files` capability check passes). **Never** ship an
  icon as a committed theme file or a hardcoded inline `<path>` in render.
- Render icons through **`mbn-ai-icon`**, which inlines the media SVG via the single
  shared helper **`mbn_inline_svg_attachment( $id, $size, $classes )`** (in
  `inc/block-layout-helpers.php`) so the SVG inherits `currentColor` and can recolour on
  hover. That helper is the **only** SVG-display path — buttons, social links and
  vector-lines all route through it; never add a second inliner or read the SVG file
  inline in a block's render.
- Author icon SVGs with `fill="currentColor"` / `stroke="currentColor"` so colour comes
  from a Tailwind text-colour utility on the wrapper.
- **If an asset is an SVG, render it INLINE as `<svg>` from the media library — NEVER an
  `<img>` (ENSURED).** This applies to ALL SVGs, not just icons: accent lines/vectors
  (`mbn-ai-vector-line` style `svg`), patterns (`mbn-ai-pattern` auto-detects an SVG via
  `get_post_mime_type`), logos, dividers — every one goes through
  `mbn_inline_svg_attachment( $id, $size, $classes )` (pass `$size = 0` for responsive,
  non-square artwork that should scale from its viewBox; a positive `$size` for square
  icons). Only fall back to `<img>` for raster assets (JPG/PNG/WebP). Rationale: inline
  `<svg>` keeps gradients/strokes crisp, inherits `currentColor`, and avoids an extra
  request; an `<img>` SVG can't recolour and is treated as opaque.
- **Never set `h-auto` on an inline `<svg>` (ENSURED).** Unlike `<img>`, an inline SVG
  does not derive height from `height:auto` reliably — size it with a SINGLE dimension
  (`w-full` for a horizontal/full-width element, `h-full` for a vertical one, or an
  explicit `h-[Npx]`) and let the `viewBox` supply the aspect ratio. Setting both a
  width and `h-auto` can collapse or mis-size the SVG.

> When `/build-components` (or any build) creates or extends a component, add/update
> its row in the **`ai-blocks` registry** (`.claude/skills/ai-blocks/SKILL.md`) so future
> runs reuse it instead of rebuilding — that registry is the only file a build run writes.
