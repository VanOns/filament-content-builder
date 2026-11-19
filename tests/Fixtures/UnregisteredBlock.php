<?php

namespace Tests\Fixtures;

use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

/**
 * A block that is not in the config, as when it is only given to a builder with `contentBlocks()`.
 */
class UnregisteredBlock extends Block
{
    public static function schema(): array
    {
        return [];
    }
}
