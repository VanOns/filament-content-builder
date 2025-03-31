<?php

namespace VanOns\FilamentContentBlocks;

use Filament\Forms\Components\Builder\Block as FilamentBlock;
use VanOns\FilamentContentBlocks\Blocks\Contracts\Block;

class FilamentContentBlocks
{
    /**
     * @return array<Block>
     */
    public static function getBlocks(): array
    {
        $blocks = [];

        foreach (config('filament-content-blocks.blocks') as $class) {
            if (is_a($class, Block::class, true)) {
                $blocks[] = $class;
            }
        }

        return $blocks;
    }

    /**
     * @return array<FilamentBlock>
     */
    public static function getBuilderBlocks(): array
    {
        return collect(static::getBlocks())
            ->map(fn (Block|string $block) => $block::builderBlock())
            ->toArray();
    }

    public static function blockExists(string $name): bool
    {
        return collect(static::getBlocks())
            ->contains(fn (Block|string $block) => $block::type() === $name);
    }

    public static function getBlock(string $name, array $data): ?Block
    {
        /** @var Block|null $class */
        $class = collect(static::getBlocks())
            ->first(fn (Block|string $block) => $block::type() === $name);

        return $class ? $class::make($data) : null;
    }
}
