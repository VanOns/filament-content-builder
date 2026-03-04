@php
    /**
     * @var \VanOns\FilamentContentBuilder\Blocks\TextBlock $block
     */
@endphp

<section>
    @if(!empty($content = $block->content))
        <div>{!! $content !!}</div>
    @endisset
</section>
