<?php

namespace VanOns\FilamentContentBlocks\Blocks\Contracts;

use Filament\Forms\Components\Component;
use Illuminate\Support\Str;
use RuntimeException;

abstract class Block
{
    /**
     * @return array<Component>
     */
    public static function schema(): array
    {
        throw new RuntimeException('Block schema not implemented');
    }

    public function __construct(public array $data)
    {
        foreach ($this->data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function render(): string
    {
        return view('filament-content-blocks::blocks.' . static::view(), ['block' => $this])->render();
    }

    public function toArray(): array
    {
        return [
            'type' => static::type(),
            'data' => $this->data,
        ];
    }

    public static function make(array $data): static
    {
        return app(static::class, ['data' => $data]);
    }

    public static function type(): string
    {
        return class_basename(static::class);
    }

    public static function title(): string
    {
        return Str::of(static::type())
            ->afterLast('\\')
            ->snake()
            ->replace('_', ' ')
            ->title();
    }

    public static function icon(): ?string
    {
        return null;
    }

    public static function view(): string
    {
        return Str::kebab(static::type());
    }

    public static function builderBlock(): \Filament\Forms\Components\Builder\Block
    {
        return \Filament\Forms\Components\Builder\Block::make(static::type())
            ->label(static::title())
            ->icon(static::icon())
            ->schema(static::schema());
    }

    public static function isNestable(): bool
    {
        return true;
    }
}
