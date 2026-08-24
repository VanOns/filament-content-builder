@php
    /**
     * @var \VanOns\FilamentContentBuilder\Blocks\EmbedBlock $block
     */
@endphp

<section>
    @if($url = $block->embeddableUrl())
        <x-embed :url="$url" />
    @endif
</section>
