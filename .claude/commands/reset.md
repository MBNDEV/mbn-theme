---
description: Reset the theme for a NEW project — remove ALL AI-generated mbn-ai- blocks/components and their artifacts, clear the components registry, and rebuild. Destructive and REQUIRES explicit confirmation first. Never touches the core mbn- blocks.
argument-hint: [--all] [--media] [--content] [--menus] [--preset]
---

Arguments: `$ARGUMENTS`

Reset the theme to a clean baseline so a **new** Figma project can be built from
scratch. This removes **every** `mbn-ai-` component/block the AI build commands
generated and, optionally, the generated content/media/menus/preset.

## CRITICAL — REQUIRES confirmation (destructive, irreversible)

This **deletes files and (optionally) data** and cannot be undone. You MUST:

1. List exactly what will be removed — every `mbn-ai-*` block/component dir found
   (under `src/` and `src/components/`), their `build/` output, plus whichever optional
   targets the flags enable.
2. **Ask the user to confirm and WAIT for an explicit "yes".** Never start deleting
   before they confirm — even with `--all`. If unsure, stop and ask.
3. **Never** remove or edit the core `mbn-` blocks (`mbn-section`, `mbn-container`,
   `mbn-columns`, `mbn-column`, `mbn-logo`, `mbn-menu`) or `src/shared/`. Only the
   `mbn-ai-` namespace is in scope.

## Always (default, no flags)

- Delete every AI block/component source dir: `src/mbn-ai-*` **and**
  `src/components/mbn-ai-*` (remove the now-empty `src/components/` too).
- Delete their generated builds: `build/mbn-ai-*` and `build/components/`.
- Run `npm run build` so `build/` matches `src/` (the registry auto-registers only what
  remains).
- Reset the **`components` skill registry** table (`.claude/skills/components/SKILL.md`)
  back to empty (`_none yet_`), and drop any base-asset references it lists.
- `composer run lint` to confirm the theme is still clean.

## Optional targets (flags)

- `--content` → reset the built page(s): set the target post `post_content` to empty
  (or a single placeholder), reset the `header`/`footer` block templates to the theme
  defaults (so they no longer reference deleted `mbn-ai-` blocks), and delete the
  seeded **Testimonials** (`mbn_testimonial`) posts.
- `--menus` → delete the AI-created nav menus (`Header Menu`, `Footer Menu`,
  `Footer Services`, `Social Menu`, `Footer Legal`, …) and clear the templates'
  `_mbn_template_menus` meta.
- `--media` → delete the AI-uploaded attachments for the project (logos, hero/CTA
  images, patterns, vector lines, **service icons, `mbn-social-*` icons, FAQ
  watermark**, fonts uploaded for the build). De-dupe-safe: only remove attachments
  created for this project; confirm the list first.
- `--preset` → reset `mbn_settings` (colors, fonts, weights, sizes, container) to the
  theme defaults via `update_option( 'mbn_settings', mbn_settings_defaults() )`.
- `--all` → all of the above.

## After cleanup

- Confirm `src/` has only the core `mbn-` blocks + `shared/` (+ `template-reuse`).
- Report what was removed (blocks, builds, registry, and any optional targets), and
  that `npm run build` + `composer run lint` pass — the theme is ready for a new
  `/build-design` / `/build-components` run.

## Rules

- Destructive — confirm before removing anything; show the exact list first.
- Only the `mbn-ai-` namespace (blocks/components) and the explicitly-flagged
  content/media/menus/preset are in scope. Never edit core `mbn-` blocks. Don't
  `git push`; don't commit unless asked.
