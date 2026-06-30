#!/usr/bin/env bash
#
# Sync the AI assistant config so Claude Code (.claude) and Cursor (.cursor +
# AGENTS.md) always share the SAME rules, commands and skills.
#
# Single source of truth: .claude/
#   - .claude/rules/*.md      -> .cursor/rules/*.mdc   (adds Cursor MDC frontmatter)
#   - .claude/commands/*.md   -> .cursor/commands/*.md (verbatim)
#   - .claude/skills/*/SKILL.md is indexed into .cursor/rules/skills.mdc
#   - .claude/CLAUDE.md       -> .cursor/rules/project.mdc + AGENTS.md header
#
# Edit ONLY the .claude/ sources, then run this script (the commit hook runs it
# automatically). Generated files carry a "DO NOT EDIT" banner.
#
# Usage: scripts/sync-ai-config.sh [--check]
#   --check  exit non-zero if the generated files are out of date (CI / hooks).

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

CHECK=0
[ "${1:-}" = "--check" ] && CHECK=1

BANNER="<!-- AUTO-GENERATED from .claude/ by scripts/sync-ai-config.sh — DO NOT EDIT. Edit the .claude/ source and re-run the sync. -->"

# Stage generated output in a temp tree so --check can diff before replacing.
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
mkdir -p "$TMP/.cursor/rules" "$TMP/.cursor/commands"

# 1. Rules: .claude/rules/*.md -> .cursor/rules/*.mdc (alwaysApply).
for f in .claude/rules/*.md; do
  [ -e "$f" ] || continue
  base="$(basename "$f" .md)"
  desc="$(grep -m1 '^# ' "$f" | sed 's/^# *//')"
  [ -n "$desc" ] || desc="mbn-theme $base rules"
  {
    printf -- '---\n'
    printf 'description: %s\n' "$desc"
    printf 'globs:\n'
    printf 'alwaysApply: true\n'
    printf -- '---\n'
    printf '%s\n\n' "$BANNER"
    cat "$f"
  } > "$TMP/.cursor/rules/${base}.mdc"
done

# 2. Project guide: .claude/CLAUDE.md -> .cursor/rules/project.mdc.
if [ -f .claude/CLAUDE.md ]; then
  {
    printf -- '---\n'
    printf 'description: mbn-theme project guide (stack, architecture, conventions)\n'
    printf 'globs:\n'
    printf 'alwaysApply: true\n'
    printf -- '---\n'
    printf '%s\n\n' "$BANNER"
    cat .claude/CLAUDE.md
  } > "$TMP/.cursor/rules/project.mdc"
fi

# 3. Commands: .claude/commands/*.md -> .cursor/commands/*.md (verbatim copy).
for f in .claude/commands/*.md; do
  [ -e "$f" ] || continue
  cp "$f" "$TMP/.cursor/commands/$(basename "$f")"
done

# 4. Skills index: .claude/skills/*/SKILL.md -> .cursor/rules/skills.mdc.
{
  printf -- '---\n'
  printf 'description: mbn-theme skills index (apply the matching skill for the task)\n'
  printf 'globs:\n'
  printf 'alwaysApply: true\n'
  printf -- '---\n'
  printf '%s\n\n' "$BANNER"
  printf '# Skills\n\n'
  printf 'Full skill instructions live in `.claude/skills/<name>/SKILL.md` (shared\n'
  printf 'source of truth). Apply the one that matches the task:\n\n'
  for d in .claude/skills/*/; do
    [ -f "${d}SKILL.md" ] || continue
    name="$(basename "$d")"
    sdesc="$(sed -n 's/^description: *//p' "${d}SKILL.md" | head -1)"
    [ -n "$sdesc" ] || sdesc="see ${d}SKILL.md"
    printf -- '- **%s** — %s\n' "$name" "$sdesc"
  done
} > "$TMP/.cursor/rules/skills.mdc"

# 5. AGENTS.md — the shared entry point read by Cursor and other agents.
{
  printf '%s\n\n' "$BANNER"
  printf '# AGENTS.md — mbn-theme\n\n'
  printf 'This repo is developed with **both** Claude Code and Cursor. They share ONE\n'
  printf 'source of truth: the `.claude/` directory. `.cursor/` and this file are\n'
  printf 'generated from it by `scripts/sync-ai-config.sh` (run by the commit hook), so\n'
  printf 'the two assistants always have the same skills, rules, commands and workflow.\n\n'
  printf '**Edit `.claude/` only**, then run `scripts/sync-ai-config.sh`. Do not hand-edit\n'
  printf '`.cursor/**` or this file.\n\n'
  printf '## Rules (always apply)\n\n'
  for f in .claude/rules/*.md; do
    [ -e "$f" ] || continue
    printf -- '- `.claude/rules/%s`\n' "$(basename "$f")"
  done
  printf -- '- `.claude/CLAUDE.md` — project guide (stack, architecture, conventions)\n\n'
  printf '## Commands\n\n'
  for f in .claude/commands/*.md; do
    [ -e "$f" ] || continue
    printf -- '- `/%s`\n' "$(basename "$f" .md)"
  done
  printf '\n## Skills\n\n'
  for d in .claude/skills/*/; do
    [ -f "${d}SKILL.md" ] || continue
    printf -- '- `%s` (`%sSKILL.md`)\n' "$(basename "$d")" "$d"
  done
  printf '\n## Workflow (enforced for both tools)\n\n'
  printf -- '- `composer run lint` must pass before committing (Claude: `.claude/hooks/git-guard.sh`).\n'
  printf -- '- Never `git push` directly — commit to a branch and open a PR.\n'
  printf -- '- Context search via vexp `run_pipeline` (not grep/glob).\n'
  printf -- '- Browser QA only on request via `/testing <url>` (chrome-devtools MCP).\n'
} > "$TMP/AGENTS.md"

# Apply or check.
if [ "$CHECK" = "1" ]; then
  rc=0
  for rel in $(cd "$TMP" && find . -type f); do
    if ! diff -q "$TMP/$rel" "${rel#./}" >/dev/null 2>&1; then
      echo "out of date: ${rel#./}"
      rc=1
    fi
  done
  [ "$rc" = "0" ] && echo "AI config in sync."
  exit "$rc"
fi

rm -rf .cursor/rules .cursor/commands
mkdir -p .cursor/rules .cursor/commands
cp -R "$TMP/.cursor/." .cursor/
cp "$TMP/AGENTS.md" AGENTS.md
echo "Synced .cursor/ and AGENTS.md from .claude/."
