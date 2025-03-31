<?php

namespace VanOns\FilamentContentBlocks\Facade;

use Illuminate\Support\Facades\Facade;

class FilamentContentBlocks extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'filament-content-blocks';
    }
}
