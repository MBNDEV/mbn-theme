# Create Gutenberg Blocks Setup

Use this command when you need to scaffold a new native Gutenberg block in `blocks/`.

## What to create

- Create `blocks/{block-name}/`.
- Add `block.json`, `index.js`, `edit.js`, `render.php`, and `style.css`.
- Keep the block dynamic and register it with `save: () => null`.

## Must-have features

- Margin controls
- Padding controls
- Background image upload
- Background video upload
- Background, text, accent, and overlay color controls
- Custom CSS input scoped to the block wrapper

## Conventions

- Use `apiVersion: 3`.
- Use `category: mbn-blocks`.
- Use `editorScript: file:./index.js`.
- Use `style: file:./style.css`.
- Use `render: file:./render.php`.
- Include `supports.html = false`, `supports.anchor = true`, and `supports.align = ["wide", "full"]`.
- Apply values with CSS variables and Tailwind classes that read those variables.
- Observe tailwind implementations css class utilities, minimize custom css, custom scripts should implemented in render.php and it should wraps by section id.
- Observe mobile-first responsive, web accessibility and performance.
- Keep editor and front-end structure consistent.
- Use `get_block_wrapper_attributes()` in the render template.

## Safety and output rules

- Escape all render output.
- Use `wp_kses_post()` only for trusted rich text.
- Scope custom CSS to the block wrapper id named mbn-{blockname}-uniqueid.
- Do not add duplicate registration logic in `functions.php`.
- always use tailwind css utilities ie: container etc if style.css is not necessary do not add it.
- Minimize or if possible do not use custom classes and just use tailwind css utiltiies.
- use ob_start() do not use string concatenation of html snippets


## File Structure

mbn-{blockname}
 - block.json
 - edit.js
 - index.js
 - render.php
 - style.css


# Naming Conventions

- Functions and variables: `snake_case`
- Classes: `PascalCase`
- Constants: `UPPER_SNAKE_CASE`
- Files containing a class: `class-product-helper.php`

# Indentation

2 spaces. No tabs.

# Security

- Sanitize all input: `sanitize_text_field()`, `absint()`, `sanitize_email()`
- Escape all output: `esc_html()`, `esc_url()`, `esc_attr()`, `wp_kses()`
- Verify nonces: `check_ajax_referer()`, `wp_verify_nonce()`
- Check capabilities: `current_user_can()` before privileged actions
- Use `$wpdb->prepare()` for all queries — never raw SQL
- No hardcoded API keys, tokens, or credentials

# Complexity

- Cyclomatic complexity per function: warning at 10, error at 20
- Max nesting depth: warning at 3 levels, error at 5
- Use early return / guard clauses to reduce nesting

# Code Quality

- No commented-out code
- No TODO or FIXME comments — convert to tracked issues
- All user-facing strings use `__()` or `_e()` with text domain example: `mbn-theme` follow the theme repository name
- Enqueue scripts via `wp_enqueue_scripts` — no inline `<script>` tags


# Code Review

## Refactoring

- Keep refactors minimal and localized.
- Check security concerns sanitization
- Follow the phpcs.xml standard, spacing and indentions.

## Error Checks

- Fix error first and validate before running command below.
- run composer run lint:run

## Test Codes

- Review your updates if there are security constraints.
- Simplify updates logic and design do not complicate.
- Test QA code updates without breaking flows.
- Fix all ts errors.


# GIT Pull Requests

Your job is to create a pull request with a descriptive title, always use the GITHUB CLI. If you haven't already made a commit, do that first.
Checklist of steps to verify the changes work correctly.
Keep it concise — reviewers should understand the PR in under 30 seconds.

# GIT Release Notes

When tagging a release (`vX.X.X`), group changes under:

- **Features**
- **Bug Fixes**
- **Performance**
- **Breaking Changes** (if any)
- **Full Changelog**

Base the notes on commit messages since the last tag. Keep entries concise and professional. No attribution to individual tools or services - notes represent the team.
