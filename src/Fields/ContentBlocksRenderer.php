<?php

namespace VanOns\FilamentContentBuilder\Fields;

use Filament\Forms\Components\Builder;
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
                CopyBlockData::make(),
            ])
            ->hintActions([
                PasteBlockAction::make(),
            ])
            ->collapsible()
            ->collapsed();
    }
}
