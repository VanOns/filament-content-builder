<?php

namespace VanOns\FilamentContentBuilder\Blocks\Contracts;

use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use ReflectionNamedType;
use ReflectionProperty;
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
     * When null, Filament's default behaviour is kept.
     */
    public static ?bool $deferLoading = null;

    /**
     * @return array<\Filament\Schemas\Components\Component>|Schema
     */
    public static function schema(): array|Schema
    {
        throw new RuntimeException('Block schema not implemented');
    }

    public static function getSchema(): array|Schema
    {
        $schema = static::schema();

        if (static::$deferLoading === null) {
            return $schema;
        }

        if (is_array($schema)) {
            $schema = Schema::make()->components($schema);
        }

        if (!method_exists($schema, 'deferLoading')) {
            throw new RuntimeException(sprintf(
                'Unable to set $deferLoading on block [%s]: the installed Filament version does not support Schema::deferLoading().',
                static::class
            ));
        }

        $schema->deferLoading(static::$deferLoading);

        return $schema;
    }

    public function __construct(public array $data)
    {
        foreach ($this->data as $key => $value) {
            if (property_exists($this, $key) && static::acceptsPropertyValue($key, $value)) {
                $this->{$key} = $value;
            }
        }
    }

    // Stored data can be null or predate the current schema, so keep the default instead.
    protected static function acceptsPropertyValue(string $property, mixed $value): bool
    {
        static $types = [];

        $key = static::class . '::' . $property;

        if (!array_key_exists($key, $types)) {
            $types[$key] = (new ReflectionProperty(static::class, $property))->getType();
        }

        $type = $types[$key];

        if (!$type instanceof ReflectionNamedType) {
            return true;
        }

        if ($value === null) {
            return $type->allowsNull();
        }

        return match ($type->getName()) {
            'string' => is_string($value) || is_numeric($value),
            'int' => is_int($value) || (is_string($value) && ctype_digit($value)),
            'float' => is_float($value) || is_int($value) || is_numeric($value),
            'bool' => is_bool($value) || in_array($value, [0, 1, '0', '1'], true),
            'array' => is_array($value),
            default => true,
        };
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
            ->schema(fn (): array|Schema => static::getSchema());
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
