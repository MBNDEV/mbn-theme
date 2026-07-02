---
name: ai-blocks
description: Live registry of the mbn-ai- blocks and components built for the CURRENT project — the ONE mutable AI-state file. Read it before building to reuse existing blocks; update ONLY this file's table when a block/component is created or extended. Skill and command definitions stay locked (read-only) while a build/reset command runs — this registry is the only file such a command writes.
---

# AI Blocks — live registry

The single source of truth for **which** `mbn-ai-` blocks and components exist in the
**current** project. Everything about *how* to build them lives in the `components`
skill and the build commands (`/build-design`, `/build-components`) — those definitions
are **locked (read-only) while a command runs**. The **only** file a build or reset run
may write is this registry table.

## Lock rule (must-follow)

- While running any command (`/build-design`, `/build-components`, `scripts/reset.sh`,
  `/testing`), treat every file under `.claude/skills/**` and `.claude/commands/**` as
  **read-only** — do not edit a skill or command definition mid-run.
- The single exception is **this file** (`.claude/skills/ai-blocks/SKILL.md`): update its
  table as blocks/components are built. Nothing else in `.claude/` is written during a run.
- Changing a skill/command definition is a **separate, explicit** task the user asks for
  (then edit `.claude/**` and run `scripts/sync-ai-config.sh`) — never a side effect of a build.

## How to use

- **Before building:** read the table. If a block/component already exists, **reuse** it
  or **extend** it (add an attribute/variant) instead of creating a new one.
- **After building or extending:** add/update its row here — and nothing else.
- **On reset:** `scripts/reset.sh` clears this table back to `_none yet_`.

## Registry

Project: **Aqua Valley Pools** (Figma `nJ4SbpWkQ9qCF5faL8EucM`). Design system: Sora
(headings) + Manrope (body); schemes 1=#006DAB blue, 2=#082F49 navy, 3=#00A9C8 cyan,
4=#F8FCFF, 5=#EAF8FC, 6=#9BC7D7, 7=#536476, 8=#F4C542; radius 12px; container 1280px.
Media: logo color=921, logo white=922, chevron-down svg=923, menu svg=924, close svg=925,
photos 946–962 (hero 952, intro 962, banner 954, why 953/955/956, steps 951/957/961,
CTA person 950, dashboard 949, map+pins 947, trust logos 946/948/958/959/960), icons
svg 963–972 (arrow 963, clipboard 964, form 965–967, phone 968, star 969, svc 970–972),
CTA texture 980, FAQ chevron 982. Gravity Forms: Quote Request=1, Newsletter=2.
Menus: Header=24, Footer Menu=27, Footer Services=25. Home page = post 39.

