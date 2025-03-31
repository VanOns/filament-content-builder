<?php

namespace VanOns\FilamentContentBlocks\Blocks;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBlocks\Blocks\Contracts\Block;

class CtaBlock extends Block
{
    public string $text;
    public string $url;
    public string $target;

    public static function title(): string
    {
        return __('CTA');
    }

    public static function icon(): ?string
    {
        return 'heroicon-o-cursor-arrow-rays';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('text')
                ->label(__('Text')),

            TextInput::make('url')
                ->label(__('URL')),

            Select::make('target')
                ->label(__('Target'))
                ->options([
                    '_self' => __('Self'),
                    '_blank' => __('Blank'),
                ])
                ->default('_self')
                ->required(),
        ];
    }
}
