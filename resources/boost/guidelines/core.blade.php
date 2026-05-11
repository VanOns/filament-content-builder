# Filament Content Builder

`van-ons/filament-content-builder` adds a Filament builder field for composing pages from reusable content blocks, plus an optional template system. Blocks are PHP classes (Filament schema + Blade view) registered in `config/filament-content-builder.php`. Block instance data is stored as JSON; rendering on the front-end uses Blade components shipped by the package.

Requires `filament/filament` ^4.0 or ^5.0. Auto-registers via `extra.laravel.providers`.

## Setup

After `composer require`, publish whichever resources need overriding:

@verbatim
<code-snippet name="Publish package resources" lang="bash">
php artisan vendor:publish --tag=filament-content-builder-config
php artisan vendor:publish --tag=filament-content-builder-views
php artisan vendor:publish --tag=filament-content-builder-stubs
php artisan vendor:publish --tag=filament-content-builder-lang
</code-snippet>
@endverbatim

Default config registers five blocks (`CtaBlock`, `ListBlock`, `TextBlock`, `EmbedBlock`, `Container`). Add or remove entries in `config/filament-content-builder.php` under `blocks`. The `container-blocks` array controls which blocks are pickable inside the `Container` block.

## Artisan commands

| Command | Generates | Notes |
|---|---|---|
| `php artisan make:content-block {name}` | `app/View/Blocks/{Name}.php`, `resources/views/blocks/{name}.blade.php` | Publishes config if missing, then appends the new class to the `blocks` array. Fails on PHP reserved words and duplicate types. |
| `php artisan make:template {name}` | `app/View/Templates/{Name}.php` | Plain `GeneratorCommand` — does not touch config. |

## Form usage

Add `ContentBlocksRenderer` to a Filament resource. It already wires up the per-item settings modal, collapsible state, and the configured blocks.

@verbatim
<code-snippet name="ContentBlocksRenderer in a resource" lang="php">
use VanOns\FilamentContentBuilder\Fields\ContentBlocksRenderer;

public static function form(Form $form): Form
{
    return $form->schema([
        ContentBlocksRenderer::make('content')
            ->label(__('Content'))
            ->required(),
    ]);
}
</code-snippet>
@endverbatim

Restrict to a subset of blocks for one form via `->contentBlocks([...])` (accepts class-strings or a closure returning them).

## Front-end rendering

Render a stored content array with the `block-renderer` component. Render a single fixed block (data lives elsewhere on the model) with the `block` component.

@verbatim
<code-snippet name="Renderer Blade components" lang="blade">
{{-- Render an array of stored blocks --}}
<x-filament-content-builder::block-renderer :blocks="$post->content" />

{{-- Render a single block by type with arbitrary data --}}
<x-filament-content-builder::block block="hero" :data="$page->data['hero-block']" />

{{-- Inside a Container block view: forward `nested` so children can adapt --}}
<x-filament-content-builder::block-renderer :blocks="$block->content" :nested="true" />
</code-snippet>
@endverbatim

## Block contract

Custom blocks extend `VanOns\FilamentContentBuilder\Blocks\Contracts\Block`. Public typed properties matching keys in stored `data` are auto-hydrated by the constructor.

@verbatim
<code-snippet name="Custom block skeleton" lang="php">
namespace App\View\Blocks;

use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class Hero extends Block
{
    public string $title;
    public ?string $subtitle = null;

    public static ?string $labelField = 'title'; // shown in builder header

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
            TextInput::make('subtitle'),
        ];
    }

    public function toText(): ?string
    {
        return trim("{$this->title} {$this->subtitle}");
    }
}
</code-snippet>
@endverbatim

Conventions:

- Block `type()` defaults to the class basename (`Hero`). Override only if you need a custom string.
- Block view path defaults to `resources/views/blocks/{kebab-type}.blade.php`. Built-in blocks resolve to `filament-content-builder::blocks.*`.
- Set `public static ?string $labelField` to render the value of a state field (e.g. `'title'`) as the builder item header.
- Override `settingsSchema(): array` to add a per-block settings modal (a cog icon appears on the builder item). Settings live under `data.settings` by default — change the key with `settingsPrefix()`.
- Override `toText()` for SEO/plain-text extraction. Default returns `null`.
- For nesting, extend or model after `Blocks\Container` and use `ContentBlocksRenderer::make(...)->contentBlocks(fn () => FilamentContentBuilder::getContainerBlocks())`.
- The `CanBeFixed` trait's `getFields()` is **deprecated** — for fixed blocks, wrap `Block::schema()` in a Filament container with `->statePath('data.hero-block')` (see README "Fixed blocks").

## Templates

Templates pair a Filament field-set with a Blade view selectable via `Template` field. Useful for picking a page layout where each layout has its own set of inputs.

@verbatim
<code-snippet name="Template usage" lang="php">
use VanOns\FilamentContentBuilder\Fields\Template;
use VanOns\FilamentContentBuilder\Templates\TemplateService;

public static function form(Form $form): Form
{
    return $form->schema([
        ...app(TemplateService::class)->templateGroups(),

        Template::make('template')->required()->live(),
    ]);
}
</code-snippet>
@endverbatim

`TemplateService` resolves classes from each path in `config('filament-content-builder.template_directories')` (default `app_path('View/Templates')`). Public methods: `templates()`, `options()`, `templateFieldSets()`, `templateGroups()`, `resolve(string $type)`, `render($item)`.

## Facade

`VanOns\FilamentContentBuilder\Facade\FilamentContentBuilder` (alias `FilamentContentBuilder`) exposes static helpers:

| Method | Purpose |
|---|---|
| `getBlocks()` | All registered block classes. |
| `getContainerBlocks()` | Block classes allowed inside `Container`. |
| `getBuilderBlocks(?array $blocks = null)` | Convert classes to `Filament\Forms\Components\Builder\Block` instances. |
| `blockExists(string $type)` | Whether a block type is registered. |
| `getBlock(string $type, array $data)` | Hydrate a block instance (auto-increments `$blockIndex`). |
| `getBlockClass(?string $type)` | Resolve `class-string<Block>` from a type. |
| `parseBlocks(array $blocks)` | Map stored blocks through each block's `parseData()`. |
| `parseBlockInstances(array $blocks)` | Same, but returns hydrated `Block` instances. |

## Conventions to follow

- Generate new blocks with `php artisan make:content-block` rather than hand-rolling — it scaffolds the view, registers the class, and handles publishing the config.
- Use `Block::make($data)` (not `new Block($data)`) so resolution goes through the container.
- Rendering: prefer `<x-filament-content-builder::block-renderer>` over manually iterating `$block['type']`/`$block['data']`. The component handles unknown types gracefully (skipped).
- When adding embedded video/audio support, register services in `config('filament-content-builder.embeddable_services')` — the package wires them through `bensampo/laravel-embed`.
- Translation keys for built-ins live under `filament-content-builder-lang::blocks.*` and `filament-content-builder-lang::fields.*`. Reuse them in custom blocks where labels overlap.
