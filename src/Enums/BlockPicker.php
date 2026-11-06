<?php

namespace VanOns\FilamentContentBuilder\Enums;

enum BlockPicker: string
{
    case Dropdown = 'dropdown';

    case Gallery = 'gallery';

    case Both = 'both';

    public static function resolve(self|string|null $picker): self
    {
        return $picker instanceof self ? $picker : (self::tryFrom((string) $picker) ?? self::Dropdown);
    }
}
