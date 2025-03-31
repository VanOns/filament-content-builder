<?php

namespace VanOns\FilamentContentBlocks;

use Filament\Forms\Components\Builder\Block as FilamentBlock;
use VanOns\FilamentContentBlocks\Blocks\Contracts\Block;

class FilamentContentBlocks
{
    /**
     * @return array<FilamentBlock>
     */
    public static function getBlocks(): array
    {
        $blocks = [];

        foreach (glob(__DIR__ . '/Blocks/*.php') as $filename) {
            $class = 'VanOns\\FilamentContentBlocks\\Blocks\\' . basename($filename, '.php');
            if (is_a($class, Block::class, true)) {
                $blocks[] = $class::builderBlock();
            }
        }

        return $blocks;
    }
}
