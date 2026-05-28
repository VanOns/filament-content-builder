<?php

namespace VanOns\FilamentContentBuilder\Traits;

use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

trait ResolvesBlockClass
{
    /**
     * @var array<string, class-string<Block>|null>
     */
    protected array $resolvedBlockClasses = [];

    /**
     * @return class-string<Block>|null
     */
    protected function getBlockClass(array $arguments, array $state): ?string
    {
        $data = $this->getBlockItemData($arguments, $state);
        $type = $data['type'] ?? null;

        if (! $type) {
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
}
