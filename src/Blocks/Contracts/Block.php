<?php

namespace VanOns\FilamentContentBuilder\Blocks\Contracts;

use Illuminate\Support\Str;
use RuntimeException;
use VanOns\FilamentContentBuilder\Traits\CanBeFixed;
use VanOns\FilamentContentBuilder\Traits\CanBeNested;
use VanOns\FilamentContentBuilder\Traits\HasDynamicLabel;
use VanOns\FilamentContentBuilder\Traits\HasSettings;

abstract class Block
{
    use CanBeFixed;
    use HasDynamicLabel;
    use CanBeNested;
    use HasSettings;

    public int $blockIndex = 0;
    public static ?string $labelField = null;

    /**
     * @return array<\Filament\Schemas\Components\Component>
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
        return view(static::view(), ['block' => $this])->render();
    }

    public function toArray(): array
    {
        return [
            'type' => static::type(),
            'data' => $this->data,
        ];
    }

    public function parseArray(): array
    {
        return [
            'type' => static::type(),
            'data' => $this->parseData(),
        ];
    }

    public function parseData(): array
    {
        return $this->data;
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
        return str_contains(static::class, 'VanOns\FilamentContentBuilder\Blocks')
            ? 'filament-content-builder::blocks.' . Str::kebab(static::type())
            : 'blocks.' . Str::kebab(static::type());
    }

    public static function builderBlock(): \Filament\Forms\Components\Builder\Block
    {
        return \Filament\Forms\Components\Builder\Block::make(static::type())
            ->label(fn (mixed $state) => static::getLabel($state) ?? static::title())
            ->icon(fn () => static::icon())
            ->schema(fn () => static::schema());
    }

    /**
     * Convert the block to plain text, this can be useful for things like SEO analysis.
     * @return string|null
     */
    public function toText(): ?string
    {
        return null;
    }
}
