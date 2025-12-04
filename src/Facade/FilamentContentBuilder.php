<?php

namespace VanOns\FilamentContentBuilder\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \VanOns\FilamentContentBuilder\FilamentContentBuilder
 */
class FilamentContentBuilder extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'filament-content-builder';
    }
}
