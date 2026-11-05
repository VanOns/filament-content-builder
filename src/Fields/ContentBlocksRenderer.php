<?php

namespace VanOns\FilamentContentBuilder\Fields;

use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Field;
use VanOns\FilamentContentBuilder\Actions\BlockFieldAction;
use VanOns\FilamentContentBuilder\Actions\CopyBlockData;
use VanOns\FilamentContentBuilder\Actions\PasteBlockAction;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;
use VanOns\FilamentContentBuilder\Traits\HasContentBlocks;
use VanOns\FilamentContentBuilder\Traits\ResolvesBlockClass;

class ContentBlocksRenderer extends Builder
{
    use HasContentBlocks;
    use ResolvesBlockClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blocks(fn () => $this->getContentBlocks())
            ->contentBlocks(FilamentContentBuilder::getBlocks())
            ->extraItemActions([
                fn (): array => $this->getBlockActions(),
                CopyBlockData::make()->hidden(! config('filament-content-builder.copy_paste', true)),
            ])
            ->hintActions([
                PasteBlockAction::make()->hidden(! config('filament-content-builder.copy_paste', true)),
            ])
            ->collapsible()
            ->collapsed();
    }

    /**
     * @return array<Action>
     */
    public function getBlockActions(): array
    {
        $actions = [];

        foreach ($this->getContentBlockClasses() as $block) {
            foreach ($block::blockActions() as $action) {
                if ($action instanceof Field) {
                    $action = BlockFieldAction::forField($action);
                }

                $actions[] = $this->prepareBlockAction($action, $block::type());
            }
        }

        return $actions;
    }

    // Item actions are shared by all blocks, so each one gets a unique name and only shows for its own block.
    protected function prepareBlockAction(Action $action, string $type): Action
    {
        $original = (clone $action)->schemaComponent($this);
        $resolve = fn (Action $action): Action => $original->arguments($action->getArguments());

        return $action
            ->name("{$type}_{$action->getName()}")
            ->label(fn (Action $action) => $resolve($action)->getLabel())
            ->visible(function (Action $action, array $arguments, array $state) use ($resolve, $type): bool {
                $item = $this->getBlockItemData($arguments, $state);

                return ($item['type'] ?? null) === $type && $resolve($action)->isVisible();
            });
    }
}
