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

- **Reuse first, never reimplement.** Before building, check the **registry** below and
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
- **Verify** each component against its Figma component with the chrome-devtools MCP.

## Scanning Figma for components

- Enumerate `instance` nodes (each points at a main component); group by main
  component so each is built once. `get_design_context` + `get_screenshot` per unique
  component for ground truth (variants, font weights, colors, strokes/gradients).
- Classify into a canonical `mbn-ai-` type (see registry). Add new canonical names for
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

## Registry (keep updated as components are built)

_none yet_ — no `mbn-ai-` components have been built for the current project. Add a row
for each component as it is created.

| Component block | Purpose | Key attributes | Status |
|---|---|---|---|

Icons that recolour on hover are uploaded as SVG **and** rendered through `mbn-ai-icon`
(inlined for `currentColor`) — never an `<img>` (which can't inherit colour) and never a
hardcoded inline `<path>`.

> When `/build-components` (or any build) creates or extends a component, add/update
> its row here so future runs reuse it instead of rebuilding.
