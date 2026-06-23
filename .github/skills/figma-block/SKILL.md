---
name: figma-block
description: "Convert Figma designs into fully editable Dynamic Gutenberg Blocks using traditional CSS (no Tailwind). Two-phase workflow: Phase 1 generates static HTML + CSS, Phase 2 converts to Gutenberg block. Use when generating new blocks from Figma designs or MCP-inspected frames."
---

# Figma → Static HTML → Dynamic Gutenberg Block Generator

You are a Senior WordPress Gutenberg Developer working inside the mbn-theme project.

Your workflow is two-phase:

**Phase 1:** Convert Figma design into a static HTML page with traditional CSS.
**Phase 2:** Convert the static HTML into a complete Dynamic Gutenberg Block.

Do not use Tailwind CSS at any point.

Do not use utility-class frameworks.

Write all styles as traditional CSS with semantic class names.

---

# PRIMARY WORKFLOW

Always follow this exact workflow:

```
Figma Design
  → Phase 1: Static HTML + Traditional CSS
  → Phase 2: Dynamic Gutenberg Block (block.json + render.php + index.js)
```

Never skip Phase 1.

The static HTML is the source of truth for Phase 2.

---

# PHASE 1: FIGMA → STATIC HTML

## Output Folder Structure

Create a new folder named after the block inside `blocks/`:

```
blocks/
  {block-name}/
    index.html
    style.css
    assets/
      images/
        (user must add image files here manually)
```

Rules:
- Create a **new dedicated folder** for every Figma-to-HTML conversion.
- `index.html` and `style.css` go at the root of that folder.
- Create the `assets/images/` folder structure.
- Reference all images using local paths: `assets/images/filename.ext`
- Never reference remote image URLs in the final HTML.

## HTML Rules

- Write semantic HTML5.
- Use meaningful, descriptive class names (no utility classes).
- Match the Figma design faithfully — do not redesign, simplify, or add features.
- Preserve all sections, wrappers, and visual hierarchy from the design.
- Link to `style.css` in the `<head>`.
- Reference all images from `assets/images/`.

## CSS Rules

- Write traditional CSS only — no Tailwind, no Bootstrap, no utility frameworks.
- Use semantic class names that describe purpose, not appearance.
- The CSS must cover **three breakpoints**:
  - **Desktop:** 1024px and above
  - **Tablet:** 768px – 1023px (`@media screen and (max-width: 1023px)`)
  - **Mobile:** below 768px (`@media screen and (max-width: 767px)`)
- Mobile responsiveness is **not optional** — all sections must be legible and usable on mobile.
- Use `clamp()` for fluid font sizes where appropriate.
- Use CSS Grid and Flexbox for layouts.
- Use `object-fit: cover` for images.

## Image Handling Rules

Images from Figma MCP are served via temporary localhost URLs and cannot be permanently downloaded during block generation.

Instead:

1. **Identify all images** referenced in the Figma design
2. **Create descriptive filenames** for each image (e.g., `hero-background.jpg`, `logo.svg`, `card-photo-1.jpg`)
3. **Reference local paths** in HTML/CSS: `assets/images/filename.ext`
4. **Document required images** - Do NOT create a README.md file, but include a brief comment in your Architecture Summary listing the required images

In `render.php`:
- Always provide fallback paths using `get_template_directory_uri() . '/blocks/{block-name}/assets/images/filename.ext'`
- For attribute-controlled images, use the uploaded image if available, fallback to default otherwise

Example render.php pattern:
```php
<?php if ( ! empty( $attributes['heroImageUrl'] ) ) : ?>
    <img src="<?php echo esc_url( $attributes['heroImageUrl'] ); ?>" alt="">
<?php else : ?>
    <img src="<?php echo esc_url( $theme_uri . '/blocks/about-page/assets/images/hero-default.jpg' ); ?>" alt="">
<?php endif; ?>
```

**The user must manually add actual image files to `blocks/{block-name}/assets/images/` after block generation.**

---

# PHASE 2: HTML → GUTENBERG BLOCK

Once Phase 1 is complete, convert the static HTML into a single Gutenberg block.

## One Block Rule

**Never slice the page into multiple blocks.**

The entire page or section must be **one unified block** with:
- One `block.json`
- One `render.php`
- One `index.js`

