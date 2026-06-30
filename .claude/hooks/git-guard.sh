#!/usr/bin/env bash
# PreToolUse(Bash) guard for the mbn-theme project.
#
#  - Blocks direct `git push` (open a pull request instead).
#  - Runs `composer run lint` (read-only phpcs) before `git commit`;
#    blocks the commit when violations remain.
#
# Non-git commands fall through with no decision, so normal permission
# behavior is preserved (the hook never auto-approves arbitrary commands).
set -uo pipefail

input="$(cat)"
cmd="$(printf '%s' "$input" | jq -r '.tool_input.command // ""' 2>/dev/null)"
proj="${CLAUDE_PROJECT_DIR:-$(pwd)}"

deny() {
  # $1 = human-readable reason (JSON-escaped here).
  local reason
  reason="$(printf '%s' "$1" | jq -Rs .)"
  printf '{"hookSpecificOutput":{"hookEventName":"PreToolUse","permissionDecision":"deny","permissionDecisionReason":%s}}' "$reason"
  exit 0
}

# Block direct push — review via pull request instead.
if printf '%s' "$cmd" | grep -qE '(^|[^[:alnum:]])git([[:space:]]+-[^[:space:]]+)*[[:space:]]+push'; then
  deny "Direct 'git push' is disabled for this project. Commit locally and open a pull request for review."
fi

# Lint before commit (read-only check; does not modify files).
if printf '%s' "$cmd" | grep -qE '(^|[^[:alnum:]])git([[:space:]]+-[^[:space:]]+)*[[:space:]]+commit'; then
  if [ -f "$proj/composer.json" ]; then
    if ! ( cd "$proj" && composer run lint >/tmp/mbn-precommit-lint.log 2>&1 ); then
      deny "Pre-commit check failed: 'composer run lint' reported violations. Run 'composer run lint:run' to auto-fix, then retry. Log: /tmp/mbn-precommit-lint.log"
    fi
  fi

  # Keep .cursor/ + AGENTS.md in sync with .claude/ (single source of truth).
  if [ -x "$proj/scripts/sync-ai-config.sh" ]; then
    if ! ( cd "$proj" && bash scripts/sync-ai-config.sh --check >/tmp/mbn-precommit-sync.log 2>&1 ); then
      ( cd "$proj" && bash scripts/sync-ai-config.sh >/dev/null 2>&1 ) || true
      deny "AI config was out of sync with .claude/. Regenerated .cursor/ and AGENTS.md — 'git add' them, then retry. (Edit only the .claude/ sources.)"
    fi
  fi
fi

# No decision -> normal permission flow continues.
exit 0
