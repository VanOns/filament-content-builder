@php
    /**
     * @var \VanOns\FilamentContentBlocks\Blocks\TextBlock $block
     */
@endphp

<section>
    @if(!empty($subtitle = $block->subtitle))
        <p>{{ $subtitle }}</p>
    @endif

    @if(!empty($title = $block->title))
        <h2>{{ $title }}</h2>
    @endif

    @if(!empty($content = $block->content))
        <div>{!! $content !!}</div>
    @endisset
</section>
