# Create Gutenberg Blocks

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
- Use div as we can use another blocks for sections and containers.

## Conventions

- Use `apiVersion: 3`.
- Use `category: mbn-blocks`.
- Use `editorScript: file:./index.js`.
- Use `style: file:./style.css`.
- Use `render: file:./render.php`.
- Blocks title should have MBN prefix ex: MBN Section
- Include `supports.html = false`, `supports.anchor = true`, and `supports.align = ["wide", "full"]`.
- Apply values with CSS variables and Tailwind classes that read those variables.
- Observe tailwind implementations css class utilities, minimize custom css, custom scripts should implemented in render.php and it should wraps by section id.
- Observe mobile-first responsive, web accessibility and performance.
- Keep editor and front-end structure consistent.
- Use `get_block_wrapper_attributes()` in the render template (or via `mbn_theme_render_layout_shell()`).
-  blocks design from edit js and output should 100% the same looking and building.
- just directly implement class names in render.php and edit.js do not complicate things.

## render.php rules

- Implement block HTML directly in `render.php` with `ob_start()` and PHP template markup.
- Do not build HTML with string concatenation (no `$html .= '<div...'` patterns).
- The only shared render helper allowed is `mbn_theme_render_layout_shell()` for the layout wrapper (spacing, background, overlay, scoped custom CSS).
- Build block-specific markup (galleries, grids, text, media) inline in that block's `render.php`.
- Child-only blocks (for example `mbn-column`) may output their own markup directly without the layout shell.
- Class-string helpers such as `mbn_theme_get_centered_content_classes()` are allowed as arguments to the layout shell.

## Safety and output rules

- Escape all render output.
- Use `wp_kses_post()` only for trusted rich text.
- Scope custom CSS to the block wrapper id named mbn-{blockname}-uniqueid.
- Do not add duplicate registration logic in `functions.php`.
- Always use Tailwind CSS utilities; do not add `style.css` unless utilities are not sufficient.
- Minimize custom classes; prefer Tailwind utility classes.

## File Structure

mbn-{blockname}
 - block.json
 - edit.js
 - index.js
 - render.php
 - style.css (optional)
