# Filament Content Builder

This package exposes a content builder field for Filament, together with a set of basic content blocks. You have the
full control of the blocks you want to use, and you can easily create your own blocks.

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

## Usage

The core functionality of this package is to provide a content builder field for Filament. You can use it in your
Filament resources as follows:

```php
<?php

use App\Models\Post;
use VanOns\FilamentContentBuilder\Fields\ContentBuilder;

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

