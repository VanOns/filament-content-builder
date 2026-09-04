@if ($usage['records'] === [])
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ __('filament-content-builder-lang::usage.empty') }}
    </p>
@else
    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="py-2 pe-4 text-start font-semibold text-gray-950 dark:text-white">{{ __('filament-content-builder-lang::usage.model') }}</th>
                <th class="py-2 pe-4 text-start font-semibold text-gray-950 dark:text-white">{{ __('filament-content-builder-lang::usage.record') }}</th>
                <th class="py-2 text-start font-semibold text-gray-950 dark:text-white">{{ __('filament-content-builder-lang::usage.count') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usage['records'] as $record)
                <tr class="border-t border-gray-200 dark:border-white/10">
                    <td class="py-4 pe-4 text-gray-500 dark:text-gray-400">{{ $record['model_label'] }}</td>
                    <td class="py-4 pe-4">
                        @if ($record['url'])
                            <a href="{{ $record['url'] }}" class="font-medium text-primary-600 hover:underline dark:text-primary-400">
                                {{ $record['title'] }}
                            </a>
                        @else
                            <span class="font-medium text-gray-950 dark:text-white">{{ $record['title'] }}</span>
                        @endif
                    </td>
                    <td class="py-4">{{ $record['count'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
