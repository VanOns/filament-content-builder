<?php

namespace VanOns\FilamentContentBuilder\Blocks;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class TextBlock extends Block
{
    public string $content;

    public static function title(): string
    {
        return __('filament-content-builder-lang::blocks.text');
    }

    public static function icon(): ?string
    {
        return 'heroicon-o-document-text';
    }

    public static function schema(): array
    {
        return [
            RichEditor::make('content')
                ->label(__('filament-content-builder-lang::fields.content')),
        ];
    }
}
