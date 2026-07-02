# mbn-theme — Development setup

What this theme needs to build and run, and how to keep AI-assisted development fast
and cheap (fewer tokens).

## Required WordPress plugins

| Plugin | Why |
|---|---|
| **Gravity Forms** | All front-end forms render through Gravity Forms (newsletter, contact, etc.) while keeping the theme's design. Blocks fall back to a styled static form only when it is not active. |
| **Gravity Forms SMTP** (Gravity SMTP) | Reliable form-email delivery (notifications/confirmations) so submissions actually arrive. |

Install and activate both before building forms. Assign a form to a block via its
settings (e.g. the footer's *Newsletter Gravity Form ID*).

## Required Claude tooling (plugins / MCP)

| Tool | Install | Use |
|---|---|---|
| **Figma MCP** | [Remote server installation](https://developers.figma.com/docs/figma-mcp-server/remote-server-installation/) | Read designs (metadata / design context / screenshots / variables) and export assets to upload into WP media. |
| **chrome-devtools MCP** | [ChromeDevTools/chrome-devtools-mcp](https://github.com/ChromeDevTools/chrome-devtools-mcp) | The ONLY browser QA path — section/page verification across desktop/tablet/mobile, console + network checks, screenshots. Run on request via `/testing <url>`. |
| **frontend-design** (plugin) | `/plugin install frontend-design@claude-plugins-official` | Distinctive, intentional visual design when building or reshaping UI. |
| **Claude in Chrome** (extension) | [Chrome Web Store](https://chromewebstore.google.com/detail/claude/fcoeoabgfenejglbffodgkkbkcdhcgfn) | Lets Claude drive your own Chrome session (tabs, forms, screenshots) when needed. |
| **WordPress MCP** (or WP-CLI) | — | Read/write posts, media and options. If the host PHP can't reach the DB, run `wp` through the site's PHP container. |
| **vexp MCP** | — | Graph-ranked code context (`run_pipeline`) instead of grep/glob. |

## Project workflow (start → ship)

1. **Install the WordPress plugins** — Gravity Forms + Gravity SMTP (table above).
2. **Install the Claude MCPs / plugins** — Figma MCP, chrome-devtools MCP, the
   `frontend-design` plugin and the Claude in Chrome extension (install links above).
3. **Authenticate Figma** — run `/mcp` in Claude Code and complete the Figma sign-in
   before any design work; `/build-design` stops if Figma isn't authenticated.
4. **Build with the Claude commands:**

   ```
   /build-design <post_id> <figma_link>              # full build: header + footer, then the body
   /build-design <post_id> <figma_link> --header     # reimplement only the header template
   /build-design <post_id> <figma_link> --footer     # reimplement only the footer template
   /build-design <post_id> <figma_link> --mobile     # responsiveness pass from the mobile frames
   /testing <url>                                    # AI browser QA of that URL (chrome-devtools)
   ```

   `--header` / `--footer` can be combined; QA never runs automatically — only via
   `/testing`.
5. **Prompt with plans.** Keep a `plans/` folder in the repository root and write each
   request there as a numbered plan (e.g. `plans/follow-up.md`), then prompt the LLM
   with the file — e.g. “implement `plans/follow-up.md`”. Plans stay out of the bundle
   and make multi-step work reviewable.
6. **Ship:** after the project is built and verified, run `npm run bundle` and upload
   `bundle/mbn-theme.zip` as the theme on the target site (Appearance → Themes →
   Add New → Upload).

## Speed-up & token reduction

Treat tokens as the scarce resource. In order of impact:

1. **Move post content + media through the content endpoint, never paste it.** Use the
   theme's import/export (below) to upsert a whole page — block markup *and* its
   media — in one call instead of streaming large JSON/base64 through the chat.
2. **Inspect with `get_skeleton`, not `Read`.** Read full files only to edit an exact
   line. Use `run_pipeline` for context rather than reading across files.
3. **Edit block source, then one `npm run build`.** Don't rebuild after every tiny edit.
4. **Verify with one whole-post chrome-devtools pass at the end**, not section by
   section, and only when asked.
5. **Reuse media by filename** (the importer does this automatically) so assets are
   never re-uploaded or re-encoded.

## Content import / export (upsert by post_id)

`inc/includes-content-io.php` exposes a post **upsert** keyed by `post_id` (update when
it exists, otherwise insert) that carries its media. Media are matched by **filename** —
an existing attachment is reused; otherwise the base64 is uploaded — and the post's
media ids / URLs are rewritten to the local ids.

Payload shape:

```json
{
  "post_id": 39,
  "post_type": "page",
  "post_title": "…",
  "post_status": "publish",
  "post_content": "<!-- wp:mbn-theme/… -->",
  "thumbnail": "hero.jpg",
  "media_map": { "78": "hero.jpg" },
  "medias": [ { "filename": "hero.jpg", "base64": "…" } ]
}
```

**WP-CLI** (preferred locally — keeps base64 out of the chat):

```bash
wp mbn-content export 39 --file=/path/page-39.json
wp mbn-content import --file=/path/page-39.json      # also accepts an array of payloads, or stdin
```

**REST** (move content between sites / automate):

```
GET  /wp-json/mbn/v1/posts/<id>     # export
POST /wp-json/mbn/v1/posts          # import (upsert), JSON body
```

**Admin UI:** the post list tables show an **Export** row action per post (downloads its
JSON) and an **Import** button above the list (upload a JSON export to upsert). Both are
capability- + nonce-checked.

**Authorization:** the `edit_posts` capability (works with an application password or a
logged-in editor), **or** a shared token — define `MBN_IO_TOKEN` in `wp-config.php` and
send it as the `X-MBN-Token` header. Never hardcode the token in source or commit it.

## Front-end performance (`optimizations.php`)

On anonymous front-end requests the page is buffered and rewritten to load late:

- **Fonts** — the Fonts Library inline CSS (`wp-fonts-local`) is turned into
  `type="text/lazystyle"` and re-applied on `window.load`, so the metric-matched
  fallback shows first and the webfont swaps in late (minimal layout shift).
- **Scripts** — external scripts get `defer`; executable inline scripts (no type or
  `text/javascript`, excluding WordPress `-js-before/after/extra` data) become
  `type="lazyload"` and re-run after the page is interactive, in order, as **fresh
  inline `<script>` elements** (synchronous, no Blob URLs — CSP-safe under
  `script-src 'self'`, no async race).
- **Styles** — non-theme/plugin stylesheets load non-blocking (`media="print"` → `all`
  on load); the theme's own styles stay to avoid a flash of unstyled content.
- **Security headers** — sent site-wide on the front end (X-Frame-Options SAMEORIGIN,
  X-Content-Type-Options nosniff, X-XSS-Protection, Referrer-Policy, a minimal CSP
  `upgrade-insecure-requests`, Permissions-Policy, and HSTS over HTTPS). Filter:
  `mbn_security_headers`.
- **Caching** — `Cache-Control: public, max-age=600` for anonymous GET/HEAD requests;
  `no-store, private` for logged-in users (and POST/preview/404/search/feed). TTL via
  the `mbn_cache_max_age` filter (0 disables); honours `DONOTCACHEPAGE`.

`assets/js/mbn-lazyload.js` is the small loader. It is **skipped** in admin, editor,
customizer, AJAX/REST/feeds and **for logged-in users** (so the toolbar/editor are
untouched) — verify it logged out. Toggle with the `mbn_enable_optimizations` filter.

## Build, bundle & lint

```bash
npm run build         # JS blocks (wp-scripts) + Tailwind CSS
npm run bundle        # distributable theme zip → bundle/mbn-theme.zip
composer run lint     # PHPCS — must pass before committing
```

`npm run bundle` (`scripts/bundle.mjs`) runs a fresh `npm run build`, stages every
runtime file into `bundle/mbn-theme/` and zips it. The AI/dev harness never ships —
`.claude`, `.cursor`, `AGENTS.md`, `.githooks`, `.vexp`, `src/`, `resources/`,
`scripts/`, `plans/`, node/composer manifests and `vendor/` are excluded (a
production-only `vendor/` is rebuilt inside the stage when the theme has runtime
Composer deps), and the build aborts if a required runtime file is missing. Only
regular files ship — sockets/FIFOs/symlinks (e.g. the vexp daemon socket) are skipped.

Build artifacts (`build/`, `assets/build/`, `bundle/`) are generated; never commit
them. Commit to a feature branch and open a PR — no direct pushes.

## Versioning the `mbn-ai-` blocks

On a **specific project install** (like this one) the generated `mbn-ai-*` blocks and
components under `src/` are part of the project and **are committed** — the
`.gitignore` no longer excludes them. Only the **reusable theme baseline** repo keeps
them ignored (`src/mbn-ai-*/`, `src/components/mbn-ai-*/`), because there they are
per-project output of `/build-design` / `/build-components` and get wiped by
`scripts/reset.sh`.
