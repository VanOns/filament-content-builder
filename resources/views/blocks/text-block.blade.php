@php
    /**
     * @var \VanOns\FilamentContentBuilder\Blocks\TextBlock $block
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
        <div>{!! \Illuminate\Support\Str::sanitizeHtml($content) !!}</div>
    @endif
</section>
