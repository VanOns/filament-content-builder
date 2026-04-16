<?php

namespace VanOns\FilamentContentBuilder\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Set;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

class SettingsModalAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'settings';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label($this->getBlockSettingsTitle(...))
            ->icon($this->getBlockSettingsIcon(...))
            ->visible($this->hasBlockSettings(...))
            ->schema($this->getBlockSettingsSchema(...))
            ->fillForm($this->getBlockSettingsData(...))
            ->action($this->setBlockSettingsData(...));
    }

    /**
     * @return class-string<Block>|null
     */
    protected function getBlockClass(array $arguments, array $state): ?string
    {
        if (!isset($arguments['item'])) {
            return null;
        }
        $data = $state[$arguments['item']];

        return FilamentContentBuilder::getBlockClass($data['type'] ?? null);
    }

    protected function getBlockSettingsTitle(array $arguments, array $state): string
    {
        $class = $this->getBlockClass($arguments, $state);
        if (!$class) {
            return Block::settingsTitle();
        }

        return $class::settingsTitle();
    }

    protected function getBlockSettingsIcon(array $arguments, array $state): string
    {
        $class = $this->getBlockClass($arguments, $state);
        if (!$class) {
            return Block::settingsIcon();
        }

        return $class::settingsIcon();
    }

    protected function hasBlockSettings(array $arguments, array $state): bool
    {
        return empty($this->getBlockSettingsSchema($arguments, $state)) === false;
    }

    protected function getBlockSettingsSchema(array $arguments, array $state): array
    {
        $class = $this->getBlockClass($arguments, $state);
        if (!$class || empty($schema = $class::settingsSchema())) {
            return [];
        }

        return [
            Group::make($schema)
                ->columnSpanFull()
                ->columns(),
        ];
    }

    protected function getBlockSettingsData(array $arguments, array $state): array
    {
        $class = $this->getBlockClass($arguments, $state);
        return $state[$arguments['item']]['data'][$class::settingsPrefix()] ?? [];
    }

    protected function setBlockSettingsData(Builder $component, array $arguments, array $state, Set $set, array $data): void
    {
        $id = $arguments['item'];
        if (!isset($arguments['item']) || !($class = $this->getBlockClass($arguments, $state))) {
            return;
        }

        $key = implode('.', [
            $component->getName(),
            $id,
            'data',
            $class::settingsPrefix(),
        ]);

        $set($key, $data);
    }
}