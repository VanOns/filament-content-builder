<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use VanOns\FilamentContentBuilder\FilamentContentBuilderServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FilamentContentBuilderServiceProvider::class,
        ];
    }
}
