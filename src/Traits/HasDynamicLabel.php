<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

/**
 * @mixin Block
 */
trait HasDynamicLabel
{
    public static function getLabelField(): ?string
    {
        return static::$labelField ?? null;
    }

    public static function label(mixed $state): ?string
    {
        if (is_null($state) || is_null(static::getLabelField())) {
            return null;
        }

        return !empty($label = Arr::get($state, static::getLabelField()))
            ? html_entity_decode(strip_tags($label))
            : null;
    }

    public static function getLabel($state): ?string
    {
        return ($label = static::label($state))
            ? Str::limit($label, 30) . ' - ' . static::title()
            : static::title();
    }
}
