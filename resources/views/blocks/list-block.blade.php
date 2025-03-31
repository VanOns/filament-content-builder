@php
    /**
     * @var \VanOns\FilamentContentBlocks\Blocks\ListBlock $block
     */

    $listEl = match ($block->type) {
        'ordered' => 'ol',
        default => 'ul',
    };
@endphp

<section>
    @if(!empty($title = $block->title))
        <h2>{{ $title }}</h2>
    @endif

    @if(!empty($items = $block->items))
        <{{ $listEl }}>
            @foreach($items as $item)
                <li>{{ $item['text'] }}</li>
            @endforeach
        </{{ $listEl }}>
    @endif
</section>
