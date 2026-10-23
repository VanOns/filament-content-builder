@php
    /**
     * @var list<array{label: string, icon: ?string, previews: list<array{url: string, label: ?string, width: int, height: int}>}> $items
     */

    $buttonStyle = 'opacity: 0; transition: opacity 150ms; position: absolute; top: 50%; transform: translateY(-50%); display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 9999px; background: rgb(255 255 255 / 0.9); color: rgb(17 24 39); box-shadow: 0 1px 3px rgb(0 0 0 / 0.3)';
@endphp

<div style="overflow: hidden; border: 1px solid rgb(127 127 127 / 0.25); border-radius: 0.5rem">
    @foreach ($items as $item)
        @php
            $count = count($item['previews']);
        @endphp

        <div
            title="{{ $item['label'] }}"
            x-data="{ variant: 0, active: false }"
            x-on:mouseenter="active = true"
            x-on:mouseleave="active = false"
            x-on:focusin="active = true"
            x-on:focusout="active = false"
            style="position: relative"
        >
            @forelse ($item['previews'] as $index => $preview)
                <div x-show="variant === {{ $index }}" @if ($index > 0) style="display: none" @endif>
                    <img src="{{ $preview['url'] }}" alt="{{ $item['label'] }}" width="{{ $preview['width'] }}" height="{{ $preview['height'] }}" loading="lazy" style="display: block; width: 100%; height: auto">

                    @if ($count > 1)
                        <span x-show="active" style="display: none; position: absolute; bottom: 0.5rem; left: 50%; transform: translateX(-50%); padding: 0.125rem 0.5rem; border-radius: 9999px; background: rgb(17 24 39 / 0.75); color: white; font-size: 0.75rem; white-space: nowrap">
                            {{ $preview['label'] }} · {{ $index + 1 }}/{{ $count }}
                        </span>
                    @endif
                </div>
            @empty
                <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 1.25rem; border-block: 1px dashed rgb(127 127 127 / 0.4); color: rgb(127 127 127); font-size: 0.875rem">
                    @if ($item['icon'])
                        <x-filament::icon :icon="$item['icon']" />
                    @endif

                    {{ $item['label'] }} · {{ __('filament-content-builder-lang::fields.preview_missing') }}
                </div>
            @endforelse

            @if ($count > 1)
                <button
                    type="button"
                    x-on:click="variant = (variant + {{ $count - 1 }}) % {{ $count }}"
                    x-bind:style="{ opacity: active ? 1 : 0 }"
                    aria-label="{{ __('filament-content-builder-lang::fields.previous_variant') }}"
                    style="{{ $buttonStyle }}; left: 0.5rem"
                >
                    <x-filament::icon icon="heroicon-m-chevron-left" />
                </button>

                <button
                    type="button"
                    x-on:click="variant = (variant + 1) % {{ $count }}"
                    x-bind:style="{ opacity: active ? 1 : 0 }"
                    aria-label="{{ __('filament-content-builder-lang::fields.next_variant') }}"
                    style="{{ $buttonStyle }}; right: 0.5rem"
                >
                    <x-filament::icon icon="heroicon-m-chevron-right" />
                </button>
            @endif
        </div>
    @endforeach
</div>
