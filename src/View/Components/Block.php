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
        public ?bool $nested = false,
    ) {
    }

    public function getData(): array
    {
        $data = $this->data ?? [];
        return $this->nested
            ? [...$data, 'nested' => $this->nested]
            : $data;
    }

    public function render(): View|Closure|string
    {
        return FilamentContentBuilder::getBlock(
            Str::studly($this->block),
            $this->getData(),
        )?->render() ?? '';
    }
}
