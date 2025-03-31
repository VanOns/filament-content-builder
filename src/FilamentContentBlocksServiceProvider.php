<?php

namespace VanOns\FilamentContentBlocks;

use Illuminate\Support\ServiceProvider;
use VanOns\FilamentContentBlocks\Console\FilamentContentBlocksCommand;

class FilamentContentBlocksServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //config
        $this->publishes(
            paths: [
                __DIR__ . '/../config/filament-content-blocks.php' => config_path('filament-content-blocks.php'),
            ],
            groups: 'filament-content-blocks-config'
        );

        //translations
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

        //views
        $this->loadViewsFrom(
            path: __DIR__ . '/../resources/views',
            namespace: 'filament-content-blocks'
        );

        //public assets
//        $this->publishes(
//            paths: [
//                __DIR__ . '/../public' => public_path('vendor/courier'),
//            ],
//            groups: 'skeleton-public'
//        );

        //commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                FilamentContentBlocksCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->bind('filament-content-blocks', function ($app) {
            return new FilamentContentBlocks();
        });
    }
}
