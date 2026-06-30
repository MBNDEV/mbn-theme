# mbn-theme — Project Guide

WordPress custom theme that builds Gutenberg blocks. Figma designs are converted
to native blocks, with assets uploaded to the WordPress media library via the
WordPress MCP (or WP-CLI) — never duplicating existing media. Tailwind CSS styles
both the WordPress admin/editor and the front end.

## Stack & architecture

- **Blocks (JSX, built with @wordpress/scripts):** each block is authored in
  `src/<name>/` — `block.json` + `index.js` (registerBlockType) + `edit.js` (the
  JSX `Edit` component) + `render.php` (server render); shared editor pieces live in
  `src/shared/`. **Reusable `mbn-ai-` component blocks live in `src/components/<name>/`**
  (import shared from `../../shared/…`); composite/section blocks stay at `src/<name>/`
  and **compose** components via `do_blocks('<!-- wp:mbn-theme/mbn-ai-<name> … /-->')`
  rather than duplicating their markup. `npm run build:js` (wp-scripts, config
  `webpack.config.js`) compiles to `build/<name>/` (and `build/components/<name>/`)
  with auto-detected dependency `*.asset.php` files; `block-registry.php` scans
  `build/` **recursively** and registers every dir with a `block.json`, so block.json
  wires the editor/view scripts + render. Layout blocks: `mbn-section`/`mbn-container`/
  `mbn-columns`/`mbn-column`. Dynamic blocks: `mbn-logo`, `mbn-menu` (server-rendered;
  editor previews via `ServerSideRender` for parity). AI-authored blocks use the
  `mbn-ai-` prefix (e.g. `mbn-ai-accordion`, `mbn-ai-tabs`); components: `mbn-ai-button`,
  `mbn-ai-pattern`, `mbn-ai-vector-line`, `mbn-ai-bg-media`.
- **Styling:** Tailwind only. Source `resources/css/app.css`, built to
  `assets/build/tailwind.css` with `npm run build:css`. `npm run build` runs both the
  JS (`build:js`) and CSS (`build:css`) builds; `npm run dev` = `wp-scripts start`
  (JS watch). The `build/` directory is a generated artifact (gitignored; the bundle
  regenerates it). Use Tailwind utilities in admin and front end; design-system color
  utilities `bg-scheme-N`/`text-scheme-N`, `font-primary`/`font-secondary`,
  `text-mbn-h1..6` are safelisted. Custom classes use the `mbn-` prefix, kebab-case.
