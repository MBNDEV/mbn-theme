---
description: Build a distributable theme zip (excludes the AI/dev harness, keeps runtime files).
---

Build the shippable theme package with `npm run bundle` (`scripts/bundle.mjs`).

What it does:

- Runs `npm run build` (JS blocks + Tailwind CSS) first.
- Stages every theme file into `bundle/mbn-theme/` **except** the AI/dev harness and
  tooling, then zips to `bundle/mbn-theme.zip`.

**Excluded (never ship):** `.claude`, `.cursor`, `AGENTS.md`, `.githooks`, `.git`,
`.gitignore`, `.vscode`, `node_modules`, `vendor`, `bundle`, `src`, `resources`,
`scripts`, `plans`, env files, source maps, and the build/lint config + manifests
(`tailwind.config.js`, `webpack.config.js`, `postcss.config.js`, `phpcs.xml`,
`composer.*`, `package*.json`, `yarn.lock`).

**Required to keep (the bundle aborts if any are missing — `REQUIRED_KEEP` in
`scripts/bundle.mjs`):** `style.css`, `functions.php`, `index.php`, `theme.json`,
`block-registry.php`, `tailwind-loader.php`, `optimizations.php`, `header.php`,
`footer.php`, `inc/`, `build/` (compiled blocks), `assets/build/tailwind.css`. Also
kept: templates/parts, `libs/`, `README.md`, `screenshot.png`, and a `--no-dev`
`vendor/` only when the theme has runtime Composer deps.

Steps:

1. `npm run bundle`.
2. Confirm `bundle/mbn-theme.zip` was produced and the run did not abort on a missing
   required file.
3. Report the zip path/size and that `.claude`/`.cursor`/`AGENTS.md` are **not** in it.

When the bundler's keep/exclude rules change, update this command **and**
`.cursor/commands/bundler.md` stays in sync automatically via
`scripts/sync-ai-config.sh` (the commit hook enforces it).
