<?php

namespace Tests;

use BenSampo\Embed\EmbedServiceProvider;
use Filament\Support\SupportServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use VanOns\FilamentContentBuilder\FilamentContentBuilderServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            EmbedServiceProvider::class,
            FilamentContentBuilderServiceProvider::class,
        ];
    }
}
