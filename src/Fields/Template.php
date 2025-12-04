<?php

namespace VanOns\FilamentContentBuilder\Fields;

use Filament\Forms\Components\Select;
use VanOns\FilamentContentBuilder\Templates\TemplateService;

class Template extends Select
{
    public static function getDefaultName(): ?string
    {
        return 'template';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $options = app(TemplateService::class)->options();

        $this->options($options)
            ->live(onBlur: true)
            ->default(array_key_first($options))
            ->selectablePlaceholder(false);
    }
}