| Block / component | Type (section \| component) | Purpose | Key attributes | Status |
|---|---|---|---|---|
| `mbn-ai-button` | component | Design-system CTA button (filled primary / outline tertiary) | `label`, `href`, `variant` (primary\|tertiary), `size` (sm\|md\|lg), `iconId`, `fullWidth`, `newTab` | built |
| `mbn-ai-icon` | component | Inline media-library SVG icon (inherits currentColor) | `iconId`, `size` (0 = CSS), `colorClass` | built |
| `mbn-ai-header` | section | Site header (template 45): logo, menu slot 1 w/ hover dropdown + mobile accordion, CTA buttons via mbn-ai-button, sticky | `menuSlot`, `logoMaxWidth`, `behavior` (sticky\|appear\|static), `showCtas`, `ctaPrimary*`, `ctaSecondary*`, `chevronIconId`, `menuIconId`, `closeIconId` | built |
| `mbn-ai-footer` | section | Site footer (template 46): navy panel, white logo + tagline + badge pill, 2 menu columns (slots 1/2 = menus 27/25) + contact column | `logoMaxWidth`, `tagline`, `badgeLabel`, `navHeading`/`navSlot`, `servicesHeading`/`servicesSlot`, `contactHeading`, `phoneLabel`, `phoneUrl`, `cities` | built |
| `mbn-ai-hero` | section | Hero: eyebrow + H1 + desc, CTA lg buttons + note, side photo w/ visit-status card | `eyebrow`, `title`, `description`, `ctaPrimary*`, `ctaSecondary*`, `note`, `imageId`(+`imageSize`), `lcp`, `cardEyebrow`, `cardTitle`, `cardIconId`, `items[{text}]` | built |
| `mbn-ai-trust-bar` | section | Dark #084c74 badge strip (logo boxes + label) | `items[{logoId,logo2Id,label,boxBg}]` | built |
| `mbn-ai-intro` | section | Rounded photo + blue→cyan gradient overlay, white H2 + copy, dark full-width button, tag pills | `title`, `body`, `ctaLabel/Url`, `imageId`(+size), `items[{text}]` | built |
| `mbn-ai-why-choose` | section | Title stack + bordered cards (last item = wide side-image card) | `eyebrow`, `title`, `description`, `items[{image(+Size),title,text}]` | built |
| `mbn-ai-services` | section | Radial-glow section, gradient cards: icon badge, copy, Explore-more button | `eyebrow`, `title`, `description`, `arrowIconId`, `items[{icon,title,text,btnLabel,btnUrl}]` | built |
| `mbn-ai-banner` | section | Full-bleed responsive bg photo + 3-layer scrim, white H2, cyan/gold lines, CTAs | `title`, `emphasisLine1/2`, `ctaPrimary*`, `ctaSecondary*`, `imageId`, `lcp` | built |
| `mbn-ai-steps` | section | Title stack + image-top step cards + centered CTA row | `eyebrow`, `title`, `description`, `ctaPrimary*`, `ctaSecondary*`, `items[{image(+Size),title,text}]` | built |
| `mbn-ai-portal` | section | H2 + paragraphs + emphasized primary CTA beside dashboard image | `title`, `body`, `ctaLabel/Url`, `imageId`(+size) | built |
| `mbn-ai-stats` | section | H3 + desc beside 2×2 gradient stat cards | `title`, `description`, `items[{value,label}]` | built |
| `mbn-ai-testimonials` | section | Title + review cards (5 stars, quote, colored initial avatar + name) | `title`, `subtitle`, `starIconId`, `items[{text,name,initial,color}]` | built |
| `mbn-ai-map` | section | Service areas: title stack, outlined city pills, note, map (Google Embed or static image) | `eyebrow`, `title`, `description`, `note`, `mapMode` (google\|image), `mapQuery`, `mapZoom`, `mapImageId`(+size, fallback), `items[{text}]` | built |
| `mbn-ai-cta` | section | Gradient CTA band + 10% texture, cut-out photo overflowing top, gold eyebrow, CTAs | `eyebrow`, `title`, `description`, `ctaPrimary*`, `ctaSecondary*`, `imageId`(+size), `textureId` | built |
| `mbn-ai-faq` | section | Accordion (open item navy w/ cyan Sora question) + Still-have-questions card; view.js toggle | `title`, `chevronIconId`, `items[{question,answer}]`, `ctaTitle`, `ctaText`, `ctaLabel/Url` | built |
| `mbn-ai-quote-form` | section | Title stack + info column (call card, icon cards, award tags) + navy Gravity Forms card | `eyebrow`, `title`, `description`, `callHeading/Sub`, `phoneLabel/Url/IconId`, `starIconId`, `formId`, `formNote`, `items[{icon,text}]`, `tags[{text}]` | built |
| `mbn-ai-subscribe` | section | Gradient newsletter band w/ inline Gravity Forms email signup | `title`, `subtitle`, `note`, `formId` | built |

`mbn-ai-button` variants: primary / tertiary / dark (#082F49) / deep (#084c74) / cyan
(scheme-3 + #084c74 border) / light (white outline); sizes sm / base / md / lg; extras
`emphasize` (extrabold), `fullWidth`, `newTab`, trailing `iconId`. Shared
`ItemsRepeater` now takes an `attribute` prop for multiple repeaters per block.

Every section block's heading is tag-selectable via the shared `TagControl`:
`titleTag` on all sections (hero default `h1`, others `h2`), `itemTitleTag` (`h3`) on
why-choose/services/steps, `headingTag` (`h2`) on the footer — rendered through
`mbn_tag()`. Google Maps API key lives in Appearance → MBN Theme → Integrations
(`maps_api_key`). Per-post JSON Import/Export is the `content-io` editor sidebar
(icon beside Remote Templates; imports into the CURRENT post via `mbn/v1` REST).
