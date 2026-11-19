<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use VanOns\FilamentContentBuilder\Actions\SettingsModalAction;

trait HasBlockToolbar
{
    /**
     * @return array<Action|Field>
     */
    public static function blockToolbar(): array
    {
        return [
            SettingsModalAction::make(),
        ];
    }
}
