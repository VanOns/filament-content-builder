@php
    use Filament\Support\Facades\FilamentAsset;
@endphp

<x-filament::link
    tag="span"
    size="xs"
    x-load-css="[{{ \Illuminate\Support\Js::from(FilamentAsset::getStyleHref('block-gallery', package: 'van-ons/filament-content-builder')) }}]"
    class="fcb-block-picker-gallery-link"
>
    {{ __('filament-content-builder-lang::gallery.browse') }}
</x-filament::link>
