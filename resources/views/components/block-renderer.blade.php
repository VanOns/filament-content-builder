@props([
    'blocks' => [],
])

@isset($blocks)
    @foreach($blocks as $block)
        @continue(!isset($block['type'], $block['data']))

        @php
            /**
             * @var ?\VanOns\FilamentContentBuilder\Blocks\Contracts\Block $blockInstance
             */
            $blockInstance = \VanOns\FilamentContentBuilder\Facade\FilamentContentBuilder::getBlock($block['type'], $block['data']);

            if (!$blockInstance) {
                continue;
            }
        @endphp

        {!! $blockInstance->render() !!}

{{--        <x-dynamic-component :component="$component" :block="$block['data']" />--}}
    @endforeach
@endisset
