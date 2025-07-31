<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Closure;
use Filament\Forms\Components\Builder;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

/**
 * @mixin Builder
 */
trait HasContentBlocks
{
    public array|Closure $contentBlocks = [];

    public function contentBlocks(array|Closure $blocks): static
    {
        $this->contentBlocks = $blocks;

        return $this;
    }

    public function getContentBlocks(): array
    {
        return FilamentContentBuilder::getBuilderBlocks(
            $this->evaluate($this->contentBlocks)
        );
    }
}
