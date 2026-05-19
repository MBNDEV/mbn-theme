# Blackline Guardian Fund Theme — SOP & Developer Guide

**Theme:** `blacklineguardianfund-theme` (package name: `mbn-theme`)  
**Version:** 1.0.2  
**Stack:** WordPress + Native Gutenberg Blocks + Tailwind CSS + React + Webpack  
**Author:** My Biz Niche

---

## Table of Contents

1. [Project Overview & File Structure](#1-project-overview--file-structure)
2. [Running the App Locally](#2-running-the-app-locally)
3. [How Blocks Work](#3-how-blocks-work)
4. [Block Files Explained](#4-block-files-explained)
5. [Generating a New Block](#5-generating-a-new-block)
6. [Using the AI Agent](#6-using-the-ai-agent)
7. [Block Registry](#7-block-registry)
8. [Block Template Synchronization (Export & Import)](#8-block-template-synchronization-export--import)
9. [Page Template Synchronization (Export & Import)](#9-page-template-synchronization-export--import)
10. [Navigation Menu Synchronization (Export & Import)](#10-navigation-menu-synchronization-export--import)
11. [Webpack & the Build System](#11-webpack--the-build-system)
12. [The `inc/` Folder — File by File](#12-the-inc-folder--file-by-file)
13. [functions.php — How It Wires Everything Together](#13-functionsphp--how-it-wires-everything-together)
14. [Adding Global CSS](#14-adding-global-css)
15. [Husky — Git Hooks](#15-husky--git-hooks)
16. [Security Scanning, Static Analysis & Linting](#16-security-scanning-static-analysis--linting)
17. [Running the Linter](#17-running-the-linter)
18. [Deployment](#18-deployment)
19. [Release Checklist](#19-release-checklist)

---

## 1. Project Overview & File Structure

This theme is a **fully custom WordPress block theme**. All page sections are built as native Gutenberg blocks written in React and rendered server-side with PHP. There is no page builder plugin. Tailwind CSS handles all styling.

```
blacklineguardianfund-theme/
│
├── assets/
│   ├── build/tailwind.css        ← Compiled Tailwind output (do NOT edit manually)
│   ├── icons/                    ← SVG/icon assets
│   └── images/                   ← Static images
│
├── blocks/                       ← Block SOURCE files (edit these)
│   ├── hero-section/
│   ├── site-navbar/
│   ├── site-footer/
│   ├── donation-options/
│   ├── intro-section/
│   ├── mission-section/
│   ├── board-members/
│   └── who-we-serve/
│
├── build/                        ← Webpack OUTPUT (do NOT edit manually)
│   └── blocks/
│       ├── hero-section/
│       └── …
│
├── docs/                         ← Team documentation
│   ├── DEPLOYMENT.md
│   ├── DEPLOYMENT_CHECKLIST.md
│   ├── IMAGE-ARCHITECTURE.md
│   └── LOGGING.md
│
├── inc/                          ← Modular PHP feature files (see section 12)
│   ├── includes-block-patterns.php
│   ├── includes-block-templates.php
│   ├── includes-html-injection.php
│   ├── includes-nav-menu-sync.php
│   ├── includes-page-sync.php
│   ├── includes-post-meta.php
│   ├── includes-template-page-sync.php
│   ├── includes-template-sync-tools.php
│   ├── includes-theme-block-section.php
│   ├── includes-theme-options.php
│   ├── includes-theme-preset-options-render.php
│   └── includes-widget-loader.php
│
├── page-templates/               ← PHP page template files (source for sync)
├── resources/css/app.css         ← Tailwind CSS entry point (add global CSS here)
├── template-parts/               ← PHP partials: nav-menus/, layouts/, header/footer PHP
├── vendor/                       ← Composer PHP dependencies
│
├── block-registry.php            ← Auto-registers all blocks from build/
├── bs-config.js                  ← BrowserSync config for live reload
├── composer.json                 ← PHP dependencies & lint scripts
├── functions.php                 ← Theme bootstrap — loads all inc/ files
├── optimize.php                  ← Optional asset optimizations
├── package.json                  ← npm scripts & JS dependencies
├── phpcs.xml                     ← PHP CodeSniffer rules
├── postcss.config.js             ← PostCSS pipeline (autoprefixer, import)
├── style.css                     ← Theme metadata header (name, version, etc.)
├── tailwind.config.js            ← Tailwind theme config (colors, fonts)
├── tailwind-loader.php           ← Enqueues compiled Tailwind CSS
└── webpack.config.js             ← Custom Webpack config for block builds
```

---

## 2. Running the App Locally

### Prerequisites

- PHP 8.x + Composer
- Node.js 18+
- A local WordPress install (Laragon recommended — already configured at `d:/laragon/www/mybizniche`)

### First-Time Setup

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Development Mode (Watch + Live Reload)

```bash
npm run dev
```

This runs **three watchers in parallel**:

| Watcher | What it does |
|---------|-------------|
| `watch:css` | Rebuilds `assets/build/tailwind.css` whenever a PHP/JS/CSS file changes |
| `watch:blocks` | Rebuilds block JS bundles in `build/blocks/` via Webpack |
| `watch:browser` | BrowserSync live-reloads the browser on file save |

### Production Build

```bash
npm run build
```

Runs `build:css` (minified Tailwind) and `build:blocks` (Webpack) sequentially.

### Build a Single Block Only

```bash
BLOCK=hero-section npm run build:blocks
```

Use the `BLOCK` environment variable with any part of the block folder name to filter which block gets compiled.

---

## 3. How Blocks Work

This theme uses **native WordPress Gutenberg blocks** — not Carbon Fields, not ACF, not a page builder. Every visual section on the site is a custom block.

The data flow is:

```
blocks/{name}/index.js      ← Registers the block with WordPress
       |
       ├── edit.js          ← React component shown inside the block editor
       └── block.json       ← Declares attributes, name, scripts, styles
                                        ↓
                             Webpack compiles to build/blocks/{name}/
                                        ↓
                             block-registry.php registers from build/blocks/
                                        ↓
                             render.php renders HTML on the front end
```

**Key concept:** The editor (React) and the front end (PHP) are separate. The `edit.js` React component is only used inside the WordPress admin editor. On the actual site, WordPress calls `render.php` and passes the saved attribute values to it.

---

## 4. Block Files Explained

Every block folder under `blocks/` contains these files:

### `block.json` — Block Metadata & Attributes

This is the single source of truth for the block. It tells WordPress everything about the block.

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "mbn-theme/hero-section",
  "title": "Hero Banner",
  "category": "mbn-blocks",
  "icon": "cover-image",
  "description": "Full-screen hero banner with background image, heading, subheading, and CTA buttons",
  "textdomain": "mbn-theme",
  "supports": {
    "html": false,
    "align": ["full"]
  },
  "attributes": {
    "heading": { "type": "string", "default": "ELITE PROTECTION FOR THE MOST VULNERABLE." },
    "overlayOpacity": { "type": "number", "default": 40 },
    "verticalPosition": { "type": "string", "default": "bottom", "enum": ["center", "bottom"] }
  },
  "editorScript": "file:./index.js",
  "style": "file:./style.css",
  "editorStyle": "file:./style.css",
  "render": "file:./render.php"
}
```

| Field | Purpose |
|-------|---------|
| `name` | Unique block identifier — must be `mbn-theme/{slug}` |
| `category` | Groups the block in the editor inserter under "MBN Blocks" |
| `attributes` | Defines all editable data (text, images, numbers, enums) stored with the block |
| `editorScript` | The compiled JS file for the block editor UI |
| `style` | CSS loaded on both front end and in the editor |
| `render` | The PHP file that renders the block on the front end |

**Attribute types:** `string`, `number`, `boolean`, `array`, `object`

---

### `index.js` — Block Registration

Registers the block type with WordPress and connects the `edit.js` component.

```js
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';
import './style.css';

registerBlockType(metadata.name, {
  edit: Edit,
  // No "save" function — server-side rendered via render.php
});
```

**Note:** There is no `save` function. Returning `null` (or omitting it) tells WordPress this is a dynamic/server-rendered block. All HTML comes from `render.php`.

---

### `edit.js` — Block Editor UI (React)

This is a React component that renders the block inside the WordPress block editor. It uses WordPress's block editor APIs to provide a WYSIWYG editing experience.

```jsx
import {
  useBlockProps,
  RichText,
  MediaUpload,
  InspectorControls
} from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl, SelectControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
  const { heading, overlayOpacity, backgroundImageUrl } = attributes;

  const blockProps = useBlockProps({
    className: 'hero-banner-editor',
    style: { backgroundImage: `url(${backgroundImageUrl})` },
  });

  return (
    <>
      {/* InspectorControls appear in the right sidebar */}
      <InspectorControls>
        <PanelBody title="Overlay Settings">
          <RangeControl
            label="Overlay Opacity (%)"
            value={overlayOpacity}
            onChange={(value) => setAttributes({ overlayOpacity: value })}
            min={0} max={100}
          />
        </PanelBody>
      </InspectorControls>

      {/* This is what appears in the editor canvas */}
      <section {...blockProps}>
        <RichText
          tagName="h1"
          value={heading}
          onChange={(value) => setAttributes({ heading: value })}
          placeholder="Enter heading..."
        />
      </section>
    </>
  );
}
```

Common editor components used in this theme:

| Component | Purpose |
|-----------|---------|
| `useBlockProps` | Applies required block wrapper attributes |
| `InspectorControls` | Adds controls to the right sidebar panel |
| `RichText` | Inline-editable text area |
| `MediaUpload` | WordPress media library picker |
| `RangeControl` | Numeric slider |
| `SelectControl` | Dropdown select |
| `TextControl` | Plain text input |

---

### `render.php` — Front-End Output

Called by WordPress on every page load. Receives the block's saved attributes as `$attributes`.

```php
<?php
// $attributes is injected automatically by WordPress
$heading            = $attributes['heading'] ?? '';
$overlay_opacity    = absint( $attributes['overlayOpacity'] ?? 40 );
$overlay_breakpoint = $attributes['overlayBreakpoint'] ?? 'always';

// Build Tailwind classes based on attribute values
$overlay_class_map = [
  'always' => 'block',
  'sm'     => 'block sm:hidden',
  'md'     => 'block md:hidden',
];
$overlay_class = $overlay_class_map[ $overlay_breakpoint ] ?? 'block';

// Always use get_block_wrapper_attributes() for the outer wrapper
$wrapper_attrs = get_block_wrapper_attributes([
  'class' => 'hero-banner relative w-full min-h-screen',
]);
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore ?>>
  <div class="absolute inset-0 bg-black <?php echo esc_attr( $overlay_class ); ?>"></div>
  <h1><?php echo wp_kses_post( $heading ); ?></h1>
</section>
```

**Security rules in render.php:**

| Function | When to use |
|----------|-------------|
| `esc_html()` | Plain text output |
| `esc_attr()` | HTML attribute values (class, id, etc.) |
| `esc_url()` | URLs |
| `wp_kses_post()` | Rich text that may contain basic HTML tags |
| `absint()` | Integers (e.g. opacity, IDs) |
| `sanitize_text_field()` | User-supplied plain strings |

---

### `style.css` — Block Styles

Loaded on both the **front end** and inside the **editor**. Write Tailwind classes directly in the PHP templates; reserve this file for styles that cannot be expressed with utility classes.

```css
/* Any CSS that cannot be expressed as Tailwind utilities goes here */
.hero-banner {
  container-type: inline-size;
}
```

---

## 5. Generating a New Block

There is no automatic scaffolding CLI. Create the block manually:

**Step 1 — Create the folder and files:**

```
blocks/
└── my-new-block/
    ├── block.json
    ├── index.js
    ├── edit.js
    ├── render.php
    └── style.css       (optional)
```

**Step 2 — `block.json` minimum template:**

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "mbn-theme/my-new-block",
  "title": "My New Block",
  "category": "mbn-blocks",
  "description": "Short description of this block.",
  "textdomain": "mbn-theme",
  "attributes": {
    "title": { "type": "string", "default": "Default Title" }
  },
  "editorScript": "file:./index.js",
  "style": "file:./style.css",
  "render": "file:./render.php"
}
```

**Step 3 — `index.js`:**

```js
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';
import './style.css';

registerBlockType(metadata.name, { edit: Edit });
```

**Step 4 — `edit.js` (minimal):**

```jsx
import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function Edit({ attributes, setAttributes }) {
  return (
    <div {...useBlockProps()}>
      <RichText
        tagName="h2"
        value={attributes.title}
        onChange={(title) => setAttributes({ title })}
        placeholder="Enter title..."
      />
    </div>
  );
}
```

**Step 5 — `render.php` (minimal):**

```php
<?php
$title = $attributes['title'] ?? '';
$wrapper_attrs = get_block_wrapper_attributes();
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore ?>>
  <h2><?php echo esc_html( $title ); ?></h2>
</div>
```

**Step 6 — Build:**

```bash
# Build just your new block
BLOCK=my-new-block npm run build:blocks

# Or build all blocks
npm run build:blocks
```

The block will automatically be discovered and registered — no `functions.php` edits needed.

---

## 6. Using the AI Agent

There are two AI agent configurations in this project:

### 6a. Cursor IDE Agent — `.cursor/rules/instructions.mdc`

This file applies automatically whenever Cursor AI works on any `.php` file in the project (`globs: **/*.php`, `alwaysApply: true`). It enforces code style and security rules so Cursor produces standards-compliant PHP without needing reminders each session.

Rules enforced:

- WordPress PHP coding standards (snake_case functions, PascalCase classes, UPPER_SNAKE_CASE constants)
- Security: all input sanitized, all output escaped — specific functions required per context
- 2-space indentation, no tabs
- `array()` syntax — not `[]` shorthand
- Max cyclomatic complexity of 10 per function (warning); use early-return / guard clauses
- Max nesting depth of 3 (warning)
- No commented-out code or TODO/FIXME comments
- Text domain: `mbn-theme` for all `__()` / `_e()` calls
- `get_block_wrapper_attributes()` output requires a `phpcs:ignore` comment
- Native Gutenberg block architecture only — no Carbon Fields, no ACF

**How to use Cursor effectively:**

1. Open Cursor in the theme directory
2. Ask it to create a block: *"Create a new block called `team-grid` with a title, description, and repeatable team member cards (name, role, photo)"*
3. The agent produces files following the coding standards automatically
4. After the agent writes PHP files, always run `composer run lint:fix` before committing

---

### 6b. GitHub Copilot / Claude Agent — `.github/agents/wp-gutenberg-dev.agent.md`

**File:** [.github/agents/wp-gutenberg-dev.agent.md](.github/agents/wp-gutenberg-dev.agent.md)

This is a **custom agent definition** (compatible with GitHub Copilot agent mode and Claude Code agent mode). It is a prompt document that instructs the AI how to work with this specific theme when building or modifying Gutenberg blocks.

**Frontmatter:**
```yaml
name: "WP Gutenberg Developer"
tools: [read, edit, search, execute]
argument-hint: "Describe the block or feature to implement"
```

**When to use it:** Invoke this agent when creating a new block, implementing a Figma design as a block, or working with the WordPress Block API.

**What it knows:**
- Native Gutenberg block architecture (`block.json` + React + `render.php`)
- WordPress block editor components: `InspectorControls`, `RichText`, `MediaUpload`, `BlockControls`
- Tailwind CSS integration
- Drag-and-drop repeater fields using `@dnd-kit` (for array-type attributes like cards, members)
- PHP `render.php` patterns with proper escaping

**Backup file:** [.github/agents/wp-gutenberg-dev.agent.md.backup](.github/agents/wp-gutenberg-dev.agent.md.backup) — this is an **older version** of the agent that was written for a Carbon Fields architecture (now replaced). It references `Abstract_Block`, `carbon-loader.php`, and `class-{blockname}.php` — none of which exist in the current theme. **Do not use the backup file.**

---

### 6c. Agent Conformance Analysis — Known Gaps vs. Real Codebase

A careful comparison between the agent's instructions and the actual blocks in `blocks/` reveals the following issues. **Read these before using the agent so you know what to check or correct after it generates code.**

---

#### GAP 1 — CRITICAL: Wrong `npm` development command

**Agent says:**
```bash
npm run start  # Development watch mode
```

**Reality:** `npm run start` does not exist in `package.json`. It will fail with "missing script: start".

**Correct command:**
```bash
npm run dev          # Watch CSS + blocks + BrowserSync (all parallel)
npm run watch:blocks # Watch blocks only
```

The confusion comes from `wp-scripts start` (the underlying webpack watcher binary), which is invoked internally by `npm run watch:blocks`. The agent conflates the two.

---

#### GAP 2 — CRITICAL: Wrong Tailwind color names in button/component examples

**Agent says:**
```css
.btn-primary {
  @apply bg-gradient-to-b from-amber-100 to-amber-700 text-amber-900;
}
```

**Reality:** The theme does **not** use standard Tailwind `amber-*` colors for brand styling. It has a custom palette in `tailwind.config.js`:

| Agent class | Correct class |
|-------------|--------------|
| `from-amber-100` | `from-gold-light` |
| `to-amber-700` | `to-gold` |
| `text-amber-900` | `text-gold-dark` |
| `bg-gray-900` | `text-dark-text` |
| `text-gray-600` | `text-paragraph-gray` |

Always use the custom color tokens from `tailwind.config.js`. Using `amber-*` will compile (Tailwind includes those in the default palette) but the colors will be wrong.

**Full custom color reference:**

| Token | Hex | Use |
|-------|-----|-----|
| `gold-light` | `#FCE5B0` | Button gradient start, highlights |
| `gold` | `#B89352` | Primary gold accent |
| `gold-dark` | `#6B4502` | Button text, dark gold |
| `cream` | `#F9F5EE` | Page/section background |
| `cream-light` | `#FFF6E5` | Light cream backgrounds |
| `dark-text` | `#25272B` | Body text |
| `footer-bg` | `#191919` | Footer background |
| `paragraph-gray` | `#B2B2B2` | Muted/secondary text |
| `card-cream` | `#F5F1E8` | Card background (tier 1) |
| `card-gold` | `#FFF4D9` | Card background (tier 2) |
| `card-beige` | `#F8F5F0` | Card background (tier 3) |
| `check-green` | `#7CAA6D` | Checkmark/success |
| `divider-gold` | `#CEB270` | Dividers |
| `intro-bg` | `#EFEBE3` | Intro section background |

---

#### GAP 3 — IMPORTANT: Registration PHP example reads from the wrong directory

**Agent says:**
```php
function blacklineguardianfund_register_blocks() {
  $blocks_dir = __DIR__ . '/blocks';   // ← reads from SOURCE blocks/
  ...
}
```

**Reality:** The actual `block-registry.php` reads from the **compiled** output directory:
```php
$blocks_dir = get_theme_file_path( 'build/blocks' );   // ← reads from BUILD
```

Reading from `blocks/` (source) would fail because that directory contains React source files, not compiled JS. WordPress needs the compiled `build/blocks/` directory.

---

#### GAP 4 — IMPORTANT: Agent says to add registration code to `functions.php`

**Agent output format says:**
> 9. **Registration code**: PHP snippet for functions.php

**Reality:** `block-registry.php` already auto-discovers and registers every block in `build/blocks/`. **Never add another registration function** — it would cause a PHP fatal error ("Cannot redeclare function `blacklinesecurityops_register_blocks`").

No `functions.php` changes are needed when adding a new block. Just build it and it's registered.

---

#### GAP 5 — MODERATE: Inconsistent `save.js` pattern vs. the real codebase

The agent always generates a separate `save.js` file. The real codebase uses **three different patterns** — the cleanest of which requires no `save.js` at all:

| Pattern | Blocks using it | Notes |
|---------|----------------|-------|
| No `save` registered at all | hero-section, site-navbar | Cleanest — works when `"render"` is in block.json |
| Inline `save: () => null` in index.js | board-members, mission-section, who-we-serve | No separate file needed |
| Separate `save.js` returning `null` + imported in index.js | donation-options, intro-section, site-footer | What the agent generates |

All three are functionally equivalent when `"render": "file:./render.php"` is declared in `block.json`. The **preferred pattern going forward** (to keep files minimal) is the inline approach:

```js
// index.js — preferred pattern
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';
import './style.css';

registerBlockType( metadata.name, {
  edit: Edit,
  save: () => null, // server-side rendered via render.php
} );
```

If the agent generates a separate `save.js`, it still works but adds an unnecessary file. You can delete it and move to the inline pattern.

**Never** let the agent generate a `save.js` that returns actual JSX markup while also having a `render.php` — that creates a block-validation conflict where WordPress checks the saved HTML against the save function output, finds a mismatch, and breaks the block.

---

#### GAP 6 — MODERATE: `editorStyle` pointing to a non-existent `editor.css`

**Agent's block.json example:**
```json
"editorStyle": "file:./editor.css"
```

**Reality:** No block in this theme has an `editor.css` file. All blocks use `style.css` for both front end and editor:
```json
"style": "file:./style.css",
"editorStyle": "file:./style.css"
```

If the agent generates `"editorStyle": "file:./editor.css"` without creating the file, WordPress will silently skip it. To fix: either create `editor.css`, or change `editorStyle` to point to `style.css`.

---

#### GAP 7 — MODERATE: `block.json` examples missing the `"supports"` field

**Agent's block.json:** No `"supports"` field in most examples.

**Reality:** All real blocks include `"supports"`. This field is important for editor behavior:

```json
"supports": {
  "html": false,         // Prevents the HTML edit mode for this block
  "align": ["full"],     // Enables full-width alignment toggle in the toolbar
  "anchor": true         // (optional) Adds an HTML anchor/ID field
}
```

Without `"html": false`, editors can accidentally switch to HTML mode and corrupt the block. Without `"align": ["full"]`, the block won't offer the full-width toggle that most sections need.

---

#### GAP 8 — MINOR: Backup file contains a completely different (legacy) architecture

[.github/agents/wp-gutenberg-dev.agent.md.backup](.github/agents/wp-gutenberg-dev.agent.md.backup) still exists and describes a **Carbon Fields** approach:

- `class-{blockname}.php` extending `Abstract_Block`
- `Container::make( 'block', … )`
- `carbon-loader.php` for auto-discovery
- `blocks-render/render-{name}.php` template path

**None of this exists in the current codebase.** Carbon Fields was removed and replaced with native Gutenberg blocks. The backup file should not be used as reference. It is preserved only for historical context.

---

### 6d. Agent Usage Checklist

After the agent generates block files, verify these points before running `npm run build:blocks`:

- [ ] `block.json` has `"render": "file:./render.php"` (not missing)
- [ ] `block.json` has a `"supports"` field with at least `"html": false`
- [ ] `index.js` uses `save: () => null` or no save — not a static save with JSX
- [ ] No separate `save.js` file with real JSX markup
- [ ] Tailwind classes use brand tokens (`gold`, `cream`, `dark-text`) not `amber-*`
- [ ] `editorStyle` in block.json points to `style.css`, not `editor.css`
- [ ] No `register_block_type()` call was added to `functions.php`
- [ ] PHP in `render.php` uses `get_block_wrapper_attributes()` on the outer element
- [ ] All render.php output is escaped (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`)
- [ ] After PHP files are written: run `composer run lint:fix`

---

## 7. Block Registry

**File:** [block-registry.php](block-registry.php)

This file runs on WordPress `init` and auto-registers every block found in `build/blocks/`.

```php
function blacklinesecurityops_register_blocks() {
  $blocks_dir    = get_theme_file_path( 'build/blocks' );
  $block_folders = glob( $blocks_dir . '/*', GLOB_ONLYDIR );

  foreach ( $block_folders as $block_folder ) {
    if ( file_exists( $block_folder . '/block.json' ) ) {
      register_block_type( $block_folder );
    }
  }
}
add_action( 'init', 'blacklinesecurityops_register_blocks' );
```

**How it works:**

1. Scans `build/blocks/` for subdirectories
2. Each subdirectory must contain a `block.json`
3. Calls `register_block_type( $path )` — WordPress reads `block.json` and wires up scripts, styles, and the render callback automatically

**Block category registration:** Also in this file, a custom "MBN Blocks" category (`mbn-blocks`) is registered so theme blocks appear in their own section in the editor inserter.

**Admin debug notice:** A dismissible admin notice lists all registered `mbn-theme/*` blocks after login. This is useful when debugging why a block isn't appearing in the editor.

**Key rule:** You must run `npm run build:blocks` before a new block appears in WordPress. The source files in `blocks/` are not read directly — only the compiled files in `build/blocks/` are.

---

## 8. Block Template Synchronization (Export & Import)

**Files:**  
- [inc/includes-block-templates.php](inc/includes-block-templates.php) — Defines the custom post type and seed logic  
- [inc/includes-template-sync-tools.php](inc/includes-template-sync-tools.php) — Admin UI for export/import

Block Templates are used for **global site chrome** (Header, Footer) and **page layout wrappers**. They are stored as a custom post type `mbn_block_template` in the WordPress database, but they can be exported to PHP files and committed to Git so every environment stays in sync.

### What gets synced

| Template | Export destination |
|----------|--------------------|
| Header Template | `template-parts/header-template.php` |
| Footer Template | `template-parts/footer-template.php` |
| Layout templates | `template-parts/layouts/{name}.php` |

### Export (Local Dev → Git)

1. Edit the Block Template content in **WP Admin → Block Templates**
2. Go to **Block Templates → Sync Tools**
3. Select the templates to export
4. Click **Export Selected to Files**
5. The tool writes the block editor content to the PHP files listed above
6. Commit those files to Git:
   ```bash
   git add template-parts/
   git commit -m "Export updated header block template"
   git push
   ```

### Import (Git → Staging/Production)

1. Pull the latest code: `git pull`
2. Go to **WP Admin → Block Templates → Sync Tools**
3. Select the templates to import
4. Click **Import Selected from Files**
5. The tool reads the PHP files and updates the database records

> **Why this pattern?** The WordPress database is never synchronized between environments. Exporting to Git-tracked PHP files is the safe, version-controlled way to move content changes.

---

## 9. Page Template Synchronization (Export & Import)

**File:** [inc/includes-template-page-sync.php](inc/includes-template-page-sync.php)

This system keeps `page-templates/*.php` files in sync with the Block Templates database records.

Page template PHP files live in `page-templates/` (e.g. `template-blank.php`, `template-sidebar.php`). The sync tool auto-creates a corresponding `mbn_block_template` record for each file.

### How it works

```
page-templates/template-blank.php
       ↓
custom_theme_sync_layout_template_files_to_block_templates()
       ↓
Creates/updates: mbn_block_template post with slug "template-blank"
       ↓
Rendered when a page uses the "Blank" page template
```

This runs automatically on theme activation and can be re-triggered via the Sync Tools admin page.

**Legacy slug mapping:** The system handles old slugs: `blank` maps to `template-blank`, `sidebar` maps to `template-sidebar`.

---

## 10. Navigation Menu Synchronization (Export & Import)

**File:** [inc/includes-nav-menu-sync.php](inc/includes-nav-menu-sync.php)  
**Admin location:** Tools → Nav Menu Sync

Navigation menus live in the WordPress database and are environment-specific. This system exports them to PHP files so they can be version-controlled and imported on other environments.

### Registered theme navigation locations

Defined in `functions.php`:

| Location slug | Label |
|---------------|-------|
| `primary-menu` | Primary Menu |
| `footer-menu` | Footer Menu |
| `footer-menu-1` | Footer Menu Column 1 |
| `footer-menu-2` | Footer Menu Column 2 |
| `footer-legal` | Footer Legal Links |
| `mobile-menu` | Mobile Menu |

### Export (Local Dev → Git)

1. Build or edit menus in **Appearance → Menus**
2. Go to **Tools → Nav Menu Sync**
3. Click **Export All Menus to Files**
4. Exported PHP files are written to `template-parts/nav-menus/{slug}.php`
5. Commit and push:
   ```bash
   git add template-parts/nav-menus/
   git commit -m "Export navigation menus"
   git push
   ```

### Import (Git → Staging/Production)

1. Pull latest code: `git pull`
2. Go to **Tools → Nav Menu Sync**
3. Click **Import All Menus from Files**
4. The tool reads each PHP file, creates or updates the menu, and assigns it to the correct theme location

### How portability is achieved

| Challenge | Solution |
|-----------|----------|
| Post/page links have different IDs per environment | Stored as slugs (`/about-us`), resolved to local IDs on import |
| Term links have different IDs per environment | Stored as term slugs, resolved on import |
| Parent/child menu item relationships use DB IDs | Stored as array indices, re-wired on import |
| Theme location assignments | Stored in the export file, re-applied on import |

> **Tip:** For custom links (e.g. external URLs), always use **relative paths** like `/contact` instead of `https://example.com/contact` so they work across environments.

---

## 11. Webpack & the Build System

**File:** [webpack.config.js](webpack.config.js)

Webpack compiles block JavaScript and copies static files into the `build/` output directory.

### What Webpack does

1. **Auto-discovers entry points:** Scans `blocks/*/index.{js,jsx,ts,tsx}` and creates one bundle per block
2. **Copies static files** into `build/blocks/{name}/`:
   - `block.json` — Required for registration
   - `style.css` — Block styles
   - `render.php` — Server-side renderer
   - `script.js` — Optional frontend-only JS
3. **Outputs to** `build/blocks/{name}/index.js`

### Configuration overview

```js
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

// Auto-discover all block entry points
const blockEntries = {};
glob.sync('./blocks/*/index.{js,jsx,ts,tsx}').forEach((file) => {
  const blockName = file.match(/blocks\/([^/]+)\/index\./)[1];
  blockEntries[`blocks/${blockName}/index`] = path.resolve(file);
});

module.exports = {
  ...defaultConfig,       // Extends WordPress's default Webpack config
  entry: blockEntries,    // One entry per block
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'build'),
  },
  plugins: [
    ...defaultConfig.plugins,
    new CopyPlugin({ patterns: copyPatterns }), // Copies block.json, render.php, etc.
  ],
};
```

### Filtering builds

```bash
# Build all blocks
npm run build:blocks

# Build only the hero-section block
BLOCK=hero-section npm run build:blocks

# Watch a single block
BLOCK=site-navbar npm run watch:blocks
```

### npm scripts summary

| Script | Command | Description |
|--------|---------|-------------|
| `npm run dev` | `npm-run-all --parallel watch:*` | Watch CSS + blocks + BrowserSync |
| `npm run build` | `npm-run-all build:*` | Full production build |
| `npm run build:css` | `tailwindcss -i … --minify` | Compile Tailwind CSS only |
| `npm run build:blocks` | `wp-scripts build` | Compile blocks only |
| `npm run watch:css` | `tailwindcss … --watch` | Watch Tailwind |
| `npm run watch:blocks` | `wp-scripts start` | Watch blocks via Webpack |
| `npm run watch:browser` | `browser-sync start …` | BrowserSync live reload |

---

## 12. The `inc/` Folder — File by File

All files in `inc/` are loaded by `functions.php` using `require_once`. They are **modular feature files** — each one owns a single concern.

### `includes-theme-options.php`

Registers a **Theme Options** page under **Appearance → Theme Options** using the native WordPress Settings API.

Settings available:

| Tab | Setting | Description |
|-----|---------|-------------|
| Typography | Primary font | Font for headings (Sofia Sans, Poppins, Inter) |
| Typography | Secondary font | Font for body text |
| Appearance | Primary accent color | Brand primary color |
| Appearance | Secondary accent color | Brand secondary color |
| Performance | Remove block assets | Strip unnecessary core block CSS from front end |
| Performance | Remove classic theme styles | Remove legacy stylesheet |
| Custom HTML | Head injection | HTML injected into `<head>` |
| Custom HTML | Before body | HTML injected after `<body>` opens |
| Custom HTML | After body | HTML injected before `</body>` closes |
| Custom HTML | Footer | HTML injected in footer |

---

### `includes-post-meta.php`

Registers native WordPress **meta boxes** on post/page edit screens. Replaces the need for Carbon Fields or ACF for simple per-post data fields.

---

### `includes-theme-preset-options-render.php`

Reads the font and color settings from Theme Options and outputs them as **CSS custom properties** in the page `<head>`:

```html
<style>
  :root {
    --font-primary: 'Sofia Sans', sans-serif;
    --color-primary: #B89352;
  }
</style>
```

---

### `includes-html-injection.php`

Reads the "Custom HTML" settings from Theme Options and injects them at the correct WordPress hooks:

| Hook | Injection point |
|------|----------------|
| `wp_head` | Inside `<head>` |
| `wp_body_open` | Immediately after `<body>` opens |
| `wp_footer` | Before `</body>` closes |

---

### `includes-widget-loader.php`

Auto-loads and registers **sidebar/widget area** definitions for the theme.

---

### `includes-block-templates.php`

Registers the `mbn_block_template` **custom post type** and provides helper functions for the Header/Footer block template system.

Key responsibilities:
- Creates the CPT with admin UI (Block Templates menu)
- Seeds default Header and Footer templates on first activation
- Loads template content from PHP files in `template-parts/`
- Protects "Global" templates (Header, Footer) from being trashed
- Provides `custom_theme_get_global_header_template_output_html()` and `custom_theme_get_global_footer_template_output_html()` for rendering

---

### `includes-template-page-sync.php`

Keeps `page-templates/*.php` files synchronized with `mbn_block_template` database records. See [Section 9](#9-page-template-synchronization-export--import).

---

### `includes-template-sync-tools.php`

The admin UI under **Block Templates → Sync Tools**. Provides export and import buttons for block templates. See [Section 8](#8-block-template-synchronization-export--import).

---

### `includes-theme-block-section.php`

Utility functions for section **background images** at different breakpoints (desktop, tablet, mobile). Registers two custom image sizes:

- `CUSTOM_THEME_SECTION_BG_TABLET_IMAGE_SIZE` → `section-bg-tablet`
- `CUSTOM_THEME_SECTION_BG_MOBILE_IMAGE_SIZE` → `section-bg-mobile`

These sizes allow blocks to serve appropriately-sized background images on different devices.

---

### `includes-block-patterns.php`

Registers **block patterns** — reusable pre-composed layouts that editors can insert with one click from the pattern inserter.

Registered patterns:
- **Hero with Content** — Hero banner + intro text layout
- **Two Column Content** — Side-by-side content blocks
- **Complete Home Page** — Full home page pattern

Patterns appear under the **"Black Line Security Ops"** category in the pattern inserter.

---

### `includes-page-sync.php`

Optional system for syncing **page content** (post content body) to/from PHP files. Similar to the block template sync but for regular page posts. Useful for seeding page content on fresh installs.

Featured images are handled portably:
- Theme asset images stored as relative paths
- User-uploaded images stored as URLs

---

### `includes-nav-menu-sync.php`

The navigation menu export/import system. See [Section 10](#10-navigation-menu-synchronization-export--import) for the full workflow.

---

## 13. `functions.php` — How It Wires Everything Together

**File:** [functions.php](functions.php)

This is the **theme bootstrap file**. It does three things:

### 1. Defines theme constants

```php
define( 'CUSTOM_THEME_SECTION_BG_TABLET_IMAGE_SIZE', 'section-bg-tablet' );
define( 'CUSTOM_THEME_SECTION_BG_MOBILE_IMAGE_SIZE', 'section-bg-mobile' );
```

### 2. Registers theme features and navigation menus

```php
function blacklinesecurityops_theme_setup() {
  add_theme_support( 'wp-block-styles' );
  add_theme_support( 'editor-styles' );
  add_editor_style( 'assets/build/tailwind.css' ); // Injects Tailwind into the editor

  register_nav_menus([
    'primary-menu'  => 'Primary Menu',
    'footer-menu'   => 'Footer Menu',
    'footer-menu-1' => 'Footer Menu Column 1',
    'footer-menu-2' => 'Footer Menu Column 2',
    'footer-legal'  => 'Footer Legal Links',
    'mobile-menu'   => 'Mobile Menu',
  ]);
}
add_action( 'after_setup_theme', 'blacklinesecurityops_theme_setup' );
```

### 3. Loads all feature modules

```php
require_once get_theme_file_path( 'block-registry.php' );       // Block auto-registration
require_once get_theme_file_path( 'tailwind-loader.php' );      // Enqueue compiled CSS
require_once get_theme_file_path( 'optimize.php' );             // Optional asset cleanup

require_once get_theme_file_path( 'inc/includes-theme-options.php' );
require_once get_theme_file_path( 'inc/includes-post-meta.php' );
require_once get_theme_file_path( 'inc/includes-theme-preset-options-render.php' );
require_once get_theme_file_path( 'inc/includes-html-injection.php' );
require_once get_theme_file_path( 'inc/includes-widget-loader.php' );
require_once get_theme_file_path( 'inc/includes-block-templates.php' );
require_once get_theme_file_path( 'inc/includes-template-page-sync.php' );
require_once get_theme_file_path( 'inc/includes-theme-block-section.php' );
require_once get_theme_file_path( 'inc/includes-block-patterns.php' );
require_once get_theme_file_path( 'inc/includes-template-sync-tools.php' );
require_once get_theme_file_path( 'inc/includes-page-sync.php' );
require_once get_theme_file_path( 'inc/includes-nav-menu-sync.php' );
```

### 4. Sets up GitHub auto-updates

```php
PucFactory::buildUpdateChecker(
  'https://github.com/MBNDEV/mbn-theme',
  get_theme_file_path( 'style.css' ),
  'mbn-theme'
);
```

This uses the `yahnis-elsts/plugin-update-checker` Composer package to check the GitHub repo for new releases and show the update prompt in WordPress admin. When you tag a release (e.g. `v1.2.0`), WordPress sites running this theme will see an update notification.

---

## 14. Adding Global CSS

There are three correct places to add CSS, depending on the scope:

### 1. Global utility styles (Tailwind-based) → `resources/css/app.css`

This is the **Tailwind entry point**. It currently contains only the three Tailwind directives:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

To add custom global CSS that needs to be available everywhere, add it here **after** the Tailwind directives:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom global styles */
@layer base {
  body {
    @apply font-inter text-dark-text;
  }

  h1, h2, h3 {
    @apply font-sofia;
  }
}

@layer components {
  .btn-primary {
    @apply inline-flex items-center px-6 py-3 rounded-full font-bold uppercase bg-gold text-white;
  }
}
```

After editing, run `npm run build:css` (or it rebuilds automatically in `npm run dev`).

### 2. Custom Tailwind colors/fonts → `tailwind.config.js`

The theme has a custom color palette. Add new brand colors here:

```js
theme: {
  extend: {
    colors: {
      'new-color': '#AABBCC',
    },
    fontFamily: {
      brand: ['"Brand Font"', 'sans-serif'],
    },
  },
},
```

### 3. Block-specific styles → `blocks/{name}/style.css`

CSS that only applies to a single block. This file is loaded on both the front end and in the editor (because `block.json` declares `"style": "file:./style.css"` and `"editorStyle": "file:./style.css"`).

### 4. Editor-only global styles → `assets/css/editor.css`

If you create this file, `block-registry.php` will automatically enqueue it inside the editor (and only the editor).

---

## 15. Husky — Git Hooks

**Files:** [.husky/pre-push](.husky/pre-push)

Husky installs Git hooks automatically when you run `npm install` (via the `"prepare": "husky"` script in `package.json`).

Currently one hook is active:

### `pre-push` hook

Runs before any `git push`. If it fails, the push is rejected.

```sh
#!/usr/bin/env sh
echo "Running PHP lint checks before push..."
composer run lint

if [ $? -ne 0 ]; then
  echo "PUSH REJECTED: Lint errors found!"
  echo "Fix with: composer run lint:fix"
  exit 1
fi

echo "All lint checks passed!"
```

**Purpose:** Prevents code that violates WordPress PHP coding standards from being pushed to the remote repository.

**If your push is rejected:**

```bash
# Auto-fix most issues
composer run lint:fix

# Stage the fixed files and try again
git add .
git commit -m "Fix PHP lint errors"
git push
```

---

## 16. Security Scanning, Static Analysis & Linting

The theme has three layers of code quality enforcement:

### Layer 1 — Security Scanning (`scripts/security-scan.php`)

A PHP CLI script that scans all theme files for **hardcoded credentials and secrets**. Run via `composer run lint:security`.

**Patterns it detects:**

| Pattern | Example |
|---------|---------|
| AWS access keys | `AKIA...` |
| GitHub tokens | `ghp_`, `gho_`, `github_pat_` |
| Stripe live keys | `sk_live_`, `pk_live_` |
| Private keys | `-----BEGIN PRIVATE KEY-----` |
| Bearer tokens | `Bearer ey...` |
| Hardcoded passwords | `password = "..."` in PHP/env files |

Skips: `node_modules/`, `vendor/`, `build/`, `.git/`, `package-lock.json`, `composer.lock`

### Layer 2 — PHP CodeSniffer / Static Analysis (`phpcs.xml`)

Checks PHP code against **WordPress Coding Standards**. Configuration:

| Rule | Threshold |
|------|-----------|
| Standards | WordPress-Core, WordPress-Extra, WordPress-Docs |
| Indentation | 2 spaces |
| Cyclomatic complexity | Warning at 10, error at 20 |
| Nesting depth | Warning at 3, error at 5 |
| Commented-out code | Max 35% of file |
| TODO/FIXME | Not allowed — use issue tracker |

Excluded from scanning:
- `vendor/`, `node_modules/`, `build/`, `scripts/`
- `template-parts/page-patterns/`

### Layer 3 — Implicit JS/CSS Standards

JavaScript follows WordPress's `@wordpress/scripts` defaults (ESLint + Prettier under the hood). No separate ESLint config file is present — the WordPress scripts toolchain enforces it.

---

## 17. Running the Linter

### PHP linting

```bash
# Check for violations (read-only, no changes)
composer run lint

# Auto-fix fixable violations
composer run lint:fix

# Full pipeline: security scan + fix (twice) + check
composer run lint:run
```

The `lint:run` script is the one to use before tagging a release. It runs:
1. `composer run lint:security` — Credential scan
2. `composer run lint:fix` — Auto-fix (run twice to catch cascading fixes)
3. `composer run lint` — Final check (must pass with zero errors)

### npm linting

```bash
# Lint PHP via npm (delegates to composer)
npm run lint:php

# Auto-fix PHP via npm
npm run lint:php:fix
```

---

## 18. Deployment

**Full docs:** [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)  
**Setup checklist:** [docs/DEPLOYMENT_CHECKLIST.md](docs/DEPLOYMENT_CHECKLIST.md)

### Branch → Environment mapping

| Branch | Environment |
|--------|-------------|
| `develop` | Staging |
| `master` | Production |

### What GitHub Actions deploys

1. Builds block JS (`npm run build`)
2. Compiles Tailwind CSS
3. Installs Composer production dependencies
4. Rsyncs files to the WordPress theme directory on the server
5. Attempts to flush WordPress cache via WP-CLI

### Files excluded from deployment

`node_modules/`, `.git/`, `.github/`, `resources/css/` (source only), `blocks/*/src/`, webpack/tailwind config files, `references/`, lock files.

### Post-deployment: sync block templates

After deploying code that includes exported block templates:

1. SSH or go to WP Admin on the deployed environment
2. **Block Templates → Sync Tools → Import from Files**

### Development workflow

```bash
# 1. Create feature branch
git checkout -b feature/new-hero-block

# 2. Develop locally
npm run dev

# 3. Build for production check
npm run build

# 4. Commit
git add .
git commit -m "Add new hero block with donation CTA"

# 5. Merge to develop (auto-deploys to Staging)
git checkout develop
git merge feature/new-hero-block
git push origin develop

# 6. Test on Staging
# 7. Merge to master (auto-deploys to Production)
git checkout master
git merge develop
git push origin master
```

### Rollback

```bash
# Option 1: Revert via Git (triggers re-deploy)
git revert HEAD
git push origin master

# Option 2: Tag-based rollback
git checkout v1.0.1
git push origin master --force  # Only in emergency
```

---

## 19. Release Checklist

Based on [.cursor/rules/instructions.mdc](.cursor/rules/instructions.mdc):

1. Pull latest `main`/`master`
2. Update the `Version` header in `style.css`:
   ```
   Version: 1.2.0
   ```
3. Run the full lint pipeline:
   ```bash
   composer run lint:run
   ```
4. Build production assets:
   ```bash
   npm run build
   ```
5. Commit:
   ```bash
   git add style.css
   git commit -m "Release v1.2.0"
   ```
6. Tag and push:
   ```bash
   git tag v1.2.0
   git push origin master
   git push origin v1.2.0
   ```
7. Create a GitHub Release from the tag with release notes grouped as:
   - **Features**
   - **Bug Fixes**
   - **Performance**
   - **Breaking Changes** (if any)
   - **Full Changelog**

The Plugin Update Checker reads the `Version:` header from `style.css` and compares it against the latest GitHub tag. WordPress admin will show an update notification when a new tag is pushed.

---

## Quick Reference

### All npm commands

```bash
npm run dev             # Development mode (watch CSS + blocks + BrowserSync)
npm run build           # Full production build
npm run build:css       # Tailwind CSS only
npm run build:blocks    # Webpack blocks only
npm run lint:php        # Check PHP standards
npm run lint:php:fix    # Auto-fix PHP standards
```

### All composer commands

```bash
composer run lint           # PHP CodeSniffer check
composer run lint:fix       # PHP CodeSniffer auto-fix
composer run lint:security  # Credential leak scan
composer run lint:run       # Full pipeline (security + fix + check)
```

### Key admin locations

| Task | WP Admin location |
|------|------------------|
| Edit block templates | Block Templates |
| Export/Import templates | Block Templates → Sync Tools |
| Edit navigation menus | Appearance → Menus |
| Export/Import nav menus | Tools → Nav Menu Sync |
| Theme settings | Appearance → Theme Options |
| Block patterns | Editor → Patterns inserter |

### Custom Tailwind colors

| Name | Hex |
|------|-----|
| `cream` | `#F9F5EE` |
| `gold` | `#B89352` |
| `gold-dark` | `#6B4502` |
| `dark-text` | `#25272B` |
| `footer-bg` | `#191919` |
| `check-green` | `#7CAA6D` |

### Custom Tailwind fonts

| Class | Font |
|-------|------|
| `font-sofia` | Sofia Sans |
| `font-poppins` | Poppins |
| `font-inter` | Inter |
