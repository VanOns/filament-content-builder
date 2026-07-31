# Usage

## Content blocks

Start by creating a new content block:

```bash
php artisan make:content-block
```

Add the `ContentBlocksRenderer` field to your Filament resource:

```php
use VanOns\FilamentContentBuilder\Fields\ContentBlocksRenderer;

ContentBlocksRenderer::make('content')
    ->label(__('Content'))
    ->required(),
```

Render the blocks in your Blade view:

```blade
<x-filament-content-builder::block-renderer :blocks="$post->content" />
```

### Block index

All blocks have a `blockIndex` property — a number that increments with each rendered block. Use it for things like staggered animations.

### Fixed blocks

Fixed blocks are blocks that are fixed to a resource. For example, a hero block.
This block is usually always on top of the page. With fixed blocks you can 'fix' the block fields to a resource, and then render it on a specific place on the page.
You will need to wrap the block schema in a wrapper component, such as a section in the example below, to specify the statePath where the data for the fixed blocks will be stored in your model.

```php
use App\View\Blocks\Hero;

Section::make()->statePath('data.hero-block')->schema([
    ...Hero::schema(),
])
```

Render a fixed block in your Blade view:

```blade
<x-filament-content-builder::block block="hero" :data="$page->data['hero-block']" />
```

### Container & nested blocks

Container blocks are blocks that have other blocks inside of them. This package already contains a `Container` block, extend this, or use it as an example, to create your own `Container` block.

Pass `nested="true"` to the block renderer inside a container's view:

```blade
<x-filament-content-builder::block-renderer :blocks="$container->blocks" :nested="true" />
```

Nested blocks receive the `nested` property, which you can use to conditionally alter rendering:

```blade
@if ($block->nested)
    {{-- nested-specific output --}}
@endif
```

## Templates

Start by creating a new template:

```bash
php artisan make:template
```

This creates a template class in `app/View/Templates`. Use it in a Filament resource:

```php
use VanOns\FilamentContentBuilder\Fields\Template;
use VanOns\FilamentContentBuilder\Templates\TemplateService;

...app(TemplateService::class)->templateGroups(),

Template::make('template')
    ->required()
    ->live(),
```
