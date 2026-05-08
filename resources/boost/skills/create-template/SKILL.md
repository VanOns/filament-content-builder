---
name: create-template
description: Use when the user wants to add a page-layout template to a project that uses van-ons/filament-content-builder — e.g. "create a landing-page template", "add a new template", "scaffold a template class", or wiring `Template::make()` and `TemplateService` into a Filament resource. Covers scaffolding via `make:template`, defining template fields, registering directories, and rendering on the front-end.
---

# Create a template

## When to use this skill

Use when the project's Filament resources expose a `Template` select that swaps the rest of the form's field-set based on the chosen layout — that's the `van-ons/filament-content-builder` template system. Triggers: "create a template", "add a {layout-name} template", "scaffold a template class", "wire up TemplateService".

Skip this skill for: regular Blade view templates unrelated to this package, or content-block work (use `create-content-block`).

## Workflow

### 1. Scaffold

```bash
php artisan make:template Landing
```

Creates `app/View/Templates/Landing.php`. Unlike `make:content-block`, this command does **not** touch any config — registration happens by directory scanning.

### 2. Define fields

Edit `app/View/Templates/{Name}.php`. Override `fields()` to declare the schema; override `name()` for a friendly label.

```php
namespace App\View\Templates;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use VanOns\FilamentContentBuilder\Templates\Contracts\Template;

class Landing extends Template
{
    public static function name(): string
    {
        return __('templates.landing');
    }

    public static function fields(string $prefix = 'template_data.'): array
    {
        return [
            TextInput::make($prefix . 'hero_title')->required(),
            Textarea::make($prefix . 'hero_subtitle'),
        ];
    }
}
```

The default `type()` is the kebab-cased class basename with `Template` stripped (e.g. `LandingTemplate` → `landing`, `Landing` → `landing`). Override `static $type` or `static type()` to customize.

### 3. Decision table — template options

| Want to… | Override |
|---|---|
| Custom display name | `static name()` or `protected static ?string $name` |
| Custom slug stored in the DB | `static type()` or `protected static ?string $type` |
| Adjust the field-prefix used in stored state | Pass a different `$prefix` from `fields()` (default: `template_fields.` from base; the stub uses `template_data.`) |
| Scan an additional directory for template classes | Add path to `template_directories` in `config/filament-content-builder.php` |
| Use a `Group` instead of a `Fieldset` in the form | Use `app(TemplateService::class)->templateGroups()` instead of `templateFieldSets()` |
| Render on the front-end | Override `render(Model $model): View` (default: `view('templates.{first-segment-of-kebab-name}', compact('model'))`) |

### 4. Wire up the resource form

Both `templateGroups()` and `templateFieldSets()` return arrays keyed by template type — splat them into the schema. Each entry self-applies a `visible()` rule that checks the `template` field's current value.

```php
use VanOns\FilamentContentBuilder\Fields\Template;
use VanOns\FilamentContentBuilder\Templates\TemplateService;

public static function form(Form $form): Form
{
    return $form->schema([
        Template::make('template')->required()->live(),

        ...app(TemplateService::class)->templateGroups(),
        // OR fieldsets, with visible labels:
        // ...app(TemplateService::class)->templateFieldSets(),
    ]);
}
```

`Template` is a `Select` subclass — the select defaults to the first option and disables a placeholder, and is `live(onBlur: true)`. Override defaults via standard Filament chaining.

### 5. Render on the front-end

`TemplateService::render($model)` resolves the template class from `$model->template`, instantiates it, and calls `render($model)` — which by default returns `view('templates.{name}', compact('model'))`.

```php
return app(TemplateService::class)->render($page);
```

For a custom render (e.g. different view path or extra view data), override `render()` on the template class:

```php
public function render(Model $model): View
{
    return view('layouts.landing', [
        'page'    => $model,
        'data'    => $model->template_data,
    ]);
}
```

### 6. (Optional) Add a custom template directory

If you keep templates outside `app/View/Templates`:

```php
// config/filament-content-builder.php
'template_directories' => [
    app_path('/View/Templates'),
    app_path('/Marketing/Templates'),
],
```

`TemplateService::templates()` globs `*.php` in each directory (including one level of subfolders via `GLOB_BRACE`). Each file must declare a class extending `VanOns\FilamentContentBuilder\Templates\Contracts\Template`.

## Common follow-ups

- **Storing template data**: the stub uses prefix `template_data.`, so on the model expose a JSON-cast `template_data` attribute (or eloquent `casts`) and a `template` string column for the slug.
- **Conditional Filament fields inside `fields()`**: the template's `fieldSet()`/`group()` already handles "only show when this template is selected" — don't duplicate that with another `visible()`.
- **Custom stub**: run `php artisan vendor:publish --tag=filament-content-builder-stubs` and edit `stubs/template.stub`. Placeholder is `{{ class }}`.

## Anti-patterns

- Don't register templates in config — they're discovered by scanning `template_directories`. Adding entries elsewhere has no effect.
- Don't wrap the splatted groups/fieldsets in another container with its own `visible()` rule; each entry already gates itself on the `template` value.
- Don't call `new Landing()` to render — use `app(TemplateService::class)->resolve($type)` so the directory scan and `is_subclass_of` check run.
- Don't make `Template::make()` non-live — the conditional field-sets rely on the `template` field firing updates.
