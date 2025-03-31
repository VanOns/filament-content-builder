<?php

namespace VanOns\FilamentContentBlocks\Fields;

use Filament\Forms\Components\Builder;
use VanOns\FilamentContentBlocks\Facade\FilamentContentBlocks;

class ContentBlocksRenderer extends Builder
{
    protected function setUp(): void
    {
        $this->blocks(FilamentContentBlocks::getBuilderBlocks())
            ->collapsible()
            ->collapsed();

        parent::setUp();
    }
}
