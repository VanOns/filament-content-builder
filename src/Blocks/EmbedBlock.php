<?php

namespace VanOns\FilamentContentBuilder\Blocks;

use BenSampo\Embed\Rules\EmbeddableUrl;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Validator;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;
use VanOns\FilamentContentBuilder\Helpers\UrlHelper;

class EmbedBlock extends Block
{
    public ?string $url = null;

    public static function title(): string
    {
        return __('filament-content-builder-lang::blocks.embed');
    }

    public static function icon(): ?string
    {
        return 'heroicon-o-play-circle';
    }

    // The field rule only covers the form, so stored data is checked again here.
    public function embeddableUrl(): ?string
    {
        $url = UrlHelper::sanitize($this->url, ['http', 'https']);

        if ($url === null) {
            return null;
        }

        return Validator::make(['url' => $url], ['url' => [static::urlRule()]])->passes() ? $url : null;
    }

    protected static function urlRule(): EmbeddableUrl
    {
        return (new EmbeddableUrl())
            ->allowedServices(config('filament-content-builder.embeddable_services'));
    }

    public static function schema(): array
    {
        return [
            TextInput::make('url')
                ->label(__('filament-content-builder-lang::fields.url'))
                ->url()
                ->rule(static::urlRule()),
        ];
    }
}
