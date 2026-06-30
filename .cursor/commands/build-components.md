---
description: Scan a Figma file/page for REUSABLE components (buttons, patterns, vector-lines, strokes, badges, cards, inputs, icons…) and generate a generic, attribute-driven mbn-ai- component block for each one that does not already exist. Never reimplements an existing component. chrome-devtools-verified.
argument-hint: <figma_link> [--list]
---

Arguments: `$ARGUMENTS`

**Parse the arguments first** (a Figma URL contains `?`/`&`/`=`): take the
`https://www.figma.com/...` token (kept whole) as the **Figma link**, and note the
optional **`--list`** flag (scan + report only, build nothing).

Build the design's **reusable components** as generic `mbn-ai-` component blocks so
sections (and `/build-design`) can compose them instead of re-implementing the same
button/pattern/line over and over. Build on the `components`, `figma` and `frontend`
skills and `rules/web-design.md`.

> **Starting a new project?** Run `/cleanup` first to remove the previous project's
> `mbn-ai-` blocks/components so components are rebuilt from a clean baseline.

## Non-negotiables

- **Dedupe — never reimplement.** Before creating anything, check whether a matching
  component block already exists (`src/components/mbn-ai-*`, and any registered
  component in the `components` skill registry). If it exists, **reuse it** (extend it
  only if the design needs a new variant/attribute — modifying `mbn-ai-` blocks is
  allowed; never edit `mbn-` blocks). Report what was reused vs created.
- **Generic + attribute-driven.** Components must be design-agnostic: colors, sizes,
  labels, hrefs, image IDs, variants are **block attributes** (with the design's
  values as defaults at the call site), never hard-coded site specifics. CTA text and
  links are **block attributes** edited in that block's own settings (with a sensible
  default), never hardcoded in render and never a global option.
- **ZERO attribute styling.** Tailwind utilities / design-system classes / `--mbn-*`
  vars only; custom CSS in a `<style>` tag in `render.php`. Never `style=""`.
- **Assets → WP media.** Any image/pattern/icon/vector a component needs is exported
  to the WP media library (deduped) and referenced from there.
- **Verify with the chrome-devtools MCP** against the Figma component.

## Step 1 — Scan Figma for reusable components

- Authenticate (Figma MCP). From the link, identify the file and (if given) the
  components/section node.
- Enumerate the reusable components: use `get_metadata` to find `instance` nodes
  (each instance points at a main component) and the file's component set; group
  identical instances by their main component so each component is built **once**.
- Classify each into a component **type** (canonical `mbn-ai-` names):
  - `mbn-ai-button` (primary/secondary/outline variants, sizes, icon slot)
  - `mbn-ai-pattern` (background pattern/texture fill — `background-pattern`)
  - `mbn-ai-vector-line` (divider/accent line — `vector-lining`, linear or plain)
  - `mbn-ai-stroke` (border stroke — `border-stroke-pattern`, linear or plain)
  - `mbn-ai-badge` / tagline pill, `mbn-ai-card`, `mbn-ai-input`, `mbn-ai-icon`, …
  - add new canonical names for genuinely new component kinds.
- `get_design_context` + `get_screenshot` each unique component for ground truth
  (geometry, variants, font weights, colors, strokes/gradients).
- **`--list`:** output the inventory (component → proposed `mbn-ai-` block → exists?
  reuse/create) and stop.

## Step 2 — Build each MISSING component as a generic mbn-ai- block

For each component with no existing block:

- Author `src/components/mbn-ai-<name>/` (`block.json` + `index.js` + `edit.js` +
  `render.php`; dynamic = `NullSave` + `ServerPreview`; import shared from
  `../../shared/…`). Expose the component's variations as
  **attributes** (e.g. button: `label`, `href`, `variant`, `size`, `iconId`;
  vector-line: `orientation`, `style` linear|plain, `color`; pattern: `imageId`,
  `opacity`, `repeat`). Provide sensible defaults; do not bake in one site's values.
- Style with Tailwind utilities + `--mbn-*` vars; scoped `<style>` only when needed.
- Export any required assets to WP media (deduped).
- Category `mbn-blocks`; add an `editor` preview so it round-trips.
- `npm run build`, which registers it from `build/`.

## Step 3 — Register + verify

- Record the component in the **`components` skill registry** (name → purpose →
  attributes → Figma component) so future runs reuse it.
- `npm run build`; open a scratch post/preview and verify each new component against
  its Figma component with the chrome-devtools MCP (desktop + Moto G Power); confirm
  assets load (no 4xx) and there are no console errors.
- `composer run lint`. Report: components scanned, reused (existing), created (new
  blocks + attributes), assets exported (IDs).

## Rules

- Never reimplement an existing component — reuse, or extend the existing `mbn-ai-`
  block. Never edit `mbn-` blocks.
- Generic/attribute-driven; no site-specific values baked in. No attribute styling.
- All component assets in WP media (deduped). Follow `rules/web-design.md` +
  `rules/security.md`. `composer run lint` before any commit; never `git push`; don't
  commit unless asked.
