<?php

namespace VanOns\FilamentContentBuilder;

use Filament\Forms\Components\Builder\Block as FilamentBlock;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class FilamentContentBuilder
{
    /**
     * @return array<Block>
     */
    public static function getBlocks(): array
    {
        $blocks = [];

        foreach (config('filament-content-builder.blocks') as $class) {
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

    public static function getBlock(string $name, array $data, int $index = 0): ?Block
    {
        $data = [
            ...$data,
            'blockIndex' => $index,
        ];

        /** @var Block|null $class */
        $class = collect(static::getBlocks())
            ->first(fn (Block|string $block) => $block::type() === $name);

        return $class ? $class::make($data) : null;
    }
}
