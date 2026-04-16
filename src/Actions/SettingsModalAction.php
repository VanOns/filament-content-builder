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
        $data = $this->getBlockItemData($arguments, $state);

        return $data ? FilamentContentBuilder::getBlockClass($data['type'] ?? null) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getBlockItemData(array $arguments, array $state): ?array
    {
        return isset($arguments['item']) ? ($state[$arguments['item']] ?? null) : null;
    }

    protected function getBlockSettingsTitle(array $arguments, array $state): string
    {
        $class = $this->getBlockClass($arguments, $state);

        return $class ? $class::settingsTitle() : Block::settingsTitle();
    }

    protected function getBlockSettingsIcon(array $arguments, array $state): string
    {
        $class = $this->getBlockClass($arguments, $state);

        return $class ? $class::settingsIcon() : Block::settingsIcon();
    }

    protected function hasBlockSettings(array $arguments, array $state): bool
    {
        $class = $this->getBlockClass($arguments, $state);

        return $class && !empty($class::settingsSchema());
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
        if (!$class) {
            return [];
        }

        $data = $this->getBlockItemData($arguments, $state);

        return $data['data'][$class::settingsPrefix()] ?? [];
    }

    protected function setBlockSettingsData(Builder $component, array $arguments, array $state, Set $set, array $data): void
    {
        $class = $this->getBlockClass($arguments, $state);
        if (!$class) {
            return;
        }

        $mutatedData = $class::mutateSettingsData($data);

        $set(implode('.', [
            $component->getName(),
            $arguments['item'],
            'data',
            $class::settingsPrefix(),
        ]), $mutatedData);
    }
}