- **Presets:** Appearance → **MBN Theme** settings page (`inc/includes-admin-page.php`
  + `inc/includes-theme-settings.php`), stored in the `mbn_settings` option (not the
  Customizer). Color schemes (color-picker repeater), primary/secondary fonts from the
  **WordPress Fonts Library** (custom-installed families only), fallback fonts with
  `size-adjust`/`ascent`/`descent`/`line-gap` `@font-face` overrides, h1–h6/paragraph
  sizes + weights, with **responsive sizes** (desktop/tablet/mobile per level; tablet
  and mobile auto-reduce from the desktop value, never below the paragraph size, and the
  paragraph stays fixed) and live previews, plus container width and radius — all
  emitted as `--mbn-*` CSS variables (heading sizes via tablet/desktop media queries;
  colors as `rgba`). CTA button text and links live on **each block's own settings**
  (e.g. the header's Call-to-action panel), not in a global option. Color schemes also
  feed the editor color palette.
  The full design system (variables + selected webfont `@font-face`) is injected into
  the block-editor canvas via `block_editor_settings_all` so the editor matches output.
- **Header/footer:** rendered from the `header`/`footer` **block templates** via
  `mbn_render_part_template()` (do_blocks), falling back to the hardcoded markup in
  `header.php`/`footer.php`. Each template has a meta box for a logo + menus; the
  `mbn-logo`/`mbn-menu` blocks read that meta.
- **Block Templates:** `mbn_block_template` CPT (`inc/includes-block-templates.php`,
  seeds default header/footer). Remote Template Reuse (`inc/includes-template-reuse.php`)
  uses **JWT sign-in** in the editor — credentials are exchanged for a token held in
  the browser for the session only; nothing (no credentials, no token) is stored in
  the database.
- **Video:** front-end lazy loader `assets/js/mbn-video.js` (`data-src` → `src`,
  IntersectionObserver, respects `prefers-reduced-motion`).

## Workflow rules

- **Test before commit.** `composer run lint` must pass; `composer run lint:run`
  auto-fixes (security scan → phpcbf → phpcs). A PreToolUse hook enforces this.
- **No direct push.** Commit locally and open a pull request. The hook blocks
  `git push`.
- **Context search via vexp.** The vexp MCP (user-scoped) provides `run_pipeline`
  for graph-ranked context. The `.claude/hooks/vexp-guard.sh` PreToolUse hook
  blocks Grep/Glob while the vexp daemon + index are healthy; it falls back to
  direct search when the index isn't ready. (Full usage is in the global guide.)
- **Do NOT run browser QA automatically.** Browser QA uses the **chrome-devtools
  MCP** and runs only on explicit request via the `/testing <url>` command
  (`.claude/commands/testing.md`), which tests that URL (and an optional Figma link)
  across desktop (1920×1080) and Moto G Power (360×640) and fixes the page/post.
  Never launch the browser QA as part of normal edits. Playwright is not used.

## Rules & skills

- `rules/web-design.md` — Tailwind, responsive/mobile-first, accessibility, media.
- `rules/security.md` — sanitize/escape/nonce/capability expectations.
- `rules/git-workflow.md` — commit/PR policy.
- `commands/testing.md` — `/testing <url>`: on-request chrome-devtools QA + fix for a URL.
- `commands/build-design.md` — `/build-design <post_id> <figma_link>`: Figma → blocks,
  header/footer into their block templates, assets into the WP media library.
- `commands/build-components.md` — `/build-components <figma_link>`: scan Figma for
  reusable components → generic `mbn-ai-` component blocks (dedupe; never reimplement).
- `commands/reset.md` — `/reset`: reset for a new project — remove ALL `mbn-ai-`
  blocks/components (+ optional content/media/menus/preset); REQUIRES explicit
  confirmation; never touches `mbn-` blocks.
- `development.md` — required plugins (Gravity Forms, GF SMTP) + Claude MCP/plugins,
  the post import/export upsert endpoint, and token-reduction guidance.
- `skills/figma` — Figma → blocks + WordPress MCP media upload.
- `skills/components` — reusable `mbn-ai-` component blocks + registry.
- `skills/developer` — senior WordPress/Gutenberg engineering practices.
- `skills/frontend` — web design practices.
- `skills/quality-assurance` — QA checklist (run only via `/testing <url>`).

## AI assistants stay in sync (Claude + Cursor)

`.claude/` is the **single source of truth**. `.cursor/` (rules `*.mdc` + commands) and
`AGENTS.md` are **generated** from it by `scripts/sync-ai-config.sh`, so Claude Code and
Cursor share the same rules, commands and skills. **Edit only the `.claude/` sources**,
then run `scripts/sync-ai-config.sh` (generated files carry a DO-NOT-EDIT banner — never
hand-edit `.cursor/**` or `AGENTS.md`). The pre-commit hooks enforce it: the Claude
`git-guard.sh` and the shared `.githooks/pre-commit` (`git config core.hooksPath
.githooks`) run the sync `--check` and block/restage when out of date.

## Conventions

- Functions/variables `snake_case`; classes `PascalCase`; constants `UPPER_SNAKE_CASE`.
- PHP indent: 2 spaces (see `phpcs.xml`). Escape all output, sanitize all input.
- Editor render (`src/<name>/edit.js`) and front-end `render.php` must produce the
  same markup/classes so the editor matches the published page. Run `npm run build`
  after changing block source.


## Development

- Use the WordPress MCP when available; otherwise WP-CLI (`wp <cmd>`).
- If the active PHP CLI cannot reach the database, run `wp` through whatever PHP
  runtime the site actually uses (e.g. the app's PHP container) — the workflow is
  the same whether or not the site is containerized.