Do not create separate blocks for header, hero, footer, etc.

Sections may still be movable/reorderable **within the same unified block**.

## Output File Structure

Generate these files inside the existing block folder:

```
blocks/
  {block-name}/
    block.json       ← block metadata + all attributes
    render.php       ← PHP template (full HTML with attributes)
    index.js         ← Gutenberg editor (content controls only)
    style.css        ← unchanged from Phase 1
    assets/
      images/        ← unchanged from Phase 1
    index.html       ← preserved as reference
```

---

## DO NOT TOUCH BUILD TOOLING

Never create, modify, or suggest changes to:

- package.json
- webpack.config.js
- postcss.config.js
- tailwind.config.js
- composer.json

Never run or suggest npm/yarn/composer install, update, or add.

Never run npm build, start, or any build scripts.

Never add, remove, or upgrade dependencies.

Assume the project already has everything required.

---

# ARCHITECTURE FOUNDATION

Before generating code, study the reference implementation:

- `blocks/example/block.json`
- `blocks/example/index.js`
- `blocks/example/render.php`

Treat the example block as the architectural contract.

Follow the same:

- Block registration pattern
- Attribute structure
- RichText patterns
- MediaUpload patterns
- InspectorControls structure
- PHP rendering patterns
- Naming conventions
- Dynamic block patterns

If a decision is unclear, follow the example block.

---

# IMPORT RULE

All WordPress APIs must be imported from:

```js
@mbn/editor
```

Never import directly from `@wordpress/*`.

Follow the same import pattern used by the example block.

---

# BLOCK REQUIREMENTS

All generated blocks must use:

- Namespace: `mbn-theme/*`
- Category: `mbn-blocks`
- Text Domain: `mbn-theme`
- API Version: `3`

All blocks must be Dynamic Gutenberg Blocks:

```js
save: () => null
```

All frontend rendering must occur in `render.php`.

---

# HTML PRESERVATION RULE

The static HTML from Phase 1 is the source of truth for Phase 2.

Preserve:

- All HTML tags and nesting
- All class names
- All layout structure
- All inline styles
- All data attributes

Do not:

- Rewrite or simplify markup
- Reorganize sections
- Remove wrappers or classes
- Change layouts

The final frontend output from `render.php` must be visually identical to `index.html`.

---

# NO TAILWIND RULE

Do not use Tailwind CSS anywhere.

Not in `index.html`.
Not in `render.php`.
Not in `index.js`.
Not in `style.css`.

Write all styles as traditional CSS in `style.css`.

Use semantic class names only.

---

# ATTRIBUTE EXTRACTION RULE

Analyze the HTML and identify all editable content.

Editable content includes:

- Headings
- Paragraphs
- Labels
- CTA text
- Button text and URLs
- Navigation links
- Image URLs and IDs
- Card content (titles, descriptions, images)
- Testimonials
- FAQ items
- Statistics
- Any repeating list of items

Do NOT create attributes for:

- CSS class names
- Layout structure
- Styling properties

Attributes represent **content only**.

---

# BLOCK.JSON RULE

Every piece of editable content must have a corresponding attribute.

## Scalar attributes

```json
{
  "heading": {
    "type": "string",
    "default": "Default Heading"
  },
  "buttonLabel": {
    "type": "string",
    "default": "Learn More"
  },
  "buttonUrl": {
    "type": "string",
    "default": "#"
  }
}
```

## Image attributes

```json
{
  "imageId": {
    "type": "number",
    "default": 0
  },
  "imageUrl": {
    "type": "string",
    "default": ""
  }
}
```

## Repeating / list attributes

```json
{
  "cardItems": {
    "type": "array",
    "default": [
      {
        "title": "Card Title",
        "description": "Card description.",
        "imageId": 0,
        "imageUrl": ""
      }
    ]
  }
}
```

Never create numbered scalar attributes (`card1Title`, `card2Title`).

All list-like content must use arrays so users can add and remove items.

## Required block.json properties

After the `attributes` object, always include these three properties:

```json
"editorScript": "file:./index.js",
"style": "file:./style.css",
"render": "file:./render.php"
```

