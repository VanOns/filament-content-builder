---
name: filament-content-builder-development
description: Build and work with Filament Content Builder features, including content blocks, fixed blocks, container blocks, and template management.
---

# Filament Content Builder Development

## When to use this skill

Use this skill when working with `van-ons/filament-content-builder`, a Filament package that provides a drag-and-drop content builder field, pre-built and custom content blocks, fixed/hero blocks, nested container blocks, and a template system for page layouts.

## Installation

This package is not on Packagist. Add it as a VCS repository in `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/VanOns/filament-content-builder"
    }
]
```

Then install:

```bash
composer require van-ons/filament-content-builder
```

## Features

- **ContentBlocksRenderer field**: Filament form field that renders the drag-and-drop content builder in a resource form.
- **Built-in blocks**: `TextBlock`, `CtaBlock`, `ListBlock`, `EmbedBlock` (YouTube/Vimeo), and `Container` (nested blocks).
- **Custom blocks**: Generate new blocks with `php artisan make:content-block`; each block has a PHP class extending `Block` and a Blade view.
- **Fixed blocks**: Bind specific blocks (e.g., hero) to a resource's `statePath` rather than the content array.
- **Container / nested blocks**: Blocks that hold other blocks; pass `:nested="true"` to the renderer inside a container view.
- **Block index**: Every rendered block exposes `$blockIndex` (auto-incrementing integer) for animations or conditional styling.
- **Template system**: Create page-layout templates with `php artisan make:template`; use `TemplateService` and `Template` field in resources.

## File Structure

```
app/
└── View/
    ├── Blocks/          # Custom block classes (extend Block contract)
    │   └── MyBlock.php
    └── Templates/       # Template classes (created by make:template)
        └── MyTemplate.php

resources/views/
└── blocks/              # Custom block Blade views
    └── my-block.blade.php
```

Package source (for reference):

```
src/
├── Blocks/Contracts/Block.php    # Abstract base class for all blocks
├── Fields/ContentBlocksRenderer.php
├── Fields/Template.php
├── Templates/TemplateService.php
└── Console/
    ├── MakeContentBlockCommand.php
    └── MakeTemplateCommand.php
```

## Usage

### Adding the content builder to a Filament resource

```php
use VanOns\FilamentContentBuilder\Fields\ContentBlocksRenderer;

class PostResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            ContentBlocksRenderer::make('content')
                ->label(__('Content'))
                ->required(),
        ]);
    }
}
```

### Rendering blocks in a Blade view

```blade
<x-filament-content-builder::block-renderer :blocks="$post->content" />
```

### Fixed blocks (e.g., hero)

Wrap the block schema in a Filament `Section` with an explicit `statePath`:

```php
use App\View\Blocks\Hero;

Section::make()->statePath('data.hero-block')->schema([
    ...Hero::schema(),
]),
```

Render in Blade:

```blade
<x-filament-content-builder::block block="hero" :data="$page->data['hero-block']" />
<x-filament-content-builder::block-renderer :blocks="$page->content" />
```

### Container / nested blocks

Inside a container block's Blade view, pass `:nested="true"`:

```blade
<x-filament-content-builder::block-renderer :blocks="$container->blocks" :nested="true" />
```

Inside a nested block's view, check `$block->nested` for conditional rendering:

```blade
@if ($block->nested)
    {{-- nested-specific markup --}}
@endif
```

### Using the block index

```blade
<div style="animation-delay: {{ $block->blockIndex * 100 }}ms">
    {{-- block content --}}
</div>
```

### Template system

```php
use VanOns\FilamentContentBuilder\Fields\Template;
use VanOns\FilamentContentBuilder\Templates\TemplateService;

public static function form(Form $form): Form
{
    return $form->schema([
        ...app(TemplateService::class)->templateGroups(),
        Template::make('template')->required()->live(),
    ]);
}
```

### Creating a custom block class

```php
namespace App\View\Blocks;

use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class MyBlock extends Block
{
    public string $title = '';

    public static function schema(): array
    {
        return [
            TextInput::make('title')->required(),
        ];
    }
}
```

The corresponding view resolves automatically to `resources/views/blocks/my-block.blade.php`.

## Artisan Commands

- `php artisan make:content-block` — Scaffold a new block class and its Blade view.
- `php artisan make:template` — Create a new template class in `app/View/Templates`.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=filament-content-builder-config
```

The config (`config/filament-content-builder.php`) controls:

- `blocks` — list of block classes available in the content builder.
- `container-blocks` — block classes allowed inside container blocks.
- `embeddable_services` — services for `EmbedBlock` (defaults: `YouTube`, `Vimeo`).
- `template_directories` — directories scanned for template classes (default: `app/View/Templates`).

Publish views, stubs, or language files if customisation is needed:

```bash
php artisan vendor:publish --tag=filament-content-builder-views
php artisan vendor:publish --tag=filament-content-builder-stubs
php artisan vendor:publish --tag=filament-content-builder-lang
```

## Compatibility

| Package version | Filament version |
|-----------------|-----------------|
| v2 (current)    | 4.x, 5.x (>=4.0) |
| v1              | <4.0            |

## Common Pitfalls

- The package is **not on Packagist**; always add the VCS repository entry to `composer.json` before `composer require`.
- Block class names must be unique — `type()` defaults to `class_basename`, so two blocks with the same class name in different namespaces will collide.
- Fixed block `statePath` must match the key used to retrieve data in the Blade view (e.g., `data.hero-block` ↔ `$page->data['hero-block']`).
- Container blocks must use their own `container-blocks` config key (not `blocks`) to control which child blocks are available.
- Cast the model's content column to `array` (or `json`) in Eloquent so stored JSON is automatically decoded.
