@php
    /**
     * @var \VanOns\FilamentContentBuilder\Blocks\Container $block
     */
@endphp

<div>
    <x-filament-content-builder::block-renderer :blocks="$block->content" :nested="true" />
</div>
