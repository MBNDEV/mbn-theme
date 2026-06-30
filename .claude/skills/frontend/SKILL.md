---
name: frontend
description: Web design practices for mbn-theme — Tailwind utilities (admin + front end), mobile-first responsive design, preset CSS variables, accessibility, and shallow modern markup. Use when building or styling blocks, templates, or admin UI.
---

# Web Design Practices

- Use Tailwind CSS utilities for both the WordPress admin/editor and the front end.
- Responsive, mobile-first methodology.
- Use the preset Customizer CSS variables and utilities (`--mbn-color-scheme-*`,
  `--mbn-font-*`, `--mbn-size-*`, `--mbn-container-width`, `--mbn-radius`).
- Observe web accessibility and best practices (landmarks, labels, contrast, focus).
- When implementing images use `wp_get_attachment_image()`.
- Lessen child nodes where possible — keep the DOM shallow.
- Smooth animations/transitions and a modern look; avoid cumulative layout shift.
- Custom classes use the `mbn-` prefix and kebab-case; prefer utilities first.
- Use these blocks if necessary: `mbn-section`, `mbn-container`, `mbn-columns`

See `rules/web-design.md` for the full rule list.

## Block conventions (apply to every `mbn-ai-` block, new and existing)

- **Backgrounds are an `<img>`, never CSS `background-image`.** A full-bleed background
  renders through `mbn-ai-bg-media`: an `<img>` sized like cover/center. For video,
  render the `<video>` with an `<img>` **poster** as its still alternative, lazy-loaded
  via `assets/js/mbn-video.js`; when no poster is supplied, use a plain black image as
  the poster so nothing flashes. (A repeating *texture/pattern* is the one exception —
  it must tile, which an `<img>` can't, so `mbn-ai-pattern` sets it in a scoped
  `<style>`.)
- **Reusable media options.** Any block that takes an image must expose the same four
  alternatives together in one "Media" settings group — **image, video, poster,
  background** — built once and reused (`src/shared/media-controls.js`). They map to the
  `mbn-ai-bg-media` attributes so behaviour is identical everywhere.
- **Media pickers show the file.** In the editor, every image/media picker displays the
  selected image's URL and a link that opens that attachment in the Media Library for
  editing — not just a "Replace" button.
- **Header behaviour.** The header always offers a behaviour setting — **sticky on
  scroll**, **appear on scroll**, or **static** — and a transparent-background option
  that gains a solid background once scrolled. Sticky is the default. Implement with a
  small scroll listener in the header's `view.js`.
  - **`position: sticky` gotcha — stick the page-level wrapper, not the header block.**
    A sticky element only stays pinned while *its own parent* is on screen. The header
    block sits inside the short `<header class="mbn-site-header">` masthead, so making
    the block itself sticky fails — it unsticks the moment the masthead scrolls away.
    Make the page-spanning wrapper (`.mbn-site-header`, whose parent `#page` spans the
    whole document) the sticky element, driven by the block's `data-behavior` via a CSS
    `:has()` rule. "Appear" toggles a `mbn-header-hidden` (translateY(-100%)) class on
    that same wrapper from `view.js`; "transparent" toggles the background on the inner
    bar. Also remember an ancestor with `overflow: hidden/auto/scroll` or a `transform`
    silently breaks `position: sticky` — keep those off the header's ancestors.
- **Per-section animation.** Each section block offers an animation setting (none,
  fade, slide-up, slide-left, …) that reveals its content **in order**, one item after
  another, as it scrolls into view. The default is **none**. Drive it from one shared
  IntersectionObserver script (`src/shared/reveal.js`) and respect
  `prefers-reduced-motion`.
- **Editable like a visual builder, with RichText for text.** Every piece of content is
  editable in the editor. **Text content uses `RichText`** — edited inline on the
  canvas with formatting (bold, italic, links) — not a plain sidebar input; headings,
  paragraphs and each repeatable item's text are `RichText` fields. Media, links and
  ordering (add/remove/reorder via the shared `ItemsRepeater`) stay in
  `InspectorControls`. Save the RichText HTML to the attribute and print it with the
  matching escaping (`wp_kses_post`) in `render.php` so the editor and front end match.
- **Smooth, observed transitions.** Match the design's interactions — dropdowns with
  their chevron/icon, hovers, button states, sticky-header changes — with short, smooth
  transitions; never a hard jump. Avoid layout shift (reserve media space).
  - **Mobile menu + dropdowns must animate, not hard-toggle.** Never open/close the
    mobile nav panel or its submenus by toggling `hidden` (`display:none` can't
    transition). Collapse the panel with the grid-rows technique
    (`display:grid; grid-template-rows:0fr` → `1fr`, inner wrapper `overflow:hidden`)
    and submenus with a `max-height`/`opacity` transition, toggled via a `data-open`
    attribute from `view.js`; rotate the chevron on open. Keep these transitions scoped
    to the mobile breakpoint so the desktop hover dropdowns are unaffected.
- **Forms use Gravity Forms.** Render forms (newsletter, contact, etc.) through Gravity
  Forms (`gravity_form()` / the form block) while keeping the design's exact look via
  the theme's field styling; fall back to the styled static markup only when Gravity
  Forms is not active.

## Fonts (preset)

- **Responsive sizes.** Each type level has a desktop size plus a tablet and mobile
  size; leaving tablet/mobile empty auto-reduces from the desktop value. Headings shrink
  on smaller screens but never below the paragraph size, and the paragraph stays fixed.
- **Match the fallback to the webfont.** When a primary/secondary font is chosen,
  automatically set its fallback `@font-face` `size-adjust`, `ascent-override`,
  `descent-override` and `line-gap-override` so the fallback's metrics sit as close to
  the real webfont as possible (aim for ~100% overlap) — this is what kills the layout
  shift when the webfont finishes loading. The settings page shows a **Primary/Secondary
  font vs Fallback overlay preview**; tune the overrides until the two letterforms line
  up. Keep the paragraph readable and never let a heading render smaller than body text.

## Writing block descriptions

`block.json` `description` (and any UI help text) must read like a person wrote it for
another person: one or two plain sentences saying what the block is and what it's for.
Be specific. Do not use `---` separators, do not name HTML tags or CSS, and do not
narrate the build process or mention that it "composes" other blocks.
