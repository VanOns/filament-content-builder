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
    | by simply running `php artisan make:content-block`.
    |
    */

    'blocks' => [
        \VanOns\FilamentContentBuilder\Blocks\CtaBlock::class,
        \VanOns\FilamentContentBuilder\Blocks\ListBlock::class,
        \VanOns\FilamentContentBuilder\Blocks\TextBlock::class,
        \VanOns\FilamentContentBuilder\Blocks\EmbedBlock::class,
        \VanOns\FilamentContentBuilder\Blocks\Container::class,
    ],

    'container-blocks' => [
        \VanOns\FilamentContentBuilder\Blocks\TextBlock::class,
    ],

    /**
     * Embeddable services, for more info see:
     * https://github.com/BenSampo/laravel-embed
     */
    'embeddable_services' => [
        \BenSampo\Embed\Services\YouTube::class,
        \BenSampo\Embed\Services\Vimeo::class,
    ],

    'template_directories' => [
        app_path('/View/Templates'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Copy / Paste blocks
    |--------------------------------------------------------------------------
    |
    | When enabled, each content block gets a copy button, and a paste button
    | appears on the builder field to import a previously copied block.
    |
    */

    'copy_paste' => true,

];
