---
name: quality-assurance
description: Browser QA for mbn-theme using the chrome-devtools MCP across desktop (1920x1080) and Moto G Power (360x640) — opens the post URL alongside the Figma link (and prototype), compares section by section, checks interactions/sticky header/background video, enforces no attribute styling and all-assets-in-WP-media, and fixes drift automatically. Run ONLY on explicit request via the /testing <url> command — never automatically.
---

# Quality Assurance

> Run only when asked, via `/testing <url>` (`.claude/commands/testing.md`).
> Do not launch browser QA automatically as part of normal edits.
> Tooling: the **chrome-devtools MCP** (`chrome-devtools-mcp:chrome-devtools` skill).
> Playwright is not used in this theme.

Do the browser test with the **chrome-devtools MCP**. Open the post URL and, when a
Figma link is provided, open the design in a second tab so you can compare the two
side by side. Navigate, click, scroll, trigger hovers/menus, and resize/emulate the
viewport at:

- Desktop 1920×1080
- Mobile / Moto G Power 360×640 (emulator-based mobile uses the Moto G Power)
- If a device isn't applicable, fall back to the Lighthouse mobile dimensions

Run `lighthouse_audit` for page-speed and accessibility performance.

# Compare post URL ↔ Figma — SECTION BY SECTION (and fix automatically)

When a Figma link is given, treat the design as ground truth and the page must match
it **100%**. Verify **one section at a time** — open both the post URL and the Figma
URL and compare *that section* before judging the next. Also **run the Figma prototype
(Present flow)** and walk the page's interactions against it. Fix any drift in place:

- Layout, spacing (margins/paddings/new lines), alignment, **column ratios** (1/3 etc.),
  container widths.
- Colors, gradients, overlays, textures, **border styling**, border radius.
- Fonts, font sizes, **font weights**, line-height, letter-spacing, exact text.
- Images, **vectors/icons (SVG)**, background images, **background videos** (each video
  must have a preload `<img>` poster and respect reduced-motion).
- Interactions: hover/focus states, button behavior, scroll behavior, and **whether
  the header is sticky / changes on scroll** — replicate the prototype.
- Modules: sliders/carousels, animated vectors, repeating patterns.

**No attribute styling:** the markup must style via Tailwind utilities / design-system
classes / `--mbn-*` vars (custom CSS only in a `<style>` tag) — flag any `style="…"`
attribute as drift to fix.

**Ensure all assets are implemented:** every image, icon, font, background image and
background video the design uses must be in the **WP media library**, present on the
page, and actually load (verify via `list_network_requests` — no missing/4xx assets,
nothing referencing Figma at runtime). Reuse existing WP media; never duplicate.

Fix the root cause — page `post_content` / the section's `mbn-ai-` block (then
`npm run build`) — and re-verify that section with chrome-devtools until it matches the
design and is clean, then continue to the next section.

# Checking

- Each section matches its Figma counterpart (spacing, font weights, borders,
  vectors/icons, colors, text) — verified individually, not just the whole page.
- Interactions match the prototype: hovers, button states, scroll behavior, sticky
  header, sliders/modules.
- Background videos have a preload `<img>` poster and respect reduced-motion.
- No attribute styling (`style="…"`) in the markup — utilities / `<style>` only.
- Every asset is in WP media and loads; nothing references Figma at runtime.
- Missing links, bugs, design glitches causing cumulative layout shift.
- No console errors (`list_console_messages`).
- No 4xx/5xx for the document or any asset (`list_network_requests`).
- Avoid 404 page links.
- Confirm the editor render matches the published front-end output.
