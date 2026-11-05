<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use VanOns\FilamentContentBuilder\Actions\SettingsModalAction;

trait HasBlockActions
{
    /**
     * @return array<Action|Field>
     */
    public static function blockActions(): array
    {
        return [
            SettingsModalAction::make(),
        ];
    }
}
