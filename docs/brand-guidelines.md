# DUYOG '26 Brand Guidelines v1.0

> Last updated: 2026-10-14
> Status: Draft, documented from the existing campaign artwork and `public/css/duyog-theme.css`. Items under "To confirm" need a decision from the South Mindanao District team.

DUYOG '26 is the South Mindanao District Youth Camp of the Alliance Youth of the Philippines (CAMACOP). The 2026 theme is **ROOTED**: "Growing deeper. Reaching higher. Living for Christ." (Colossians 2:6-7). The brand is a night sky in deep purple, lit by warm gold and glowing lilac.

## Quick Reference

| Element | Value |
|---------|-------|
| Primary Color | #F1D29A (gold) on #090510 (night) |
| Secondary Color | #B344DC (magenta), #DFB4F3 (lilac) |
| Display Font | Cinzel Decorative |
| Heading / UI Font | Quicksand |
| Body Font | Public Sans |
| Voice | Reverent, Warm, Clear, Youthful |

The CSS custom properties in `public/css/duyog-theme.css` are the source of truth for the web app. Use the variable, not the raw hex, in stylesheets.

---

## 1. Color Palette

The brand is dark-only. There is no light theme.

### Primary Colors

| Name | Hex | CSS variable | Usage |
|------|-----|--------------|-------|
| Gold | #F1D29A | `--duyog-gold` | Primary buttons, wordmark, eyebrows, labels, rank numbers |
| Gold Deep | #B98B46 | `--duyog-gold-deep` | Gradient end of gold buttons, borders on gold elements |
| Cream | #FBF1DC | `--duyog-cream` | Headings and the theme title |

### Secondary Colors

| Name | Hex | CSS variable | Usage |
|------|-----|--------------|-------|
| Magenta | #B344DC | `--duyog-magenta` | Glows, gradients, decorative accents only |
| Lilac | #DFB4F3 | `--duyog-lilac` | Links, verse reference, secondary accents, focus-adjacent borders |
| Purple | #391054 | `--duyog-purple` | Tinted fills, chips, background gradient stops |

### Neutral Palette

| Name | Hex | CSS variable | Usage |
|------|-----|--------------|-------|
| Night | #090510 | `--duyog-night` | Page background, browser theme colour |
| Plum | #1B0B31 | `--duyog-plum` | Background gradient, deep panels |
| Surface | #170E23 | `--duyog-surface` | Cards and panels |
| Surface Raised | #221531 | `--duyog-surface-raised` | Hovered or stacked panels |
| Copy | #E7DFED | `--duyog-copy` | Body text |
| Copy Muted | #B7AABD | `--duyog-copy-muted` | Captions, helper text |
| White | #FFFAFF | `--duyog-white` | Text inside cards and inputs |
| Line | rgba(220,193,238,.16) | `--duyog-line` | Borders and dividers |

### Semantic Colors

These are text colours on a 15% tint of the same hue, as used by badges in the staff workspace.

| State | Hex | Usage |
|-------|-----|-------|
| Success | #B6F5A0 | Confirmed, checked in, "Happening now" |
| Warning | #F1D29A | Pending, needs review (shares the gold) |
| Error | #FF9DBA | Errors, destructive actions |
| Info | #DFB4F3 | Informational badges (shares the lilac) |

### Accessibility

