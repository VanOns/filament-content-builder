<?php

namespace VanOns\FilamentContentBuilder\Blocks;

use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;
use VanOns\FilamentContentBuilder\Fields\ContentBlocksRenderer;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

class Container extends Block
{
    public array $content = [];

    public static function title(): string
    {
        return __('filament-content-builder-lang::blocks.container');
    }

    public static function icon(): ?string
    {
        return 'heroicon-s-square-3-stack-3d';
    }

    public static function schema(): array
    {
        return [
            ContentBlocksRenderer::make('content')
                ->label(__('filament-content-builder-lang::fields.content'))
                ->blockPickerColumns(1)
                ->contentBlocks(fn () => FilamentContentBuilder::getContainerBlocks()),
        ];
    }
}
