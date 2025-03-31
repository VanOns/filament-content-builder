<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Blocks
    |--------------------------------------------------------------------------
    |
    | This option defines the blocks that are available in the content blocks
    | builder. You can add your own blocks by creating a class that implements
    | the `VanOns\FilamentContentBlocks\Blocks\Contracts\Block` interface, or
    | by simply running `php artisan moopress:make-content-block`.
    |
    */

    'blocks' => [
        \VanOns\FilamentContentBlocks\Blocks\CtaBlock::class,
        \VanOns\FilamentContentBlocks\Blocks\ListBlock::class,
        \VanOns\FilamentContentBlocks\Blocks\TextBlock::class,
    ],

];
