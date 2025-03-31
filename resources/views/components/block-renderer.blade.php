@props([
    'blocks' => [],
])

@isset($blocks)
    @foreach($blocks as $block)
        @continue(!isset($block['type'], $block['data']))

        @php
            /**
             * @var ?\VanOns\FilamentContentBlocks\Blocks\Contracts\Block $blockInstance
             */
            $blockInstance = \VanOns\FilamentContentBlocks\Facade\FilamentContentBlocks::getBlock($block['type'], $block['data']);

            if (!$blockInstance) {
                continue;
            }
        @endphp

        {!! $blockInstance->render() !!}

{{--        <x-dynamic-component :component="$component" :block="$block['data']" />--}}
    @endforeach
@endisset
