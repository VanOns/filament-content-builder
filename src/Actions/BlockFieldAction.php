<?php

namespace VanOns\FilamentContentBuilder\Actions;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Field;
use Illuminate\Support\Arr;

/**
 * Renders a field in the block's toolbar, stored in the block's data under the field's name.
 */
class BlockFieldAction extends BlockAction
{
    protected Field $field;

    public static function forField(Field $field): static
    {
        return static::make(str_replace('.', '_', $field->getName()))->field($field);
    }

    public function field(Field $field): static
    {
        $this->field = $field;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->visible(fn (BlockFieldAction $action): bool => $action->getItemField()?->isVisible() ?? false);
    }

    // The field isn't part of the block's schema, so it's bound to the item's schema to use the item's state path.
    public function getItemField(): ?Field
    {
        $builder = $this->getSchemaComponent();
        $item = $this->getArguments()['item'] ?? null;

        if (! $builder instanceof Builder || $item === null || ! ($schema = $builder->getChildSchema($item))) {
            return null;
        }

        return $this->field->getClone()->container($schema);
    }

    public function toHtml(): string
    {
        $field = $this->getItemField();

        if (! $field) {
            return '';
        }

        // Livewire can only bind to keys that exist, so a missing value starts at the field's default.
        if (! Arr::has($this->getBlockData(), $this->field->getName())) {
            $field->state($field->getDefaultState());
        }

        // The toolbar's list items shrink to the size of an icon button, so the field keeps its own width.
        return '<div style="width: max-content">' . $field->toHtml() . '</div>';
    }
}
