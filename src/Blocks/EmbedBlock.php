<?php

namespace VanOns\FilamentContentBuilder\Blocks;

use BenSampo\Embed\Rules\EmbeddableUrl;
use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class EmbedBlock extends Block
{
    public string $url;

    public static function title(): string
    {
        return __('filament-content-builder-lang::blocks.embed');
    }

    public static function icon(): ?string
    {
        return 'heroicon-o-play-circle';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('url')
                ->label(__('filament-content-builder-lang::fields.url'))
                ->url()
                ->rule(
                    (new EmbeddableUrl())
                        ->allowedServices(
                            config('filament-content-builder.embeddable_services')
                        )
                ),
        ];
    }
}
