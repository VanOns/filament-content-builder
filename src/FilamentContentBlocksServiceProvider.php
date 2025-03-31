<?php

namespace VanOns\FilamentContentBlocks;

use Illuminate\Support\ServiceProvider;
use VanOns\FilamentContentBlocks\Console\MakeContentBlockCommand;

class FilamentContentBlocksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__ . '/../config/filament-content-blocks.php',
            key: 'filament-content-blocks'
        );

        $this->publishes(
            paths: [
                __DIR__ . '/../config/filament-content-blocks.php' => config_path('filament-content-blocks.php'),
            ],
            groups: 'filament-content-blocks-config'
        );

        $this->loadTranslationsFrom(
            path: __DIR__ . '/../lang',
            namespace: 'filament-content-blocks'
        );

        $this->publishes(
            paths: [
                __DIR__ . '/../lang' => $this->app->langPath('vendor/filament-content-blocks'),
            ],
            groups: 'filament-content-blocks-lang'
        );

        $this->loadViewsFrom(
            path: __DIR__ . '/../resources/views',
            namespace: 'filament-content-blocks'
        );

        $this->publishes(
            paths: [
                __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-content-blocks'),
            ],
            groups: 'filament-content-blocks-views'
        );

        $this->publishes(
            paths: [
                __DIR__ . '/../stubs' => base_path('stubs'),
            ],
            groups: 'filament-content-blocks-stubs'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeContentBlockCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->app->bind('filament-content-blocks', function () {
            return new FilamentContentBlocks();
        });
    }
}
