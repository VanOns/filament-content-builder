<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

/**
 * Blocks may have an SVG preview, shown when hovering the block in the block picker or a collapsed
 * builder item. It is read from `resources/block-previews/{kebab-type}.svg`, built-in blocks fall
 * back to the package's previews. Blocks with layout variants get a preview per variant.
 *
 * @mixin Block
 */
trait HasPreview
{
    /**
     * @var array<class-string, list<array{variant: ?string, label: ?string}>>
     */
    protected static array $resolvedPreviews = [];

    /**
     * @var array<string, string>
     */
    protected static array $previewTooltips = [];

    /**
     * Layout variants that each get their own preview, as `key => label`. Their previews are read
     * from `{kebab-type}.{key}.svg` instead of `{kebab-type}.svg`.
     *
     * @return array<string, string>
     */
    public static function previewVariants(): array
    {
        return [];
    }

    /**
     * The variant matching an item's data, previewed when hovering the collapsed item. Falls back
     * to the first variant.
     */
    public static function previewVariantFor(array $data): ?string
    {
        return null;
    }

    /**
     * Absolute path to the preview SVG of the block or one of its variants, or null when there is none.
     */
    public static function preview(?string $variant = null): ?string
    {
        $filename = Str::kebab(static::type()) . ($variant === null ? '' : ".{$variant}") . '.svg';

        $paths = [resource_path("block-previews/{$filename}")];

        if (static::isPackageBlock()) {
            $paths[] = dirname(__DIR__, 2) . "/resources/previews/{$filename}";
        }

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    public static function hasPreview(): bool
    {
        return static::previews() !== [];
    }

    /**
     * The previews that exist, in variant order.
     *
     * @return list<array{variant: ?string, label: ?string}>
     */
    public static function previews(): array
    {
        return static::$resolvedPreviews[static::class] ??= static::resolvePreviews();
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

        return $path !== null && is_file($path) ? $path : null;
    }

    public static function previewUrl(?string $variant = null): ?string
    {
        if (!$path = static::previewPath($variant)) {
            return null;
        }

        return route('filament-content-builder.block-preview', array_filter([
            'type' => static::type(),
            'variant' => $variant,
            'v' => filemtime($path),
        ], fn (mixed $value) => $value !== null), absolute: false);
    }

    protected static function pickerLabel(): string|Htmlable
    {
        return static::withPreviewTooltip(static::title(), static::previews(), picker: true);
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

        $key = static::class . ($picker ? '|picker|' : '|item|') . implode(',', array_column($previews, 'variant'));

        return new HtmlString(sprintf(
            '<span x-tooltip="%s">%s</span>',
            e(static::$previewTooltips[$key] ??= static::makePreviewTooltip($previews, $picker)),
            e($label),
        ));
    }

    /**
     * @param list<array{variant: ?string, label: ?string}> $previews
     */
    protected static function makePreviewTooltip(array $previews, bool $picker): string
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

        $props = $picker
            // Pinned next to the dropdown panel, triggered by the whole item including its icon.
            ? "placement: 'right-start', arrow: false, offset: [0, 8], duration: 0, "
                . "triggerTarget: \$el.closest('.fi-dropdown-list-item') ?? \$el, "
                . "getReferenceClientRect: () => (\$el.closest('.fi-dropdown-panel') ?? \$el).getBoundingClientRect()"
            // Below the item's label and icon, only while the item is collapsed.
            : "placement: 'bottom-start', delay: [400, 0], "
                . "triggerTarget: [\$el.closest('.fi-fo-builder-item-header-label') ?? \$el, \$el.closest('.fi-fo-builder-item-header')?.querySelector('.fi-fo-builder-item-header-icon')].filter(Boolean), "
                . "getReferenceClientRect: () => (\$el.closest('.fi-fo-builder-item-header-label') ?? \$el).getBoundingClientRect(), "
                . "onShow: () => !!\$el.closest('.fi-fo-builder-item')?.classList.contains('fi-collapsed')";

        return '{ content: ' . Js::from('<div style="display: grid; gap: 0.5rem">' . $content . '</div>')
            . ", theme: \$store.theme, allowHTML: true, maxWidth: 'none', {$props} }";
    }

    /**
     * Display size of a preview, scaled to the given width using the SVG's viewBox.
     *
     * @return array{int, int}
     */
    protected static function previewSize(?string $variant, int $width): array
    {
        $svg = (string) file_get_contents((string) static::previewPath($variant));

        if (preg_match('/viewBox\s*=\s*["\'][-\d.]+[\s,]+[-\d.]+[\s,]+([\d.]+)[\s,]+([\d.]+)/i', $svg, $matches) && (float) $matches[1] > 0) {
            return [$width, (int) round($width * (float) $matches[2] / (float) $matches[1])];
        }

        return [$width, (int) round($width * 0.6)];
    }
}
