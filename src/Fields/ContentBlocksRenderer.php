<?php

namespace VanOns\FilamentContentBuilder\Fields;

use Filament\Forms\Components\Builder;
use VanOns\FilamentContentBuilder\Facade\FilamentContentBuilder;

class ContentBlocksRenderer extends Builder
{
    protected function setUp(): void
    {
        $this->blocks(FilamentContentBuilder::getBuilderBlocks())
            ->collapsible()
            ->collapsed();

        parent::setUp();
    }
}
