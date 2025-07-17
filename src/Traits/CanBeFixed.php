<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Filament\Forms\Components\Field;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

/**
 * @mixin Block
 */
trait CanBeFixed
{
    /** @deprecated Will be removed in future versions. Use `::schema()` directly instead (to change statepath, wrap in a container and use `statePath()`). */
    public static function getFields(string $prefix = ''): array
    {
        /** @var Field[] $schema */
        $schema = static::schema();

        foreach ($schema as $field) {
            $name = $prefix . '.' . $field->getName();
            $field->name($name);
            $field->statePath($name);
        }

        return $schema;
    }
}
