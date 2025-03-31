@php
    /**
     * @var \VanOns\FilamentContentBlocks\Blocks\CtaBlock $block
     */

    $url = $block->url;
@endphp

<section>
    @if($url)
        <a href="{{ $url }}" target="{{ $block->target }}">
    @endif

    @if(!empty($text = $block->text))
        <p>{{ $text }}</p>
    @endif

    @if($url)
        </a>
    @endif
</section>