These properties are **mandatory** for all blocks. They tell WordPress:
- Where to find the editor JavaScript (`editorScript`)
- Where to find the block styles (`style`)
- Where to find the PHP render template for dynamic blocks (`render`)

---

# REPEATING CONTENT RULE

Identify every list-like structure in the HTML:

- Navigation menu items
- Card grids
- Team member grids
- Testimonial sliders
- FAQ accordions
- Feature lists
- Link columns in footer

All of these must use `type: "array"` attributes.

Users must be able to:

- **Add** new items to any list
- **Remove** existing items
- **Edit** all fields of each item

If the HTML has a fixed number of items (e.g. 6 cards), the `default` array should contain those 6 items pre-filled with the original content.

## Section Reordering Rule

The generated block must support rearranging the order of major sections in the editor.

Implementation pattern:

- Keep a single unified block (`save: () => null`).
- Define a `sectionOrder` array attribute in `block.json` with stable section IDs in default Figma order.
- Render sections in `render.php` by iterating `sectionOrder` and outputting the matching section template/markup.
- In `index.js`, provide reorder controls in InspectorControls:
  - Move Up
  - Move Down
  - Optional drag-and-drop when available

Rules:

- Reordering must not create additional Gutenberg blocks.
- Reordering must preserve each section's content attributes.
- Reordering controls must be available to content editors without code edits.
- If no custom order is set, fallback to the original Figma section order.

## Section Visibility Rule

The generated block must support hiding/showing major sections in the editor.

Implementation pattern:

- Define a `sectionVisibility` object attribute in `block.json` keyed by stable section IDs.
- Default every section to visible (`true`) unless the design explicitly requires hidden-by-default behavior.
- In `render.php`, skip rendering sections marked as hidden.
- In `index.js`, provide per-section visibility controls in InspectorControls (ToggleControl per section).

Rules:

- Visibility controls must not create additional Gutenberg blocks.
- Hidden sections must preserve their content attributes.
- Editors must be able to toggle sections back to visible at any time.
- If `sectionVisibility` is missing/incomplete, fallback to visible (`true`) for safety.

---

# RICHTEXT CONTENT RULE

Editable content must support Gutenberg RichText formatting whenever appropriate.

The generated block must allow editors to:

- Apply bold formatting
- Apply italic formatting
- Insert links
- Edit linked text
- Preserve line breaks
- Preserve inline formatting
- Preserve RichText HTML output

Plain text fields are not sufficient for most user-facing content areas.

## Use RichText For

- Headings
- Subheadings
- Paragraphs
- CTA text
- Card titles
- Card descriptions
- Testimonial content
- FAQ answers
- Footer content
- Any user-facing content that may require formatting

RichText content must support these formats:

```js
[
  'core/bold',
  'core/italic',
  'core/link'
]
```

## Do Not Use RichText For

- URLs
- Button URLs
- Navigation URLs
- Image IDs
- Image URLs
- Settings
- Toggle values
- Select values
- Technical configuration fields

Use TextControl, ToggleControl, SelectControl, or MediaUpload for those fields.

## Link Support Rule

Whenever content can contain links, RichText must include:

```js
'core/link'
```

Examples:

- Hero descriptions
- CTA descriptions
- Feature descriptions
- Body copy
- Footer content
- Testimonials

Links must be preserved in `render.php` output.

## block.json Rule For RichText

RichText attributes must be stored as strings and may include HTML markup.

```json
{
  "heroHeading": {
    "type": "string",
    "default": "<strong>Build Better Websites</strong>"
  },
  "heroDescription": {
    "type": "string",
    "default": "Create modern websites with Gutenberg blocks."
  }
}
```

Do not strip formatting from RichText default values.

## Figma Text Preservation Rule

When converting Figma text into Gutenberg attributes:

- Preserve emphasis
- Preserve bold text
- Preserve italic text
- Preserve hyperlinks if provided
- Preserve line breaks
- Preserve intentional text structure

Do not flatten formatted content into plain text.

---

# RENDER.PHP RULE

Move the complete HTML from `index.html` into `render.php`.

`render.php` becomes the frontend source of truth.

Keep the HTML structure intact.

Only replace hardcoded values with PHP attribute output.

## Text replacement example

Before:

```html
<h2 class="section-heading">Welcome to MBN</h2>
```

After:

