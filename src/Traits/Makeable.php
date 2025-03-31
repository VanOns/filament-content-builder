<?php

namespace VanOns\FilamentContentBlocks\Traits;

trait Makeable
{
    /**
     * @param $attributes
     * @return static
     */
    public static function make(...$attributes): static
    {
        return new static(...$attributes);
    }
}
