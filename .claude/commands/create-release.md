---
description: Create a release if one does not already exist.
---

Check existing tags and releases (`git tag`, `gh release list`).

If the requested version/release already exists:

- Show the existing release/tag
- Do **not** create a duplicate

If it does not exist:

- Inspect commits since the previous tag (`git log <prev>..HEAD`)
- **Confirm the gates pass first:** `composer run lint` and `npm run build`.
- Generate release notes from the commits
- Suggest a semantic version if one was not provided (read the current theme version
  from `style.css`'s `Version:` header and bump it; keep them in sync)
- Create the git tag if needed
- Create the GitHub release with `gh release create <tag>` (gh handles the push; never
  `git push` directly — the `git-guard.sh` hook blocks it)
- **No AI/LLM attribution** in the tag, release notes, or any commit (no
  `Co-Authored-By: Claude`, `Generated with Claude Code`, Cursor, etc.)

Include in the notes:

- **Highlights**
- **Bug fixes**
- **Breaking changes**
- **Migration notes**
- **Deployment checklist** (`npm run build` artifacts, `composer run lint`, any
  `wp` data steps such as `wp media regenerate`)
