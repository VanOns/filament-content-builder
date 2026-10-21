<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Closure;
use Filament\Forms\Components\Builder\Block as BuilderBlock;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

/**
 * Blocks may define a static `visible()` and/or `hidden()` method to control whether they can be
 * picked in a builder. Both are evaluated like a Filament closure in the context of the builder
 * field, so every Filament injection is available (`$get`, `$record`, `$model`, `$operation`, ...).
 *
 * @mixin Block
 */
trait HasVisibility
{
    public static function isVisibleIn(BuilderBlock $component): bool
    {
        if (method_exists(static::class, 'visible') && !static::evaluateInBuilder($component, 'visible')) {
            return false;
        }

        return !(method_exists(static::class, 'hidden') && static::evaluateInBuilder($component, 'hidden'));
    }

    protected static function evaluateInBuilder(BuilderBlock $component, string $method): bool
    {
        $builder = $component->getContainer()->getParentComponent() ?? $component;

        return (bool) $builder->evaluate(Closure::fromCallable([static::class, $method]));
    }
}
