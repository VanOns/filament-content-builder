<?php

namespace VanOns\FilamentContentBuilder\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class ListBlock extends Block
{
    public ?string $title = null;
    public ?string $type = null;
    public array $items = [];

    public static function title(): string
    {
        return __('filament-content-builder-lang::blocks.list');
    }

    public static function icon(): ?string
    {
        return 'heroicon-o-list-bullet';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('title')
                ->label(__('filament-content-builder-lang::fields.title')),

            Select::make('type')
                ->label(__('filament-content-builder-lang::fields.type'))
                ->options([
                    'unordered' => __('filament-content-builder-lang::fields.unordered'),
                    'ordered' => __('filament-content-builder-lang::fields.ordered'),
                ])
                ->required(),

            Repeater::make('items')
                ->label(__('filament-content-builder-lang::fields.items'))
                ->schema([
                    TextInput::make('text')
                        ->label(__('filament-content-builder-lang::fields.text'))
                        ->required(),
                ]),
        ];
    }
}
