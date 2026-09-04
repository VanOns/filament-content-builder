<?php

namespace VanOns\FilamentContentBuilder;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use VanOns\FilamentContentBuilder\Console\MakeContentBlockCommand;
use VanOns\FilamentContentBuilder\Console\MakeTemplateCommand;
use VanOns\FilamentContentBuilder\View\Components\Block;

class FilamentContentBuilderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__ . '/../config/filament-content-builder.php',
            key: 'filament-content-builder'
        );

        $this->publishes(
            paths: [
                __DIR__ . '/../config/filament-content-builder.php' => config_path('filament-content-builder.php'),
            ],
            groups: 'filament-content-builder-config'
        );

        $this->loadViewsFrom(
            path: __DIR__ . '/../resources/views',
            namespace: 'filament-content-builder'
        );

        $this->publishes(
            paths: [
                __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-content-builder'),
            ],
            groups: 'filament-content-builder-views'
        );

        $this->publishes(
            paths: [
                __DIR__ . '/../stubs' => base_path('stubs'),
            ],
            groups: 'filament-content-builder-stubs'
        );

        $this->loadTranslationsFrom(
            path: __DIR__.'/../lang',
            namespace: 'filament-content-builder-lang'
        );

        $this->publishes(
            paths: [
                __DIR__.'/../lang' => $this->app->langPath('vendor/filament-content-builder'),
            ],
            groups: 'filament-content-builder-lang'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeContentBlockCommand::class,
                MakeTemplateCommand::class,
            ]);
        }

        Blade::component('filament-content-builder::block', Block::class);
    }

    public function register(): void
    {
        $this->app->bind('filament-content-builder', FilamentContentBuilder::class);

        // Scoped so repeated calls within one request share the computed usage.
        $this->app->scoped(Usage\BlockUsageService::class);
    }
}
