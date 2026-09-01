<?php

namespace VanOns\FilamentContentBuilder\Templates\Contracts;

use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use VanOns\FilamentContentBuilder\Fields\TemplateFields;

abstract class Template
{
    protected static ?string $type = null;
    protected static ?string $name = null;

    public static function type(): string
    {
        if (static::$type) {
            return static::$type;
        }

        return Str::of(static::class)
            ->replace('\\', '/')
            ->basename()
            ->replace('Template', '')
            ->kebab()
            ->toString();
    }

    public static function name(): string
    {
        if (static::$name) {
            return static::$name;
        }

        return Str::of(static::type())->replace('-', ' ')->title()->toString();
    }

    public static function fields(string $prefix = 'template_fields.'): array
    {
        return [];
    }

    /**
     * @deprecated Use `TemplateFields::make()` instead.
     */
    public static function group(string $fieldName = 'template'): Group
    {
        return TemplateFields::make()
            ->templateField($fieldName)
            ->only(static::type());
    }

    /**
     * @deprecated Use `TemplateFields::make()->fieldset()` instead.
     */
    public static function fieldSet(string $fieldName = 'template'): Fieldset
    {
        return Fieldset::make(static::type())
            ->label(static::name())
            ->schema([static::group($fieldName)])
            ->visible(fn (Get $get): bool => $get($fieldName) === static::type() && !empty(static::fields()))
            ->columns(1);
    }

    public function render(Model $model): View
    {
        $name = Str::before(
            Str::kebab(class_basename(static::class)),
            '-'
        );

        return view('templates.' . $name, compact('model'));
    }
}
