<?php

namespace VanOns\FilamentContentBuilder\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;
use VanOns\FilamentContentBuilder\Rules\ValidBlockData;

class PasteBlockAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'paste';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-content-builder-lang::fields.paste_block'))
            ->icon('heroicon-o-clipboard-document-check')
            ->modal()
            ->modalIcon('heroicon-o-clipboard-document-check')
            ->modalHeading(__('filament-content-builder-lang::fields.paste_block_heading'))
            ->modalDescription(__('filament-content-builder-lang::fields.paste_block_description'))
            ->modalSubmitActionLabel(__('filament-content-builder-lang::fields.paste_block_submit'))
            ->modalWidth('lg')
            ->schema([
                Textarea::make('paste')
                    ->label(__('filament-content-builder-lang::fields.paste_block_data_label'))
                    ->placeholder('{"type":"Paragraph","data":{...}}')
                    ->helperText(__('filament-content-builder-lang::fields.paste_block_data_helper'))
                    ->rows(6)
                    ->required()
                    ->rules([new ValidBlockData()]),
            ])
            ->action(function (Builder $component, array $data, ?array $state) {
                $block = json_decode($data['paste'], true);

                if (!is_array($block) || !is_string($block['type'] ?? null) || !is_array($block['data'] ?? null)) {
                    return;
                }

                $class = FilamentContentBuilder::getBlockClass($block['type']);
                if ($class === null) {
                    return;
                }

                $block['data'] = Arr::only($block['data'], ValidBlockData::allowedKeys($class));

                $component->state([
                    ...$state ?? [],
                    Str::uuid()->toString() => $block,
                ]);
            });
    }
}
