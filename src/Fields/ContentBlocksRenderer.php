<?php

namespace VanOns\FilamentContentBuilder\Fields;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use VanOns\FilamentContentBuilder\Actions\CopyBlockData;
use VanOns\FilamentContentBuilder\Actions\PasteBlockAction;
use VanOns\FilamentContentBuilder\Actions\SettingsModalAction;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;
use VanOns\FilamentContentBuilder\Traits\HasContentBlocks;

class ContentBlocksRenderer extends Builder
{
    use HasContentBlocks;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blocks(fn () => $this->getContentBlocks())
            ->contentBlocks(FilamentContentBuilder::getBlocks())
            ->extraItemActions([
                SettingsModalAction::make(),
                CopyBlockData::make()->hidden(! config('filament-content-builder.copy_paste', true)),
            ])
            ->hintActions([
                PasteBlockAction::make()->hidden(! config('filament-content-builder.copy_paste', true)),
            ])
            ->collapsible()
            ->collapsed()
            ->mutateDehydratedStateUsing(fn (ContentBlocksRenderer $component, ?array $state): array => $component->withoutUnavailableBlocks($state ?? []));
    }

    public function withoutUnavailableBlocks(array $state): array
    {
        $types = array_map(fn (Block $block): string => $block->getName(), $this->getBlocks());

        return collect($state)
            ->filter(fn (array $item): bool => in_array($item['type'] ?? null, $types, true))
            ->values()
            ->all();
    }
}
