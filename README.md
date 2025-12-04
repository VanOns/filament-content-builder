# Filament Content Builder

This package exposes a content builder field for Filament, together with a set of basic content blocks. You have the
full control of the blocks you want to use, and you can easily create your own blocks.

### Compatibility

For certain Filament versions, changes have to be made that render the package backwards incompatible with the previous version.
Please see the table below to determine which version you need.

| Version                                                                  | Filament |
|--------------------------------------------------------------------------|----------|
| v2 (current)                                                             | \>=4.0   |
| [v1](https://github.com/VanOns/filament-content-builder/tree/release/v1) | <4.0     |

**Please note:** the `main` branch will always be the latest major version.

## Installation

Because this package is not published to Packagist, you need to add it as a repository in your `composer.json` file:

```json
"repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/VanOns/filament-content-builder"
    }
]
```

Then, require the package:

```bash
composer require van-ons/filament-content-builder
```

### Customizing the config

The config file is where you define the blocks you want to be available in the content builder. To publish the config
file, run the following command:

```bash
php artisan vendor:publish --tag=filament-content-builder-config
```

### Customizing the views

If you want to customize the views for the default blocks, or the content blocks renderer component, you can publish them.
To do that, run the following command:

```bash
php artisan vendor:publish --tag=filament-content-builder-views
```

### Customizing the stubs

Stubs are used when creating new blocks. You can customize the stubs to your liking. You can publish the stubs by
running the following command:

```bash
php artisan vendor:publish --tag=filament-content-builder-stubs
```

### Customizing the language files

If you want to customize the language files, you can publish them by running the following command:

```bash
php artisan vendor:publish --tag=filament-content-builder-lang
```

## Usage - Content Blocks
Start off by creating a new `content-block`:
```bash
php artisan make:content-block
```

The core functionality of this package is to provide a content builder field for Filament. You can use it in your
Filament resources as follows:

```php
<?php

use App\Models\Post;
use VanOns\FilamentContentBuilder\Fields\ContentBlocksRenderer;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ContentBlocksRenderer::make('content')
                    ->label(__('Content'))
                    ->required(),
            ]);
    }
}
```

Then, in the Blade view, you can render the content blocks using the provided Blade component:

```blade
<x-filament-content-builder::block-renderer :blocks="$post->content" />
```

### Fixed blocks
Fixed blocks are blocks that are fixed to a resource. For example, a hero block.
This block is usually always on top of the page. With fixed blocks you can 'fix' the block fields to a resource, and then render it on a specific place on the page.

```php
use App\Models\Page;
use App\View\Blocks\Hero;
use VanOns\FilamentContentBuilder\Fields\ContentBlocksRenderer;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Other fields...

                ...Hero::getFields('data.hero-block')
            ]);
    }
}
```

Now, you can render it on your page like this:
```bladehtml
@extends('layouts.app')

@section('content')
<div class="...">
    <div class="...">
        <x-filament-content-builder::block block="hero" :data="$page->data['hero-block']" />
        <x-filament-content-builder::block-renderer :blocks="$page->content" />
    </div>
</div>
@endsection
```

### Block index
All blocks have the `blockIndex` property. This is a number that increments when a block is rendered.
For example, this can be used to add a delayed fade in animation to the blocks.

### Container & nested blocks
Container blocks are blocks that have other blocks inside of them. This package already contains a `Container` block, extend this, or use it as an example, to create your own `Container` block.

Inside the container's view, you can add the `nested` property to the `block-renderer` component.
```bladehtml
<x-filament-content-builder::block-renderer :blocks="$container->blocks" :nested="true" />
```
This will give all nested blocks the `nested` property. You can conditionally use this property, inside the block's view, to alter the rendering of the nested blocks.
```bladehtml
@if ($block->nested)
    Do something for the nested block...
@endif
```

## Usage - Templates
Start off by creating a new `template`:
```bash
php artisan make:template
```

This will create a new template class in the `app/View/Templates` directory. You can use this in your Filament resources, for example `PageResource`:

```php
<?php

use App\Models\Page;
use VanOns\FilamentContentBuilder\Fields\ContentBlocksRenderer;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ...app(TemplateService::class)->templateGroups(),

                Template::make('template')
                    ->required()
                    ->live(),
            ]);
    }
}
```
