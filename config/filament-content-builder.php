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

    /*
    |--------------------------------------------------------------------------
    | Allowed link schemes
    |--------------------------------------------------------------------------
    |
    | URL schemes that blocks are allowed to link to. Other schemes are refused
    | when saving and left out when rendering. Scheme-less URLs such as
    | `/contact` and `#anchor` are always allowed.
    |
    */

    'allowed_link_schemes' => ['http', 'https', 'mailto', 'tel'],

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

    /*
    |--------------------------------------------------------------------------
    | Block usage
    |--------------------------------------------------------------------------
    |
    | When enabled, the panel plugin registers a page that shows how often and
    | where each block is used. Map each model to the column(s) that store
    | block content. Optionally set a `title_attribute` per model, used as the
    | record label on the usage page. The `permission` is a gate ability that
    | is checked before granting access to the page. The computed usage is
    | cached for `cache` seconds; set it to null to disable caching.
    |
    */

    'usage' => [
        'enabled' => false,

        'permission' => null,

        'cache' => 300,

        'sources' => [
            // \App\Models\Page::class => 'content',
            // \App\Models\Page::class => ['content', 'footer'],
            // \App\Models\Post::class => ['columns' => ['content', 'footer'], 'title_attribute' => 'name'],
        ],
    ],

];
