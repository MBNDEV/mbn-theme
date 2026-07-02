<!-- AUTO-GENERATED from .claude/ by scripts/sync-ai-config.sh — DO NOT EDIT. Edit the .claude/ source and re-run the sync. -->

# AGENTS.md — mbn-theme

This repo is developed with **both** Claude Code and Cursor. They share ONE
source of truth: the `.claude/` directory. `.cursor/` and this file are
generated from it by `scripts/sync-ai-config.sh` (run by the commit hook), so
the two assistants always have the same skills, rules, commands and workflow.

**Edit `.claude/` only**, then run `scripts/sync-ai-config.sh`. Do not hand-edit
`.cursor/**` or this file.

## Rules (always apply)

- `.claude/rules/figma-design-system.md`
- `.claude/rules/git-workflow.md`
- `.claude/rules/security.md`
- `.claude/rules/web-design.md`
- `.claude/CLAUDE.md` — project guide (stack, architecture, conventions)

## Commands

- `/build-components`
- `/build-design`
- `/bundler`
- `/create-release`
- `/pull-request`
- `/testing`

## Skills

- `ai-blocks` (`.claude/skills/ai-blocks/SKILL.md`)
- `components` (`.claude/skills/components/SKILL.md`)
- `developer` (`.claude/skills/developer/SKILL.md`)
- `figma` (`.claude/skills/figma/SKILL.md`)
- `frontend` (`.claude/skills/frontend/SKILL.md`)
- `quality-assurance` (`.claude/skills/quality-assurance/SKILL.md`)

## Workflow (enforced for both tools)

- `composer run lint` must pass before committing (Claude: `.claude/hooks/git-guard.sh`).
- Never `git push` directly — commit to a branch and open a PR.
- Context search via vexp `run_pipeline` (not grep/glob).
- Browser QA only on request via `/testing <url>` (chrome-devtools MCP).
