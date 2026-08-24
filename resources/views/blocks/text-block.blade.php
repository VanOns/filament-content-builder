@php
    /**
     * @var \VanOns\FilamentContentBuilder\Blocks\TextBlock $block
     */
@endphp

<section>
    @if(!empty($content = $block->content))
        <div>{!! \Illuminate\Support\Str::sanitizeHtml($content) !!}</div>
    @endif
</section>
