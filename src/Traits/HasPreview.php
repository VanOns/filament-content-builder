<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

/**
 * @mixin Block
 */
trait HasPreview
{
    /**
     * Supported preview formats, in lookup order.
     */
    public const PREVIEW_MIME_TYPES = [
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
    ];

    /**
     * @var array<string, list<array{variant: ?string, label: ?string}>>
     */
    protected static array $resolvedPreviews = [];

    /**
     * @var array<string, string>
     */
    protected static array $previewTooltips = [];

    /**
     * @return array<string, string> key => label
     */
    public static function previewVariants(): array
    {
        return [];
    }

    /**
     * Falls back to the first variant when null.
     */
    public static function previewVariantFor(array $data): ?string
    {
        return null;
    }

    /**
     * Absolute path to the preview image.
     */
    public static function preview(?string $variant = null): ?string
    {
        $name = Str::kebab(static::type()) . ($variant === null ? '' : ".{$variant}");

        $directories = [resource_path('block-previews')];

        if (static::isPackageBlock()) {
            $directories[] = dirname(__DIR__, 2) . '/resources/previews';
        }

        foreach ($directories as $directory) {
            foreach (array_keys(static::PREVIEW_MIME_TYPES) as $extension) {
                if (is_file($path = "{$directory}/{$name}.{$extension}")) {
                    return $path;
                }
            }
        }

        return null;
    }

    public static function hasPreview(): bool
    {
        return static::previews() !== [];
    }

    /**
     * @return list<array{variant: ?string, label: ?string}>
     */
    public static function previews(): array
    {
        return static::$resolvedPreviews[static::class . '|' . app()->getLocale()] ??= static::resolvePreviews();
    }

    /**
     * @return list<array{variant: ?string, label: ?string}>
     */
    protected static function resolvePreviews(): array
    {
        $variants = static::previewVariants();

        if ($variants === []) {
            return static::previewPath() ? [['variant' => null, 'label' => null]] : [];
        }

        $previews = [];

        foreach ($variants as $variant => $label) {
            if (static::previewPath((string) $variant)) {
                $previews[] = ['variant' => (string) $variant, 'label' => $label];
            }
        }

        return $previews;
    }

    protected static function previewPath(?string $variant = null): ?string
    {
        $path = static::preview($variant);

        return $path !== null && is_file($path) && static::previewMimeType($path) ? $path : null;
    }

    public static function previewMimeType(string $path): ?string
    {
        return static::PREVIEW_MIME_TYPES[strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? null;
    }

    public static function previewUrl(?string $variant = null): ?string
    {
        if (!$path = static::previewPath($variant)) {
            return null;
        }

        // Relative routes lack the base path.
        return request()->getBaseUrl() . route('filament-content-builder.block-preview', array_filter([
            'type' => static::type(),
            'variant' => $variant,
            'v' => substr((string) md5_file($path), 0, 12),
        ], fn (mixed $value) => $value !== null), absolute: false);
    }

    protected static function pickerLabel(string $label): string|Htmlable
    {
        return static::withPreviewTooltip($label, static::previews(), picker: true);
    }

    protected static function itemLabel(string $label, array $data): string|Htmlable
    {
        $previews = static::previews();
        $variant = static::previewVariantFor($data);

        $preview = Arr::first($previews, fn (array $preview) => $preview['variant'] === $variant) ?? Arr::first($previews);

        return static::withPreviewTooltip($label, $preview ? [$preview] : [], picker: false);
    }

    /**
     * @param list<array{variant: ?string, label: ?string}> $previews
     */
    protected static function withPreviewTooltip(string $label, array $previews, bool $picker): string|Htmlable
    {
        if ($previews === []) {
            return $label;
        }

        $position = $picker ? config('filament-content-builder.preview_position', 'tooltip') : 'item';

        $key = implode('|', [static::class, $position, app()->getLocale(), ...array_column($previews, 'variant')]);

        return new HtmlString(sprintf(
            '<span x-tooltip="%s">%s</span>',
            e(static::$previewTooltips[$key] ??= static::makePreviewTooltip($previews, $position)),
            e($label),
        ));
    }

    /**
     * @param list<array{variant: ?string, label: ?string}> $previews
     */
    protected static function makePreviewTooltip(array $previews, string $position): string
    {
        $content = '';

        foreach ($previews as $preview) {
            [$width, $height] = static::previewSize($preview['variant'], count($previews) > 1 ? 240 : 320);

            $content .= '<figure style="margin: 0">'
                . ($preview['label'] ? '<figcaption style="margin-bottom: 0.25rem; font-size: 0.75rem">' . e($preview['label']) . '</figcaption>' : '')
                . sprintf(
                    '<img src="%s" width="%d" height="%d" alt="" style="display: block; max-width: none">',
                    e((string) static::previewUrl($preview['variant'])),
                    $width,
                    $height,
                )
                . '</figure>';
        }

        $pickerItem = "(\$el.closest('.fi-dropdown-list-item') ?? \$el)";

        $props = match ($position) {
            'item' => "placement: 'bottom-start', delay: [400, 0], "
                . "triggerTarget: [\$el.closest('.fi-fo-builder-item-header-label') ?? \$el, \$el.closest('.fi-fo-builder-item-header')?.querySelector('.fi-fo-builder-item-header-icon')].filter(Boolean), "
                . "getReferenceClientRect: () => (\$el.closest('.fi-fo-builder-item-header-label') ?? \$el).getBoundingClientRect(), "
                . "onShow: () => !!\$el.closest('.fi-fo-builder-item')?.classList.contains('fi-collapsed')",
            'panel' => "placement: 'right-start', arrow: false, offset: [0, 8], duration: 0, triggerTarget: {$pickerItem}, "
                . "getReferenceClientRect: () => (\$el.closest('.fi-dropdown-panel') ?? \$el).getBoundingClientRect()",
            default => "placement: 'right', triggerTarget: {$pickerItem}, "
                . "getReferenceClientRect: () => {$pickerItem}.getBoundingClientRect()",
        };

        return '{ content: ' . Js::from('<div style="display: grid; gap: 0.5rem">' . $content . '</div>')
            . ", theme: \$store.theme, allowHTML: true, maxWidth: 'none', {$props} }";
    }

    /**
     * @return array{int, int}
     */
    protected static function previewSize(?string $variant, int $width): array
    {
        $path = (string) static::previewPath($variant);
        [$sourceWidth, $sourceHeight] = [0, 0];

        if (static::previewMimeType($path) !== 'image/svg+xml') {
            [$sourceWidth, $sourceHeight] = @getimagesize($path) ?: [0, 0];
        } elseif (preg_match('/viewBox\s*=\s*["\'][-\d.]+[\s,]+[-\d.]+[\s,]+([\d.]+)[\s,]+([\d.]+)/i', (string) file_get_contents($path), $matches)) {
            [$sourceWidth, $sourceHeight] = [(float) $matches[1], (float) $matches[2]];
        }

        return $sourceWidth > 0
            ? [$width, (int) round($width * $sourceHeight / $sourceWidth)]
            : [$width, (int) round($width * 0.6)];
    }
}
