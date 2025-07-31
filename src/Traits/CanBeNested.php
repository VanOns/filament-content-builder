<?php

namespace VanOns\FilamentContentBuilder\Traits;

use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

/**
 * @mixin Block
 */
trait CanBeNested
{
    public ?bool $nested = false;
}
