<?php

namespace VanOns\FilamentContentBlocks\Blocks;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBlocks\Blocks\Contracts\Block;

class TextBlock extends Block
{
    public string $title;
    public string $subtitle;
    public string $content;

    public static function title(): string
    {
        return __('Text');
    }

    public static function icon(): ?string
    {
        return 'heroicon-o-document-text';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('title')
                ->label(__('Title')),

            TextInput::make('subtitle')
                ->label(__('Subtitle')),

            RichEditor::make('content')
                ->label(__('Content')),
        ];
    }
}
