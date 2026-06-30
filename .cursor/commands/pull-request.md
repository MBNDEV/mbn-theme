---
description: Create a pull request for the current branch if one does not already exist.
---

Check the current git branch, status, commits, and remote PR status
(`git status`, `git log`, `gh pr view`/`gh pr list`).

If a PR already exists for this branch:

- Show the PR URL
- Summarize its current status
- Do **not** create a duplicate

If no PR exists:

- Review the changed files (`git diff`, `git status`)
- **Run the project gates first and confirm they pass:** `composer run lint` and
  `npm run build`. Do not open the PR if either fails.
- Branch off `master` if you are still on it (never commit straight to `master`).
- Stage and commit the work locally with a clear, focused message.
- **No AI/LLM attribution anywhere** — do **not** add `Co-Authored-By: Claude`,
  `Claude-Session`, `Generated with Claude Code`, Cursor, or any similar trailer/footer
  to the commit message or PR body.
- **Never `git push` directly** (the `git-guard.sh` hook blocks it) — let
  `gh pr create` push the branch when it opens the PR.
- Generate a clear PR title and description and create it with `gh pr create --base
  master` (or the repo's integration branch).

Include in the PR body:

- **Summary** — what changed and why
- **Testing** — how it was verified (lint, build, chrome-devtools QA results if any)
- **Risks** — anything reviewers should watch
- **Rollback** — how to revert if needed

Keep it concise — a reviewer should understand it in under 30 seconds.
