<?php

namespace VanOns\FilamentContentBuilder\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Schemas\Schema;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;
use VanOns\FilamentContentBuilder\Fields\ContentBlocksRenderer;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

class BlockAction extends Action
{
    public function storesInBlockData(): static
    {
        return $this
            ->fillForm(fn (BlockAction $action): array => $action->getBlockData())
            ->action(fn (BlockAction $action, array $data) => $action->mergeBlockData($data));
    }

    public function getBlockData(): array
    {
        return $this->getBlockItem()['data'] ?? [];
    }

    public function getBlockType(): ?string
    {
        return $this->getBlockItem()['type'] ?? null;
    }

    /**
     * @return class-string<Block>|null
     */
    public function getBlockClass(): ?string
    {
        $builder = $this->getSchemaComponent();

        return FilamentContentBuilder::getBlockClass(
            $this->getBlockType(),
            $builder instanceof ContentBlocksRenderer ? $builder->getContentBlockClasses() : null,
        );
    }

    public function setBlockData(array $data): void
    {
        $this->updateBlockSchema(fn (Schema $schema) => $schema->fill($data));
    }

    public function mergeBlockData(array $data): void
    {
        $this->updateBlockSchema(fn (Schema $schema) => $schema->fillPartially($data, array_keys($data)));
    }

    // Filling the item's schema hydrates its fields, so nested fields such as repeaters get their state in the right format.
    protected function updateBlockSchema(callable $callback): void
    {
        $builder = $this->getSchemaComponent();

        if (! $builder instanceof Builder || ! $this->getBlockItem()) {
            return;
        }

        $schema = $builder->getChildSchema($this->getArguments()['item']);

        if (! $schema) {
            return;
        }

        $callback($schema);

        $builder->callAfterStateUpdated();
    }

    protected function getBlockItem(): ?array
    {
        $builder = $this->getSchemaComponent();
        $item = $this->getArguments()['item'] ?? null;

        if (! $builder instanceof Builder || $item === null) {
            return null;
        }

        return $builder->getRawState()[$item] ?? null;
    }

    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'blockData' => [$this->getBlockData()],
            'blockType' => [$this->getBlockType()],
            'blockClass' => [$this->getBlockClass()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }
}
