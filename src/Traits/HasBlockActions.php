<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Filament\Actions\Action;
use VanOns\FilamentContentBuilder\Actions\SettingsModalAction;

trait HasBlockActions
{
    /**
     * @return array<Action>
     */
    public static function blockActions(): array
    {
        return [
            SettingsModalAction::make(),
        ];
    }
}
