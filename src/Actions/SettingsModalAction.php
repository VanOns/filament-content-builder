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
    /** @var array<string, class-string<Block>|null> */
    protected array $resolvedBlockClasses = [];

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
            ->mutateDataUsing($this->mutateBlockSettingsData(...))
            ->action($this->setBlockSettingsData(...));
    }

    /**
     * @return class-string<Block>|null
     */
    protected function getBlockClass(array $arguments, array $state): ?string
    {
        $data = $this->getBlockItemData($arguments, $state);
        $type = $data['type'] ?? null;

        if (!$type) {
            return null;
        }

        if (array_key_exists($type, $this->resolvedBlockClasses)) {
            return $this->resolvedBlockClasses[$type];
        }

        return $this->resolvedBlockClasses[$type] = FilamentContentBuilder::getBlockClass($type);
    }

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
            Group::make(fn () => $schema)
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

    protected function mutateBlockSettingsData(array $arguments, array $state, array $data): array
    {
        $class = $this->getBlockClass($arguments, $state);
        if (!$class) {
            return [];
        }

        return $class::mutateSettingsData($data);
    }

    protected function setBlockSettingsData(Builder $component, array $arguments, array $state, Set $set, array $data): void
    {
        $class = $this->getBlockClass($arguments, $state);
        if (!$class) {
            return;
        }

        $set(implode('.', [
            $component->getName(),
            $arguments['item'],
            'data',
            $class::settingsPrefix(),
        ]), $data);
    }
}
