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

### Deferred loading

On Filament versions that support `Schema::deferLoading()`, block schemas are loaded deferred by default. You can change the default via the `defer_loading` config value, or per block via the static `$deferLoading` property:

```php
class Hero extends Block
{
    public static ?bool $deferLoading = false;
}
```

When `$deferLoading` is `null` (the default), the `defer_loading` config value is used. Set the config value to `null` to keep Filament's default behavior.

On Filament versions without `Schema::deferLoading()`, the config default is silently ignored, but setting `$deferLoading` explicitly on a block throws a `RuntimeException`.

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

## Customizing blocks

### Title & icon

By default the block title is derived from the class name. Override `title()` and `icon()` to customize how the block appears in the builder:

```php
public static function title(): string
{
    return __('Hero');
}

public static function icon(): ?string
{
    return 'heroicon-o-photo';
}
```

### Dynamic labels

Set the static `$labelField` property to include a field's value in the block label, making blocks easier to tell apart in the builder:

```php
class Hero extends Block
{
    public static ?string $labelField = 'heading';
}
```

The label becomes the field's value (stripped of HTML, limited to 30 characters), followed by the block title — e.g. `Welcome to our site - Hero`. Nested fields are supported using dot notation, such as `content.heading`.

### Settings

Blocks can have a settings modal, opened via a cog button on the block. Define the settings fields by overriding `settingsSchema()`:

```php
use Filament\Forms\Components\Toggle;

public static function settingsSchema(): array
{
    return [
        Toggle::make('full_width')
            ->label(__('Full width')),
    ];
}
```

The button only shows when the schema is non-empty. Settings are stored in the block's data under the `settings` key, and are available in your view via `$block->getSettings()`:

```blade
@if ($block->getSettings()['full_width'] ?? false)
    {{-- full-width rendering --}}
@endif
```

Further customization:

```php
// Change the modal title (defaults to "Settings"):
public static function settingsTitle(): string;

// Change the button icon (defaults to `heroicon-o-cog-6-tooth`):
public static function settingsIcon(): string;

// Change the key the settings are stored under (defaults to `settings`):
public static function settingsPrefix(): string;

// Mutate the settings data before it is saved:
public static function mutateSettingsData(array $data): array;
```

### Custom view

Views are resolved from `resources/views/blocks/<kebab-case-type>.blade.php` by default. Override `view()` to change this:

```php
public static function view(): string
{
    return 'custom.path.hero';
}
```

### Plain text output

Override `toText()` to return a plain-text representation of the block, useful for things like SEO analysis:

```php
public function toText(): ?string
{
    return strip_tags($this->text);
}
```

## Copy & paste blocks

Each block has a **copy** button that copies its data as JSON to the clipboard. A **paste** button on the builder field lets you paste it — even across different resources or pages.

The paste modal validates that the pasted data is valid JSON, contains the required `type` and `data` keys, and that the block type is registered. Errors are shown inline.

### Disabling copy & paste

The feature is enabled by default. Disable it in the published config:

```php
// config/filament-content-builder.php
'copy_paste' => false,
```

## Block usage

Similar to WordPress, you can get an overview of how often and where each block is used.

Enable it in the published config and map each model to the column(s) that store block content:

```php
// config/filament-content-builder.php
'usage' => [
    'enabled' => true,

    'sources' => [
        // Single column
        \App\Models\Page::class => 'content',
        // Multiple columns
        \App\Models\Post::class => ['content', 'footer'],
        // With an explicit title attribute
        \App\Models\Product::class => ['columns' => ['description'], 'title_attribute' => 'name'],
    ],
],
```

Register the plugin in your panel provider:

```php
use VanOns\FilamentContentBuilder\FilamentContentBuilderPlugin;

$panel->plugins([
    FilamentContentBuilderPlugin::make()
        ->blockUsageNavigationGroup('other'),
]);
```

This adds a **Block usage** page to the panel, listing every registered block with its total usage count and the number of records it appears in. Nested blocks (e.g. inside containers) are counted as well. The **View usage** action shows the records a block is used in, with a link to the record's edit page when a Filament resource exists for the model.

The record label is resolved from the `title_attribute` if set, falling back to `title`, `name` or `label`.

### Per-panel configuration

The page is only registered on panels that have the plugin. Configure it per panel:

```php
FilamentContentBuilderPlugin::make()
    // Override the `usage.enabled` config value for this panel:
    ->blockUsage(false)
    ->blockUsageNavigationGroup('other'),
```

### Caching

Usage is computed by scanning all configured sources, and cached for 5 minutes by default:

```php
'usage' => [
    'cache' => 300, // seconds, null to disable
    // ...
],
```

The cache is scoped per panel and tenant. A **Refresh** header action appears on the page to recompute on demand.

### Authorization

Set a gate ability in the config to restrict access (hides the navigation item and blocks the page):

```php
'usage' => [
    'permission' => 'view block usage',
    // ...
],
```

Or authorize per panel with a closure, which takes precedence over the config permission:

```php
FilamentContentBuilderPlugin::make()
    ->authorizeBlockUsageUsing(fn () => auth()->user()->isAdmin()),
```

## Templates

Start by creating a new template:

```bash
php artisan make:template
```

This creates a template class in `app/View/Templates`. Use it in a Filament resource:

```php
use VanOns\FilamentContentBuilder\Fields\Template;
use VanOns\FilamentContentBuilder\Fields\TemplateFields;

TemplateFields::make(),

Template::make('template')
    ->required(),
```

`TemplateFields` renders the fields of the selected template, wherever you place it — the
`Template` select may sit in a sidebar, after it. Pass `->fieldset()` to wrap them in a fieldset
labeled with the template name, and `->templateField('layout')` when the select is named
something other than `template`.
