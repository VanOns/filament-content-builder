---
name: create-content-block
description: Use when the user wants to add a new content block to a project that uses van-ons/filament-content-builder — e.g. "create a hero block", "add a testimonials block", "scaffold a new content block", "add a block to the content builder". Covers the full workflow: scaffold via artisan, define the block schema, wire up the Blade view, optional settings modal, dynamic label, container-block registration, and front-end rendering.
---

# Create a content block

## When to use this skill

The host project uses `van-ons/filament-content-builder` and the user wants to add a new block usable inside `ContentBlocksRenderer`. Triggers include "create a … block", "scaffold a content block", "add a block to the builder", "make a custom block".

Skip this skill for: editing an existing block (just edit the file), adding a Filament builder block unrelated to this package, or template work (use `create-template`).

## Workflow

### 1. Scaffold

Always start with the artisan generator — it creates both files, publishes the config if missing, and appends the class to `blocks`.

```bash
php artisan make:content-block Hero
```

Result:

- `app/View/Blocks/Hero.php` — block class extending `VanOns\FilamentContentBuilder\Blocks\Contracts\Block`
- `resources/views/blocks/hero.blade.php` — block view
- Class added to `config/filament-content-builder.php` under `blocks`

The command refuses PHP reserved words and duplicate `type()` values. Names are studly-cased automatically (`hero_section` → `HeroSection`).

### 2. Define the schema

Edit `app/View/Blocks/{Name}.php`. Declare a public typed property for each field — they are auto-hydrated from stored `data`.

```php
namespace App\View\Blocks;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class Hero extends Block
{
    public string $title;
    public ?string $subtitle = null;
    public string $alignment = 'left';

    public static ?string $labelField = 'title';

    public static function title(): string
    {
        return __('blocks.hero');
    }

    public static function icon(): ?string
    {
        return 'heroicon-o-star';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('title')->required(),
            Textarea::make('subtitle'),
            Select::make('alignment')
                ->options(['left' => 'Left', 'center' => 'Center', 'right' => 'Right'])
                ->default('left')
                ->required(),
        ];
    }
}
```

### 3. Decision table — block options

| Want to… | Override |
|---|---|
| Show a heroicon next to the block in the picker | `static icon(): ?string` |
| Use a non-default header label in the builder list | Set `public static ?string $labelField` to a state key, or override `static title()` |
| Customize the builder item header beyond the field value | Override `static getLabel($state): ?string` (from `HasDynamicLabel`) |
| Force a custom type slug (default = class basename) | `static type(): string` |
| Use a non-default Blade view | `static view(): string` (default: `blocks.{kebab-type}` for app blocks, `filament-content-builder::blocks.{kebab-type}` for package blocks) |
| Add a per-block settings modal (cog icon) | `static settingsSchema(): array`; access via `$block->getSettings()` in the view |
| Mutate settings before save | `static mutateSettingsData(array $data): array` |
| Customize the settings storage key (default `settings`) | `static settingsPrefix(): string` |
| Customize what stored data looks like (e.g. cast/transform) | `parseData(): array` |
| Provide plain text for SEO / search indexing | `toText(): ?string` |
| Allow nesting inside the built-in `Container` block | Add the class to `container-blocks` in config |

### 4. Build the Blade view

Edit `resources/views/blocks/{name}.blade.php`. The block instance is exposed as `$block`. The default stub already declares the docblock — extend it.

```blade
@php
    /** @var \App\View\Blocks\Hero $block */
@endphp

<section class="hero hero--{{ $block->alignment }}">
    <h1>{{ $block->title }}</h1>

    @if($block->subtitle)
        <p>{{ $block->subtitle }}</p>
    @endif
</section>
```

For nested-aware rendering inside a container:

```blade
@if($block->nested)
    {{-- compact variant --}}
@else
    {{-- full variant --}}
@endif
```

### 5. Render on the front-end

If the block lives in a content array on the model:

```blade
<x-filament-content-builder::block-renderer :blocks="$post->content" />
```

If used as a fixed block (data stored at a known path on the model — see README "Fixed blocks"):

```blade
<x-filament-content-builder::block block="hero" :data="$page->data['hero-block']" />
```

### 6. (Optional) Settings modal

Add a settings schema to expose a cog modal on the builder item. Settings storage defaults to `data.settings.*`.

```php
public static function settingsSchema(): array
{
    return [
        Select::make('background')
            ->options(['light' => 'Light', 'dark' => 'Dark']),
        Toggle::make('full_width'),
    ];
}
```

Read in the view:

```blade
@php $settings = $block->getSettings(); @endphp
<section class="bg-{{ $settings['background'] ?? 'light' }}">…</section>
```

### 7. (Optional) Allow nesting in the Container block

```php
// config/filament-content-builder.php
'container-blocks' => [
    \VanOns\FilamentContentBuilder\Blocks\TextBlock::class,
    \App\View\Blocks\Hero::class,
],
```

### 8. (Optional) Restrict to specific forms

`ContentBlocksRenderer` exposes blocks via the global config by default. Override per-form:

```php
ContentBlocksRenderer::make('content')
    ->contentBlocks([\App\View\Blocks\Hero::class, \VanOns\FilamentContentBuilder\Blocks\TextBlock::class]);
```

A closure works too — useful for permission/context-based filtering.

## Common follow-ups

- **Translations**: built-in lang files use `filament-content-builder-lang::fields.*`. For project-specific labels, add keys to `lang/{locale}/blocks.php` and reference with `__('blocks.your_key')`.
- **Custom stubs**: run `php artisan vendor:publish --tag=filament-content-builder-stubs` to override `stubs/block.stub` and `stubs/block-view.stub`. The placeholders are `{{ class }}` and `{{ title }}`.
- **Container blocks**: extend `VanOns\FilamentContentBuilder\Blocks\Container` for a custom container; its `schema()` already wires `ContentBlocksRenderer` against `getContainerBlocks()`.

## Anti-patterns

- Don't instantiate blocks with `new`. Use `Block::make($data)` or the facade `FilamentContentBuilder::getBlock($type, $data)` so the container resolves the class.
- Don't manually edit `config/filament-content-builder.php` to register a generated block — `make:content-block` already does it. Editing it manually risks duplicate or mis-namespaced entries.
- Don't use the deprecated `CanBeFixed::getFields()` for fixed blocks. Wrap `Block::schema()` in a Filament `Section`/`Group` with `->statePath('data.hero-block')` instead.
- Don't iterate `$blocks` manually in Blade — use `<x-filament-content-builder::block-renderer>`. It already skips entries with missing `type`/`data` keys.
- Don't reuse a block `type()` across classes. Registration order matters and `getBlockClass()` returns the first match.
