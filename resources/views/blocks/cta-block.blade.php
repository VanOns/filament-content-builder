@php
    /**
     * @var \VanOns\FilamentContentBuilder\Blocks\CtaBlock $block
     */

    $url = \VanOns\FilamentContentBuilder\Helpers\UrlHelper::sanitize($block->url);
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
