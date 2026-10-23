<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

/**
 * Blocks may have an SVG preview, shown when hovering the block in the block picker. It is read from
 * `resources/block-previews/{kebab-type}.svg`, built-in blocks fall back to the package's previews.
 *
 * @mixin Block
 */
trait HasPreview
{
    /**
     * @var array<class-string, string|Htmlable>
     */
    protected static array $pickerLabels = [];

    public static function preview(): ?string
    {
        $filename = Str::kebab(static::type()) . '.svg';

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
        $path = static::preview();

        return $path !== null && is_file($path);
    }

    public static function previewUrl(): ?string
    {
        if (!static::hasPreview()) {
            return null;
        }

        return route('filament-content-builder.block-preview', [
            'type' => static::type(),
            'v' => filemtime(static::preview()),
        ], absolute: false);
    }

    public static function pickerLabel(): string|Htmlable
    {
        return static::$pickerLabels[static::class] ??= static::makePickerLabel();
    }

    protected static function makePickerLabel(): string|Htmlable
    {
        if (!$url = static::previewUrl()) {
            return static::title();
        }

        [$width, $height] = static::previewSize();

        $image = sprintf(
            '<img src="%s" width="%d" height="%d" alt="" style="display: block; max-width: none">',
            e($url),
            $width,
            $height,
        );

        // Trigger and position on the whole picker item, so hovering its icon works too.
        $item = "(\$el.closest('.fi-dropdown-list-item') ?? \$el)";

        $tooltip = '{ content: ' . Js::from($image) . ", theme: \$store.theme, allowHTML: true, placement: 'right', maxWidth: 'none', "
            . "triggerTarget: {$item}, getReferenceClientRect: () => {$item}.getBoundingClientRect() }";

        return new HtmlString(sprintf(
            '<span x-tooltip="%s" style="display: block">%s</span>',
            e($tooltip),
            e(static::title()),
        ));
    }

    /**
     * Display size of the preview, scaled to a fixed width using the SVG's viewBox.
     *
     * @return array{int, int}
     */
    protected static function previewSize(int $width = 320): array
    {
        $svg = (string) file_get_contents((string) static::preview());

        if (preg_match('/viewBox\s*=\s*["\'][-\d.]+[\s,]+[-\d.]+[\s,]+([\d.]+)[\s,]+([\d.]+)/i', $svg, $matches) && (float) $matches[1] > 0) {
            return [$width, (int) round($width * (float) $matches[2] / (float) $matches[1])];
        }

        return [$width, (int) round($width * 0.6)];
    }
}