Contrast ratios measured against Surface (#170E23):

| Pair | Ratio | Result |
|------|-------|--------|
| Cream headings | 16.7:1 | AAA |
| Copy body text | 14.4:1 | AAA |
| Copy Muted | 8.5:1 | AAA |
| Gold | 12.8:1 | AAA |
| Lilac | 10.6:1 | AAA |
| Dark text (#210B2A) on a gold button | 12.6:1 | AAA |
| Magenta | 4.3:1 | Fails AA for body text |

- Never set text in Magenta (#B344DC) or the Bootstrap primary purple (#A752D0). Use them for glows, fills and large decorative shapes only.
- Do not signal state by colour alone. Pair it with an icon or a word, as the leaderboard "Tied" tag does.
- Keyboard focus is a 3px gold outline (#E7C78F) with a 3px offset. Do not remove it.

---

## 2. Typography

### Font Stack

```css
--font-display: 'Cinzel Decorative', Georgia, serif;
--font-heading: 'Quicksand', sans-serif;
--font-body: 'Public Sans', sans-serif;
```

| Role | Font | Weights | Use for |
|------|------|---------|---------|
| Display | Cinzel Decorative | 700, 900 | The theme title ("ROOTED"), the "DUYOG '26" wordmark, large numerals such as the countdown and step numbers |
| Heading / UI | Quicksand | 500, 600, 700 | Section headings, eyebrows, buttons, labels, scores |
| Body | Public Sans | 300-700 | Paragraphs, tables, forms |

Cinzel Decorative is a display face. Never use it for sentences, body text or anything under about 18px.

### Type Scale

| Element | Size (Desktop) | Size (Mobile) | Font | Weight | Line Height |
|---------|----------------|---------------|------|--------|-------------|
| Theme title | up to 100px | from 46px | Cinzel Decorative | 700 | 1.08 |
| Wordmark | 22-32px | 22px | Cinzel Decorative | 800 | 1 |
| H2 section heading | 36px | 24px | Quicksand | 700 | 1.2 |
| H3 card heading | 18px | 18px | Quicksand | 700 | 1.3 |
| Body | 16px | 16px | Public Sans | 400 | 1.6 |
| Lead / tagline | 17-21px | 17px | Public Sans | 400 | 1.55 |
| Eyebrow | 10-12px, uppercase, .16em tracking | same | Quicksand | 700 | 1.4 |
| Caption | 12px minimum | 12px | Public Sans | 400 | 1.4 |

- Eyebrows are the only text allowed below 12px, and only because they are uppercase, bold and widely tracked.
- Use tabular numerals (`font-variant-numeric: tabular-nums`) for scores, fees and the countdown.

### Font Loading

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&family=Quicksand:wght@500;600;700&family=Cinzel+Decorative:wght@700;900&display=swap" rel="stylesheet">
```

---

## 3. Logo Usage

### Variants

| Variant | File | Use Case |
|---------|------|----------|
| DUYOG emblem (bird mark on night sky) | `public/images/duyog-emblem.jpg` | Nav bar, login card, avatars. Shown cropped to a circle. |
| "DUYOG '26" lettering (campaign art) | part of `public/images/duyog-cover.png` | Hero backgrounds and posters only |
| "DUYOG '26" wordmark (web) | text set in Cinzel Decorative, gold | Nav bar, headers, Camp IDs |
| South Mindanao District logo | `public/images/district-logo.png` | Footer, official documents |
| CAMACOP logo | `public/images/camacop-logo.png` | Footer, official documents |

### Clear Space

Keep clear space around the emblem equal to at least a quarter of its diameter. Keep the district and CAMACOP logos separated by a thin gold divider, as in the site footer, and never let them touch.

### Minimum Size

| Context | Minimum |
|---------|---------|
| Emblem, digital | 28px diameter |
| Wordmark, digital | 14px text size |
| District and CAMACOP logos, digital | 56px tall |

### Don'ts

- Don't recolour, redraw or retype the campaign lettering. On the web, use the text wordmark instead.
- Don't place the emblem on a light background. It has no transparent version and carries its own night-sky backdrop.
- Don't recolour, stretch or add glows to the district and CAMACOP logos. They belong to their organisations.
- Don't place any logo over the busiest part of the cover artwork without a dark panel behind it.

---

## 4. Voice & Tone

### Brand Personality

| Trait | Description |
|-------|-------------|
| **Reverent** | Christ-centred and anchored in Scripture, without preaching in interface text |
| **Warm** | Welcoming to youth, pastors and church leaders alike |
| **Clear** | Plain words and short sentences, since many readers are on a phone at camp |
| **Youthful** | Energetic and hopeful, never childish or slangy |

### Voice Chart

| Trait | We Are | We Are Not |
|-------|--------|------------|
| Reverent | Sincere, Scripture-rooted | Preachy, heavy with jargon |
| Warm | Encouraging, inclusive | Gushing, overly casual |
| Clear | Direct, specific, practical | Vague, bureaucratic |
| Youthful | Lively, hopeful | Childish, trend-chasing |

### Tone by Context

| Context | Tone | Example |
|---------|------|---------|
| Campaign and hero | Poetic, short phrases | "Growing deeper. Reaching higher. Living for Christ." |
| Instructions | Plain and step by step | "Get the roster template." |
| Status | Factual, brief | "Happening now" / "Registration opens soon" |
| Empty states | Reassuring, says what happens next | "No contest points yet. Standings appear here as soon as the first event is scored." |
| Errors | Calm, tells the person how to fix it | "No circuits match your search." |
| Staff workspace | Neutral and efficient | "Mark as printed" |

### Terminology

Use these terms exactly, with this capitalisation.

| Term | Notes |
|------|-------|
| DUYOG '26 | All caps, apostrophe before 26 |
| ROOTED | All caps when it is the theme title |
| Circuit, Local Church | Capitalised when naming the organisational unit |
| Youth, Pastor | Capitalised as registration types |
| Camp ID | Not "badge" or "pass" in staff screens |
| Pre-registration, On-site registration | Hyphenated as shown |
| Fees | Peso sign, no decimals: ₱350 |

### Prohibited Terms

| Avoid | Reason |
|-------|--------|
| Hurry / Last chance | No manufactured urgency; state the date instead |
| Guys | Not inclusive of everyone attending |
| Users | Say youth, leaders or staff |
| Emoji as icons | Use the Boxicons set so icons stay consistent |

---

## 5. Imagery Guidelines

### Campaign Artwork

- **Setting:** a starry night sky in deep purple, with soft bokeh light and a horizon glow.
- **Lettering:** iridescent chrome letterforms with a gold-to-lilac sheen and an outer glow.
- **Motif:** root and branch tendrils radiating from light, tied to the ROOTED theme.
- **Pattern:** the geometric weave pattern in `public/images/duyog-header-pattern.png` and the edges of the cover art. Use it as a border or backdrop, never behind body text at full strength.

On the website the cover art sits behind content at reduced opacity (about 34%) so text stays readable. Keep it that quiet wherever text overlaps it.

### Available Assets

| File | Content |
|------|---------|
| `duyog-cover.png` | Wide cover art with pattern edges |
| `duyog-rooted-poster.jpg` | Main theme poster with dates, venue and fees |
| `duyog-speaker-poster.jpg` | Speaker poster |
| `duyog-countdown.jpg` | "Days to go" social graphic |
| `duyog-header-pattern.png` | Weave pattern strip |

### Photography

No photography style is defined yet. See "To confirm".

### Icons

- Set: Boxicons (`bx`), regular outline style.
- Colour: gold for accents, lilac or muted copy elsewhere.
- Decorative icons carry `aria-hidden="true"`; icon-only buttons need a text label.

---

## 6. Design Components

### Buttons

| Type | Background | Text | Border Radius |
|------|------------|------|---------------|
| Primary | Gold gradient #F1D29A to #DBAC6F | #210B2A | 0.65rem |
| Ghost | rgba(255,255,255,.04) with a lilac border | #FBF1DC | 0.65rem |
| Outline (staff) | Transparent with a #8551A6 border | #DFB4F3 | 0.65rem |

- One primary (gold) button per section. Everything else is ghost or outline.
- Minimum touch target is 44px high; landing-page buttons are 48px.

### Spacing Scale

| Token | Value | Usage |
|-------|-------|-------|
| xs | 4px | Icon gaps |
| sm | 8px | Compact elements |
| md | 16px | Standard spacing |
| lg | 24px | Card padding |
| xl | 40px | Between related blocks |
| 2xl | 72px | Between page sections |

### Border Radius

| Element | Radius | CSS variable |
|---------|--------|--------------|
| Buttons, inputs | 0.65rem | `--duyog-radius-sm` |
| Small cards, alerts | 1rem | `--duyog-radius-md` |
| Cards, panels | 1.25rem | `--duyog-radius-lg` |
| Hero camp pass | 1.5rem | none |
| Pills, tags | 999px | none |

### Motion

- Hover and press feedback: 160ms. Card lift and larger transitions: 280ms.
- Animate `transform` and `opacity` only.
- Everything must respect `prefers-reduced-motion`; the stylesheet already disables transitions for it.

---

## AI Image Generation

### Base Prompt Template

Always prepend to image generation prompts:

```
Deep purple night sky filled with small stars and soft bokeh light, background from near-black #090510 to plum #391054 with a magenta #B344DC horizon glow. Iridescent chrome elements with a warm gold #F1D29A to lilac #DFB4F3 sheen and a soft outer glow. Thin luminous root and branch tendrils. Reverent, hopeful, youthful atmosphere.
```

### Style Keywords

| Category | Keywords |
|----------|----------|
| **Lighting** | glowing, backlit, soft bloom, starlight |
| **Mood** | reverent, hopeful, wonder, youthful |
| **Composition** | centred subject, generous dark space, horizon at the base |
| **Treatment** | iridescent chrome, high contrast, fine film grain |
| **Aesthetic** | celestial, modern, ethereal |

### Visual Mood Descriptors

- A clear night sky just before dawn
- Light breaking outward from a single centre
- Roots and branches drawn in light

### Visual Don'ts

| Avoid | Reason |
|-------|--------|
| Light or white backgrounds | The brand is dark-only |
| Colours outside purple, magenta, lilac and gold | Breaks the palette |
| Generated text or lettering | Never imitate the DUYOG '26 or ROOTED lettering; use the official art |
| Generated people presented as real attendees | Misleading; use real camp photos with consent |
| Generated versions of the district or CAMACOP logos | Official marks must not be redrawn |

### Example Prompts

**Hero Banner**:
```
Wide deep purple night sky with scattered stars and soft bokeh, magenta glow along the bottom horizon over dark mountain silhouettes, thin glowing root tendrils rising from the centre, large empty area on the left for text, no lettering.
```

**Social Media Post**:
```
Square deep purple starry sky, a burst of lilac-white light at the centre with glowing branch tendrils radiating outward, warm gold highlights, generous empty space in the middle for a number or headline, no lettering.
```

---

## To confirm

These could not be determined from the existing files and need a decision from the district team.

| Item | Question |
|------|----------|
| Vector logos | Are there SVG or transparent PNG versions of the emblem and the DUYOG '26 lettering? Only a JPG with a background is in the project. |
| Print specifications | Minimum print sizes, CMYK values and paper stock for posters and Camp IDs. |
| Photography | Style and consent rules for photos of youth, most of whom are minors. |
| Languages | Whether public text should also appear in Cebuano or Filipino, and the tone to use. |
| Year-to-year reuse | Which elements are permanent DUYOG identity and which belong only to the 2026 ROOTED theme. |

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-10-14 | Initial guidelines, documented from the existing theme CSS and campaign artwork |
