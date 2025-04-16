<?php

namespace VanOns\FilamentContentBuilder\Fields;

use Filament\Forms\Components\Select;
use VanOns\FilamentContentBuilder\Templates\TemplateService;

class Template extends Select
{
    public static function make(string $name): static
    {
        $options = app(TemplateService::class)->options();
        return parent::make($name)
            ->options($options)
            ->live(onBlur: true)
            ->default(array_key_first($options))
            ->selectablePlaceholder(false);
    }
}
