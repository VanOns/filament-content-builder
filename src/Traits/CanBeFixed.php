<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Filament\Forms\Components\Field;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

/**
 * @mixin Block
 */
trait CanBeFixed
{
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
