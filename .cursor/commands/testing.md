---
description: Run the browser QA pass against a specific URL with the chrome-devtools MCP, then fix the issues found on that page/post.
argument-hint: <url> [figma_link]
---

Target URL: `$ARGUMENTS`

Run the browser QA pass against the URL above using the **chrome-devtools MCP** and
fix what it surfaces. Browser QA is **only** run here, on request — never
automatically. (See the `quality-assurance` skill and the
`chrome-devtools-mcp:chrome-devtools` skill for the tooling.)

## Steps

1. If `$ARGUMENTS` is empty, ask for a URL and stop. The **first token** is the page
   URL; an optional `https://www.figma.com/...` token (kept whole) is the **Figma
   link** to compare the page against.
2. Open the URL with the chrome-devtools MCP (`new_page` / `navigate_page`). Test the
   **whole page end to end — not section by section** — at **three** viewports:
   resize/emulate to **desktop 1920×1080**, **tablet 768×1024**, and **mobile (Moto G
   Power) 360×640**, navigating, scrolling, and triggering hovers/menus at each.
3. Capture evidence at each viewport:
   - `take_screenshot` (full page) for desktop and mobile.
   - `list_console_messages` — no console errors.
   - `list_network_requests` — no 4xx/5xx for the document or any asset (images,
     fonts, CSS, JS, icons). Every asset the design needs must actually load.
   - Confirm HTTP status < 400, the HTML has no PHP error text (`Fatal error`,
     `Warning:`, `Notice:`, `Deprecated:`, `Uncaught`, `call to undefined`), the
     theme header/footer render, and `<title>` is non-empty.
   - Collect internal links and confirm they resolve (no 404s).
   - Optionally run `lighthouse_audit` for page-speed/accessibility regressions.
4. **If a Figma link was given**, open it in a second tab and compare the page to the
   design viewport-by-viewport (spacing, colors, fonts/sizes, images, icons,
   gradients, textures, hovers). The page must match the design **100%** — fix any
   drift. Verify every Figma asset is present and loading on the page.
5. Fix the root cause of each issue:
   - **Page content** (broken links, missing/empty fields, bad markup, layout shift,
     missing assets, design drift): update that page/post's `post_content` blocks via
     the WordPress MCP or `wp post update`. De-duplicate media, use Tailwind utilities
     and the `--mbn-*` preset variables, follow `rules/web-design.md` (mobile-first,
     accessibility, shallow DOM).
   - **Theme code** (template / block / CSS): fix the PHP/JS, then `npm run build` if
     blocks or classes changed.
6. Re-open the URL with chrome-devtools and re-verify until everything is green and
   (when a Figma link is given) the page matches the design.
7. Report the findings and the fixes applied, with before/after screenshots.

## Rules

- Follow `rules/web-design.md` and `rules/security.md`.
- Use the chrome-devtools MCP only — Playwright is not used in this theme.
- Run `composer run lint` before any commit; never `git push` (open a PR). Do not
  commit unless asked.
