<?php

namespace VanOns\FilamentContentBuilder\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

class Block extends Component
{
    public function __construct(
        public string $block,
        public ?array $data = [],
    ) {}

    public function render(): View|Closure|string
    {
        return FilamentContentBuilder::getBlock(
            Str::studly($this->block),
            $this->data ?? []
        )?->render() ?? '';
    }
}
