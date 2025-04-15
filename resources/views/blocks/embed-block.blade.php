@php
    /**
     * @var \VanOns\FilamentContentBuilder\Blocks\EmbedBlock $block
     */
@endphp

<section>
    @if(!empty($url = $block->url))
        <x-embed :url="$url" />
    @endif
</section>
