<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Blocks
    |--------------------------------------------------------------------------
    |
    | This option defines the blocks that are available in the content blocks
    | builder. You can add your own blocks by creating a class that implements
    | the `VanOns\FilamentContentBuilder\Blocks\Contracts\Block` interface, or
    | by simply running `php artisan moopress:make-content-block`.
    |
    */

    'blocks' => [
        \VanOns\FilamentContentBuilder\Blocks\CtaBlock::class,
        \VanOns\FilamentContentBuilder\Blocks\ListBlock::class,
        \VanOns\FilamentContentBuilder\Blocks\TextBlock::class,
    ],

];