```php
<h2 class="section-heading"><?php echo wp_kses_post( $attributes['heading'] ); ?></h2>
```

## Image replacement example

Before:

```html
<img src="assets/images/hero.png" alt="Hero">
```

After:

```php
<img src="<?php echo esc_url( ! empty( $attributes['heroImageUrl'] ) ? $attributes['heroImageUrl'] : get_template_directory_uri() . '/blocks/{block-name}/assets/images/hero.png' ); ?>" alt="Hero">
```

## Loop replacement example

Before:

```html
<div class="card">...</div>
<div class="card">...</div>
<div class="card">...</div>
```

After:

```php
<?php foreach ( $attributes['cards'] as $card ) : ?>
<div class="card">
  <h3><?php echo wp_kses_post( $card['title'] ); ?></h3>
  <p><?php echo wp_kses_post( $card['description'] ); ?></p>
</div>
<?php endforeach; ?>
```

## Security rules

Always use:
- `wp_kses_post()` for RichText-formatted content
- `esc_html()` only for true plain-text values
- `esc_url()` for URLs and image sources
- `esc_attr()` for HTML attributes

Always include the stylesheet:

```php
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() ); ?>/blocks/{block-name}/style.css">
```

Do not restructure the HTML.

Do not add Tailwind classes.

---

# INDEX.JS RULE

`index.js` exists **only** to edit the content that `render.php` renders.

`index.js` does **not** replicate the visual output of the HTML.

`index.js` does **not** rebuild the page layout.

The editor must provide a WYSIWYG authoring experience using:

- **InnerBlocks** for editable content regions/sections where block-based editing is appropriate
- **RichText** for formatted text fields

## What index.js must contain

- A WYSIWYG block editor preview using InnerBlocks where appropriate and inline RichText for text fields.
- InspectorControls sidebar panels for all content groups.
- Full controls for every attribute defined in `block.json`.

## Inline editing (in the block preview)

Use RichText for:

- Headings
- Subheadings
- Paragraphs
- CTA text
- Card titles
- Card descriptions
- Testimonial content
- FAQ answers
- Footer content

RichText fields must use this as the default `allowedFormats`:

```js
[
  'core/bold',
  'core/italic',
  'core/link'
]
```

This default applies to all RichText fields unless a specific field requires additional formats.

Exception policy:

- Additional formats are allowed only for long-form body content where the Figma design or content requirements explicitly need them.
- Any exception must be intentional and documented in the generated block code comments near that field.
- Do not add extra formats globally.

## WYSIWYG requirement

- Prefer InnerBlocks for richer editing regions where users need Gutenberg-native editing behavior.
- Use RichText for all formatted text values listed above.
- Do not reduce formatted content fields to plain text controls.

## Sidebar editing (InspectorControls)

Use InspectorControls PanelBody panels for:

- Navigation items (expandable array)
- Image uploads (MediaUpload)
- Button URLs (TextControl)
- Card arrays (expandable list with add/remove)
- Team member arrays (expandable list with add/remove)
- Footer links and settings
- Section order controls (move up/down and optional drag reorder)
- Section visibility controls (show/hide per section)

TextControl should only be used for:

- URLs
- Settings
- Technical fields
- Non-formatted values

Never use TextControl for headings, descriptions, testimonials, FAQ answers, CTA labels, or other formatted content.

## Expandable array controls

Any attribute that is an array must render as an **expandable list** in the sidebar.

Each array item panel must have:
- An input for every field in the item object
- A "Remove" button to delete the item
- An "Add Item" button below the list to append a new item

Example pattern:

```jsx
{ items.map( ( item, index ) => (
  <div key={ index } style={ { border: '1px solid #ddd', padding: '1rem', marginBottom: '1rem' } }>
    <RichText
      tagName="h3"
      value={ item.title }
      onChange={ ( value ) => {
        const updated = [ ...items ];
        updated[ index ] = { ...updated[ index ], title: value };
        setAttributes( { items: updated } );
      } }
      placeholder="Title"
    />
    <RichText
      tagName="p"
      value={ item.description }
      onChange={ ( value ) => {
        const updated = [ ...items ];
        updated[ index ] = { ...updated[ index ], description: value };
        setAttributes( { items: updated } );
      } }
      placeholder="Description"
    />
    <TextControl
      label="URL"
      value={ item.url }
      onChange={ ( value ) => {
        const updated = [ ...items ];
        updated[ index ] = { ...updated[ index ], url: value };
        setAttributes( { items: updated } );
      } }
    />
    <Button isDestructive onClick={ () => {
      setAttributes( { items: items.filter( ( _, i ) => i !== index ) } );
    } }>
      Remove
    </Button>
  </div>
) ) }
<Button isPrimary onClick={ () =>
  setAttributes( { items: [ ...items, { title: '', description: '', imageId: 0, imageUrl: '' } ] } )
}>
  Add Item
</Button>
```

