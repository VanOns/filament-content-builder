@php
    use Filament\Support\Facades\FilamentAsset;
    use Filament\Support\Icons\Heroicon;
    use Filament\Tables\View\TablesIconAlias;

    $cards = array_map(function (array $item): array {
        $count = count($item['previews']);

        return [
            ...$item,
            'search' => mb_strtolower(implode(' ', array_filter([$item['label'], ...array_column($item['previews'], 'label')]))),
            'variants' => array_column($item['previews'], 'variant'),
            'variantLabels' => array_column($item['previews'], 'label'),
            'positions' => $count > 1
                ? array_map(fn (int $index): string => __('filament-content-builder-lang::gallery.position', [
                    'current' => $index + 1,
                    'total' => $count,
                ]), array_keys($item['previews']))
                : [],
        ];
    }, $items);
@endphp

<div
    x-data="{
        search: '',
        busy: false,
        matches(text) {
            const query = this.search.trim().toLowerCase()

            return query === '' || text.includes(query)
        },
        get hasResults() {
            return @js(array_column($cards, 'search')).some((text) => this.matches(text))
        },
        insert(block, variant) {
            if (this.busy) {
                return
            }

            this.busy = true

            this.$wire
                .callMountedAction(variant === null ? { block } : { block, variant })
                .finally(() => (this.busy = false))
        },
    }"
    x-load-css="[@js(FilamentAsset::getStyleHref('block-gallery', package: 'van-ons/filament-content-builder'))]"
    x-bind:class="{ 'fcb-block-gallery-busy': busy }"
    class="fcb-block-gallery"
>
    @if ($cards === [])
        <p class="fcb-block-gallery-message">
            {{ __('filament-content-builder-lang::gallery.empty') }}
        </p>
    @else
        <div x-id="['fcb-block-gallery-search']" class="fcb-block-gallery-search">
            <label x-bind:for="$id('fcb-block-gallery-search')" class="fi-sr-only">
                {{ __('filament-content-builder-lang::gallery.search') }}
            </label>

            <x-filament::input.wrapper
                inline-prefix
                :prefix-icon="Heroicon::MagnifyingGlass"
                :prefix-icon-alias="TablesIconAlias::SEARCH_FIELD"
            >
                <x-filament::input
                    inline-prefix
                    type="search"
                    autocomplete="off"
                    autofocus
                    maxlength="1000"
                    :placeholder="__('filament-content-builder-lang::gallery.search')"
                    x-model="search"
                    x-bind:id="$id('fcb-block-gallery-search')"
                />
            </x-filament::input.wrapper>
        </div>

        <div class="fcb-block-gallery-grid">
            @foreach ($cards as $card)
                @php
                    $count = count($card['previews']);
                @endphp

                <div
                    x-data="{
                        index: 0,
                        flipped: false,
                        flip(step) {
                            this.index = (this.index + step + {{ $count }}) % {{ $count }}
                            this.flipped = true
                        },
                    }"
                    x-show="matches(@js($card['search']))"
                    x-id="['fcb-block-gallery-card']"
                    class="fcb-block-gallery-card"
                >
                    <button
                        type="button"
                        x-on:click="insert(@js($card['name']), flipped ? @js($card['variants'])[index] : null)"
                        @if ($count > 1)
                            x-on:keydown.left.prevent="flip(-1)"
                            x-on:keydown.right.prevent="flip(1)"
                        @endif
                        x-bind:disabled="busy"
                        x-bind:aria-labelledby="$id('fcb-block-gallery-card')"
                        class="fcb-block-gallery-card-button"
                    ></button>

                    <div class="fcb-block-gallery-card-preview">
                        @forelse ($card['previews'] as $preview)
                            <img
                                src="{{ $preview['url'] }}"
                                alt=""
                                loading="lazy"
                                @if ($count > 1)
                                    x-show="index === {{ $loop->index }}"
                                    @unless ($loop->first) x-cloak @endunless
                                @endif
                            />
                        @empty
                            <x-filament::icon
                                :icon="filled($card['icon']) ? $card['icon'] : Heroicon::OutlinedSquares2x2"
                                class="fcb-block-gallery-card-fallback-icon"
                            />
                        @endforelse

                        @if ($count > 1)
                            <button
                                type="button"
                                x-on:click="flip(-1)"
                                x-bind:disabled="busy"
                                aria-label="{{ __('filament-content-builder-lang::gallery.previous') }}"
                                class="fcb-block-gallery-card-arrow fcb-block-gallery-card-arrow-previous"
                            >
                                <x-filament::icon icon="heroicon-m-chevron-left" />
                            </button>

                            <button
                                type="button"
                                x-on:click="flip(1)"
                                x-bind:disabled="busy"
                                aria-label="{{ __('filament-content-builder-lang::gallery.next') }}"
                                class="fcb-block-gallery-card-arrow fcb-block-gallery-card-arrow-next"
                            >
                                <x-filament::icon icon="heroicon-m-chevron-right" />
                            </button>

                            <span
                                aria-hidden="true"
                                x-text="(index + 1) + '/{{ $count }}'"
                                class="fcb-block-gallery-card-counter"
                            >1/{{ $count }}</span>
                        @endif
                    </div>

                    <div x-bind:id="$id('fcb-block-gallery-card')" class="fcb-block-gallery-card-content">
                        <span class="fcb-block-gallery-card-label">
                            @if (filled($card['icon']))
                                <x-filament::icon :icon="$card['icon']" class="fcb-block-gallery-card-label-icon" />
                            @endif

                            {{ $card['label'] }}
                        </span>

                        @if ($count > 1)
                            <span aria-live="polite" aria-atomic="true" class="fcb-block-gallery-card-variant">
                                <span x-text="@js($card['variantLabels'])[index]">{{ $card['variantLabels'][0] }}</span>
                                <span x-text="@js($card['positions'])[index]" class="fi-sr-only">{{ $card['positions'][0] }}</span>
                            </span>
                        @elseif (filled($card['variantLabels'][0] ?? null))
                            <span class="fcb-block-gallery-card-variant">{{ $card['variantLabels'][0] }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <p
            x-cloak
            x-show="! hasResults"
            role="status"
            aria-live="polite"
            class="fcb-block-gallery-message"
        >
            {{ __('filament-content-builder-lang::gallery.no_results') }}
        </p>
    @endif
</div>
