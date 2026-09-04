<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Filament\Forms\Components\Field;
use Filament\Schemas\Schema;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

/**
 * @mixin Block
 */
trait CanBeFixed
{
    /**
     * @deprecated Will be removed in future versions. Use `::schema()` directly instead (to change statepath, wrap in a container and use `statePath()`).
     */
    public static function getFields(string $prefix = ''): array
    {
        $schema = static::schema();

        if ($schema instanceof Schema) {
            $schema = $schema->getComponents();
        }

        /** @var Field[] $schema */

        foreach ($schema as $field) {
            $name = $prefix . '.' . $field->getName();
            $field->name($name);
            $field->statePath($name);
        }

        return $schema;
    }
}