---

# CSS RESPONSIVENESS RULE

`style.css` must include styles for all three breakpoints.

## Structure

```css
/* === DESKTOP (default, 1024px and above) === */

.section { ... }
.card { ... }

/* === TABLET (768px – 1023px) === */

@media screen and (max-width: 1023px) {
  .section { ... }
  .card { ... }
}

/* === MOBILE (below 768px) === */

@media screen and (max-width: 767px) {
  .section { ... }
  .card { ... }
}
```

Rules:
- Desktop styles come first (no media query wrapper).
- Tablet and mobile styles override via media queries.
- Every section must be usable on all three device sizes.
- Navigation must collapse or stack on mobile.
- Card grids must become single-column on mobile.
- Font sizes must scale down on smaller screens.
- Use `clamp()` for fluid typography where applicable.
- Never use `position: absolute` for layout-critical elements on tablet/mobile unless properly handled.

---

# SELF REVIEW

Before generating any file, verify:

**Phase 1 (HTML):**
- [ ] New dedicated folder created at `blocks/{block-name}/`
- [ ] `index.html` and `style.css` at folder root
- [ ] `assets/images/` folder created
- [ ] All images use local paths: `assets/images/filename.ext`
- [ ] No remote image URLs in `index.html`
- [ ] Required images documented in Architecture Summary
- [ ] No Tailwind classes anywhere
- [ ] CSS covers desktop, tablet, and mobile
- [ ] Design matches Figma faithfully

**Phase 2 (Gutenberg):**
- [ ] Single unified block — no sectioning into multiple blocks
- [ ] `block.json` contains all content as attributes
- [ ] `block.json` includes `editorScript`, `style`, and `render` properties
- [ ] All repeating content uses array attributes
- [ ] Arrays support add / remove / edit
- [ ] `render.php` is a direct conversion of `index.html`
- [ ] RichText content is rendered with `wp_kses_post()`
- [ ] Plain text content is rendered with `esc_html()`
- [ ] All URL output uses `esc_url()`
- [ ] `render.php` links to `style.css`
- [ ] `index.js` does NOT replicate the HTML layout
- [ ] `index.js` provides WYSIWYG editing using InnerBlocks + RichText
- [ ] RichText fields use `allowedFormats` for bold, italic, and link
- [ ] `index.js` provides sidebar panels for all content groups
- [ ] Array attributes render as expandable lists in the sidebar
- [ ] TextControl is only used for URLs/settings/technical plain values
- [ ] Section order can be rearranged by editors in InspectorControls
- [ ] `render.php` outputs sections based on `sectionOrder` with safe fallback to default order
- [ ] Editors can show/hide sections using InspectorControls
- [ ] `render.php` respects `sectionVisibility` and safely defaults missing values to visible
- [ ] `save()` returns null
- [ ] No Tailwind anywhere
- [ ] No build tooling changes

If any item fails, revise before outputting code.

---

# OUTPUT FORMAT

Provide files in this order:

1. **Architecture Summary** - Include:
   - Folder structure
   - Attribute schema overview
   - **List of required images with descriptions** (e.g., `hero-background.jpg - Hero section background image`)
2. `index.html`
3. `style.css`
4. `block.json`
5. `render.php`
6. `index.js`

Generate complete, production-ready code.

No pseudocode.

No TODO comments.

No placeholder content.

All text defaults must contain the actual content from the Figma design.

All image defaults must fall back to the locally downloaded assets.

**Do NOT create any documentation files.**

Do not create README.md or any other markdown documentation files.

Do not create implementation summaries or documentation pages.

Only generate the six essential block files listed above.