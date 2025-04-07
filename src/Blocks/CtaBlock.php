<?php

namespace VanOns\FilamentContentBuilder\Blocks;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class CtaBlock extends Block
{
    public string $text;
    public string $url;
    public string $target;

    public static function title(): string
    {
        return __('filament-content-builder-lang::blocks.cta');
    }

    public static function icon(): ?string
    {
        return 'heroicon-o-cursor-arrow-rays';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('text')
                ->label(__('filament-content-builder-lang::fields.text')),

            TextInput::make('url')
                ->label(__('filament-content-builder-lang::fields.url')),

            Select::make('target')
                ->label(__('filament-content-builder-lang::fields.target'))
                ->options([
                    '_self' => __('filament-content-builder-lang::fields.self'),
                    '_blank' => __('filament-content-builder-lang::fields.blank'),
                ])
                ->default('_self')
                ->required(),
        ];
    }
}
