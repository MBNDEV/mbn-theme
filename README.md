# MBN Theme

> A no-build WordPress block theme — native Gutenberg blocks, Tailwind CSS for admin and front end, and a Customizer-driven design system.

![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759b)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-38bdf8)
![Build](https://img.shields.io/badge/blocks-no--build-success)
![License](https://img.shields.io/badge/License-GPL--2.0-blue)

**Contributors:** My Biz Niche
**Tags:** gutenberg, blocks, tailwind, customizer, no-build
**Requires at least:** WordPress 6.0
**Requires PHP:** 7.4
**Stable tag:** 1.1.0
**License:** GPL-2.0-or-later
**Text Domain:** `mbn-theme`

---

## Description

MBN Theme builds pages from a small set of native Gutenberg layout blocks and styles
everything with Tailwind utilities — in both the block editor and on the front end.
There is **no JavaScript build step**: blocks register straight from source and the
editor runs on plain `window.wp.*` globals. The only compiled asset is the Tailwind
stylesheet.

A Customizer panel exposes a reusable design system (color schemes, typography,
sizes, layout) as `--mbn-*` CSS variables that the blocks consume, so the look of a
site is configured, not hardcoded.

## Features

- **No-build blocks** — `mbn-section`, `mbn-container`, `mbn-columns`, `mbn-column`.
  Each block is `block.json` + `render.php`; the shared editor logic is one plain-JS
  file (`assets/js/mbn-blocks-editor.js`).
- **Tailwind everywhere** — utilities in the admin/editor and the front end. Custom
  classes use the `mbn-` prefix, kebab-case.
- **Design system in the Customizer** — color schemes (rgba), primary/secondary +
  fallback fonts, h1–h6/paragraph sizes, container width and border radius, all
  emitted as `--mbn-*` variables (and the editor color palette).
- **Custom HTML injection** — global (Customizer) and per-post (meta box) Header /
  After Body / Footer HTML, injected on `wp_head` / `wp_body_open` / `wp_footer`.
- **Block Templates** — a reusable `mbn_block_template` post type plus **Remote
  Template Reuse** to pull templates from other sites over the REST API.
- **Quality tooling** — PHPCS (WordPress standards) + a security scan, and an
  on-request browser QA pass via the chrome-devtools MCP.

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Node.js 18+ and npm (to build the Tailwind CSS and bundle the theme)
- Composer (for PHP linting only — no runtime dependencies)

## Installation

1. Copy the theme into `wp-content/themes/mbn-theme`.
2. Install tooling and build the stylesheet:
   ```bash
   composer install
   npm install
   npm run build        # compiles assets/build/tailwind.css
   ```
3. Activate **MBN Theme** in **Appearance → Themes**.
4. Configure the design system in **Appearance → Customize → MBN Theme**.

> For a production drop-in, run `npm run bundle` (see below) to get a clean
> `bundle/mbn-theme.zip` with development and tooling files stripped out.

## Customizer design system

**Appearance → Customize → MBN Theme** outputs these CSS variables on `:root`
(front end and editor), ready to use in blocks and custom CSS:

| Group       | Variables |
|-------------|-----------|
| Colors      | `--mbn-color-scheme-1` … `--mbn-color-scheme-6` (rgba) |
| Typography  | `--mbn-font-primary`, `--mbn-font-secondary`, `--mbn-font-fallback`, `--mbn-size-h1`…`--mbn-size-h6`, `--mbn-size-body` |
| Layout      | `--mbn-container-width` (overrides Tailwind `.container`), `--mbn-radius` (+ `.mbn-radius` utility) |

## Development

| Command | Description |
|---------|-------------|
| `npm run build` | Build the Tailwind stylesheet (production, minified) |
| `npm run dev` | Watch and rebuild Tailwind CSS |
| `npm run bundle` | Build a distributable `bundle/mbn-theme.zip` (dev files excluded) |
| `composer run lint` | PHPCS (WordPress Coding Standards) |
| `composer run lint:run` | Security scan → phpcbf (auto-fix) → phpcs |

**Blocks** have no build step: edit `blocks/<name>/render.php` and
`assets/js/mbn-blocks-editor.js` directly. Run `npm run build:css` only when you add
Tailwind classes not already present in the compiled stylesheet. The editor render
and the front-end `render.php` must produce identical markup/classes.

## Bundling for distribution

```bash
npm run bundle
```

Builds the Tailwind stylesheet, then stages and zips the theme to
`bundle/mbn-theme.zip`. Everything ships **except** development, tooling and
generated files: `.claude/`, `node_modules/`, `vendor/`, `.git/` + `.gitignore`,
`.env*`, `resources/`, `scripts/`, `plans/`, the `bundle/` output, and the
Composer/npm/Tailwind/PostCSS/PHPCS manifests and config at the root.

The local `vendor/` (dev-only — PHPCS) is never shipped. If `composer.json` ever
declares a **runtime** dependency (a `require` entry), the bundle runs
`composer install --no-dev` into the staged copy so a clean, production-only
`vendor/` is included — without the dev tooling and without touching your local
`vendor/`.

## Project structure

```
mbn-theme/
├── assets/build/        Compiled Tailwind CSS (generated)
├── assets/css|js/       Block editor styles + scripts
├── blocks/<name>/       block.json + render.php per block
├── inc/                 Theme setup, customizer, custom HTML, block templates, reuse
├── page-templates/      Classic page templates
├── template-parts/      Reusable template partials
└── functions.php        Loads the components above
```

## Quality assurance

Browser QA is **on-request only** — it never runs automatically. With Claude Code,
run `/testing <url>` to test a specific URL (optionally passing a Figma link to
compare against) across desktop (1920×1080) and Moto G Power (360×640) and fix issues
on that page. QA is driven by the **chrome-devtools MCP** — there is no standalone
test runner to invoke manually.

## License

GPL-2.0-or-later. See <https://www.gnu.org/licenses/gpl-2.0.html>.
