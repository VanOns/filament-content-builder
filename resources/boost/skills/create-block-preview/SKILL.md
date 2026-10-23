---
name: create-block-preview
description: 'Use when the user wants an SVG preview for one or more content blocks of van-ons/filament-content-builder — e.g. "create a preview for the hero block", "generate block previews", "add previews for all blocks", "which blocks are missing a preview". The preview is shown when hovering a block in the block picker. Covers finding blocks without a preview, studying how the block looks, drawing the SVG and verifying it.'
---

# Create a block preview

## When to use this skill

The project uses `van-ons/filament-content-builder` and the user wants a picker preview for a specific block, for all blocks, or for every block that is still missing one.

Skip this skill for: creating the block itself (use `create-content-block` first, then come back), or front-end screenshots unrelated to the block picker.

## How previews work

- A preview is an SVG file at `resources/block-previews/{kebab-type}.svg`, where `{kebab-type}` is `Str::kebab($block::type())` — the same name as the block's Blade view (`HeroBlock` → `hero-block.svg`).
- The block picker shows it in a tooltip next to the block when hovering, at a width of 320px. It is loaded as an `<img>`, so the SVG cannot use the page's CSS, fonts or scripts.
- Built-in package blocks ship their own previews. A file with the same name in `resources/block-previews/` overrides it.
- A block can point elsewhere by overriding `static preview(): ?string` (absolute path to the SVG, or `null` for no preview).
- Previews are cached by the browser and busted by the file's modification time, so a hard refresh is never needed.

## Workflow

### 1. Find the blocks to preview

For one block, resolve its path directly. For "all" or "missing", list every registered block and its status:

```bash
php artisan tinker --execute '
foreach (\VanOns\FilamentContentBuilder\Facade\FilamentContentBuilder::getBlocks() as $block) {
    echo str_pad($block, 50), $block::hasPreview() ? "  ok  " . $block::preview() : "  missing  resources/block-previews/" . \Illuminate\Support\Str::kebab($block::type()) . ".svg", PHP_EOL;
}'
```

Only regenerate existing previews when the user asks for it.

### 2. Study how the block looks

The preview should resemble the rendered block closely, not be a generic wireframe. Before drawing, gather:

- **Structure**: the block class (`schema()` fields, defaults, `settingsSchema()`) tells which elements exist and which variant is the default.
- **Markup**: the Blade view (`resources/views/blocks/{kebab-type}.blade.php`), or the JS component for Inertia/React/Vue front-ends (look up how the project maps block types to components).
- **Styling**: layout classes in that markup, and brand colors from the CSS theme / Tailwind config.
- **Real render**: the most reliable reference. If a page renders the block (a demo page with all blocks, or any page using it), screenshot it with a headless browser and draw from that. Otherwise use a design file the project's guidelines reference (e.g. Figma nodes per block).

Preview the default variant. When a block has a variant field that changes the layout drastically (e.g. media left/right), still draw one preview — the default.

### 3. Draw the SVG

Rules:

- `viewBox="0 0 400 H"` with width 400 and a height that matches the block's proportions on desktop (real height × 400 / page width, typically 80–320; zoom in a bit on very thin blocks, crop very tall ones). No `width`/`height` attributes needed.
- Start with a full-size background `<rect>` in the block's background color (white when transparent), so it reads well in light and dark mode.
- Abstract content, keep the layout: headings as dark rounded bars (a heading mixing regular and bold text gets a bar per part, the bold one darker and thicker), body text as thinner bars (`rx` = half the height) in the real text color, images as a tinted rect with a simple glyph matching the content (mountain/sun for scenery, head-and-shoulders for portraits), buttons as rounded rects in the brand color, icons as circles or simple shapes.
- Use the project's real colors, spacing ratios, corner radii and alignment. Repeated items (cards, slides, USPs) show the real count or 3–4 of them.
- Reproduce distinctive brand details faithfully — decorative underlines or brush strokes (copy their shape and color from the project's SVG assets), outlined vs. filled buttons, overlays/scrims, background shapes. These make a block recognizable; a generic stand-in (e.g. a straight bar for a brush underline) doesn't.
- No `<text>` (fonts don't load in an `<img>`), no `<script>`, no `<image>`, no external references, no embedded fonts.
- Hand-written, compact, grouped (`<g fill="…">` for shared fills). Aim for under 5 KB.

Example — a hero with title, subtitle, button and image on the right:

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 220" fill="none">
  <rect width="400" height="220" fill="#f8fafc"/>
  <rect x="28" y="56" width="160" height="16" rx="4" fill="#0f172a"/>
  <rect x="28" y="80" width="120" height="16" rx="4" fill="#0f172a"/>
  <g fill="#94a3b8">
    <rect x="28" y="110" width="150" height="7" rx="3.5"/>
    <rect x="28" y="124" width="130" height="7" rx="3.5"/>
  </g>
  <rect x="28" y="148" width="84" height="26" rx="13" fill="#2563eb"/>
  <rect x="212" y="28" width="160" height="164" rx="12" fill="#cbd5e1"/>
  <path d="m244 160 32-40 22 26 14-16 28 30z" fill="#94a3b8"/>
  <circle cx="320" cy="72" r="12" fill="#94a3b8"/>
</svg>
```

Write it to the path from step 1. For a block that overrides `preview()`, write to the path it returns.

### 4. Verify

- Render the SVG to PNG and look at it. Prefer a browser, which renders it exactly like the picker does — e.g. headless Chrome (`google-chrome`, `chromium`, or `/Applications/Google Chrome.app/Contents/MacOS/Google Chrome` on macOS): `--headless --screenshot=/tmp/hero-block.png --window-size=800,400 file:///abs/path/hero-block.svg`. `rsvg-convert` works too; ImageMagick's built-in renderer drops gradients. Compare against the real block and adjust.
- Confirm it is picked up: `$block::hasPreview()` returns `true` (rerun the command from step 1).
- In the admin panel, hover the block in the "Add to …" picker of a content builder field.

## Anti-patterns

- Don't put previews in `public/` or reference them by URL — they are served by the package from `resources/block-previews/`.
- Don't use a screenshot or raster image as the preview — keep it a small, hand-written SVG.
- Don't override `preview()` just to use the default location; only override it for a different path.
- Don't generate previews unasked for blocks the user didn't mention — offer it instead.
