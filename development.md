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

| Tool | Use |
|---|---|
| **frontend-design-skills** (plugin) | Distinctive, intentional visual design when building or reshaping UI. |
| **chrome-devtools MCP** | The ONLY browser QA path — section/page verification across desktop/tablet/mobile, console + network checks, screenshots. Run on request via `/testing <url>`. |
| **Figma MCP** | Read designs (metadata / design context / screenshots / variables) and export assets to upload into WP media. |
| **WordPress MCP** (or WP-CLI) | Read/write posts, media and options. If the host PHP can't reach the DB, run `wp` through the site's PHP container. |
| **vexp MCP** | Graph-ranked code context (`run_pipeline`) instead of grep/glob. |

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
  `type="lazyload"` and run after the page is interactive, in order, via Blob URLs.
- **Styles** — non-theme/plugin stylesheets load non-blocking (`media="print"` → `all`
  on load); the theme's own styles stay to avoid a flash of unstyled content.

`assets/js/mbn-lazyload.js` is the small loader. It is **skipped** in admin, editor,
customizer, AJAX/REST/feeds and **for logged-in users** (so the toolbar/editor are
untouched) — verify it logged out. Toggle with the `mbn_enable_optimizations` filter.

## Build & lint

```bash
npm run build         # JS blocks (wp-scripts) + Tailwind CSS
composer run lint     # PHPCS — must pass before committing
```

Build artifacts (`build/`, `assets/build/`) are generated; never commit them. Commit to
a feature branch and open a PR — no direct pushes.
