<?php

namespace VanOns\FilamentContentBuilder\Traits;

trait HasSettings
{
    public static function settingsPrefix(): string
    {
        return 'settings';
    }

    public static function settingsTitle(): string
    {
        return __('filament-content-builder-lang::fields.settings');
    }

    public static function settingsIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function settingsSchema(): array
    {
        return [];
    }
}