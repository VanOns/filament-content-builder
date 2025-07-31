@props([
    'blocks' => [],
    'nested' => false,
])

@isset($blocks)
    @foreach($blocks as $block)
        @continue(!isset($block['type'], $block['data']))

        <x-filament-content-builder::block :block="$block['type']" :data="$block['data']" :nested="$nested" />
    @endforeach
@endisset
