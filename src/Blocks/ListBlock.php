<?php

namespace VanOns\FilamentContentBuilder\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class ListBlock extends Block
{
    public string $title;
    public string $type;
    public array $items;

    public static function title(): string
    {
        return __('List');
    }

    public static function icon(): ?string
    {
        return 'heroicon-o-list-bullet';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('title')
                ->label(__('Title')),

            Select::make('type')
                ->label(__('Type'))
                ->options([
                    'unordered' => __('Unordered'),
                    'ordered' => __('Ordered'),
                ])
                ->required(),

            Repeater::make('items')
                ->label(__('Items'))
                ->schema([
                    TextInput::make('text')
                        ->label(__('Text'))
                        ->required(),
                ]),
        ];
    }
}
