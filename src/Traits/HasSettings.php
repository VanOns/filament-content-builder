<?php

namespace VanOns\FilamentContentBuilder\Traits;

trait HasSettings
{
    public function getSettings(): array
    {
        return $this->data[static::settingsPrefix()] ?? [];
    }

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

    public static function mutateSettingsData(array $data): array
    {
        return $data;
    }
}