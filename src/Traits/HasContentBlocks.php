<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Closure;
use Filament\Forms\Components\Builder;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;
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

    /**
     * @return class-string<Block>|null
     */
    public function getContentBlockClass(?string $type): ?string
    {
        return collect($this->evaluate($this->contentBlocks))
            ->first(fn (string $block): bool => $block::type() === $type);
    }
}
