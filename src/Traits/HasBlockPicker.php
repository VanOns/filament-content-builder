<?php

namespace VanOns\FilamentContentBuilder\Traits;

use Closure;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block as BuilderBlock;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;
use VanOns\FilamentContentBuilder\Enums\BlockPicker;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;
use VanOns\FilamentContentBuilder\FilamentContentBuilderPlugin;

/**
 * Adds a gallery that shows every block with its previews, instead of or next to the block picker dropdown.
 *
 * @mixin Builder
 */
trait HasBlockPicker
{
    /**
     * Name of the dropdown entry that opens the gallery. It's never a real block, so it can't be inserted.
     */
    public const GALLERY_BLOCK = '__block_gallery';

    protected BlockPicker|string|Closure|null $blockPicker = null;

    /**
     * Defaults to the plugin's `blockPicker()`, then the `block_picker` config value.
     */
    public function blockPicker(BlockPicker|string|Closure $picker): static
    {
        $this->blockPicker = $picker;

        return $this;
    }

    public function getBlockPicker(): BlockPicker
    {
        // Filament's own block previews open the block's form when adding.
        if ($this->hasBlockPreviews()) {
            return BlockPicker::Dropdown;
        }

        return BlockPicker::resolve($this->evaluate($this->blockPicker)
            ?? static::getContentBuilderPlugin()?->getBlockPicker()
            ?? config('filament-content-builder.block_picker'));
    }

    public function getBlockPickerBlocks(): array
    {
        $blocks = parent::getBlockPickerBlocks();

        return match ($this->getBlockPicker()) {
            BlockPicker::Dropdown => $blocks,
            // Keeps Filament from rendering its dropdown around the add buttons.
            BlockPicker::Gallery => [],
            BlockPicker::Both => $blocks === [] ? [] : [...$blocks, $this->getBlockGalleryLink()],
        };
    }

    public function getAddAction(): Action
    {
        return $this->configureBlockGalleryAction(parent::getAddAction());
    }

    public function getAddBetweenAction(): Action
    {
        return $this->configureBlockGalleryAction(parent::getAddBetweenAction());
    }

    /**
     * @return list<array{name: string, label: string, icon: mixed, class: class-string<Block>|null, previews: list<array{variant: ?string, label: ?string, url: string}>}>
     */
    public function getBlockGalleryItems(): array
    {
        $classes = $this->evaluate($this->contentBlocks);

        return array_map(
            fn (BuilderBlock $block): array => $this->makeBlockGalleryItem($block, $classes),
            array_values(parent::getBlockPickerBlocks()),
        );
    }

    /**
     * @return array{name: string, label: string, icon: mixed, class: class-string<Block>|null, previews: list<array{variant: ?string, label: ?string, url: string}>}|null
     */
    public function getBlockGalleryItem(mixed $name): ?array
    {
        $block = Arr::first(parent::getBlockPickerBlocks(), fn (BuilderBlock $block): bool => $block->getName() === $name);

        return $block ? $this->makeBlockGalleryItem($block, $this->evaluate($this->contentBlocks)) : null;
    }

    /**
     * @param array<Block|string>|null $classes
     * @return array{name: string, label: string, icon: mixed, class: class-string<Block>|null, previews: list<array{variant: ?string, label: ?string, url: string}>}
     */
    protected function makeBlockGalleryItem(BuilderBlock $block, ?array $classes): array
    {
        $class = FilamentContentBuilder::getBlockClass($block->getName(), $classes);
        $previews = [];

        foreach ($class ? $class::previews() : [] as $preview) {
            if ($url = $class::previewUrl($preview['variant'])) {
                $previews[] = [...$preview, 'url' => $url];
            }
        }

        $label = $class ? $class::title() : $block->getLabel();

        return [
            'name' => $block->getName(),
            'label' => $label instanceof Htmlable ? strip_tags($label->toHtml()) : (string) $label,
            'icon' => $block->getIcon(),
            'class' => $class,
            'previews' => $previews,
        ];
    }

    protected function getBlockGalleryLink(): BuilderBlock
    {
        return BuilderBlock::make(static::GALLERY_BLOCK)
            ->label(new HtmlString(view('filament-content-builder::components.block-picker-gallery-link')->render()));
    }

    protected function configureBlockGalleryAction(Action $action): Action
    {
        $picker = $this->getBlockPicker();

        if ($picker === BlockPicker::Dropdown) {
            return $action;
        }

        if ($picker === BlockPicker::Gallery) {
            $action
                ->livewireClickHandlerEnabled()
                // Filament wraps the button in its dropdown, which would catch the click and the keyboard.
                ->extraAttributes([
                    'x-on:mousedown.stop' => '',
                    'x-on:keydown.enter.stop' => '',
                    'x-on:keydown.space.stop' => '',
                ], merge: true);
        }

        $insert = $action->getActionFunction();

        return $action
            // Blocks picked in the dropdown are inserted right away, the gallery opens without one.
            ->modalHidden(fn (array $arguments): bool => !in_array($arguments['block'] ?? null, [null, static::GALLERY_BLOCK], true))
            ->modalHeading(fn (Builder $component) => __('filament-forms::components.builder.actions.add.modal.heading', [
                'label' => $component->getLabel(),
            ]))
            ->modalWidth(Width::SevenExtraLarge)
            ->modalSubmitAction(false)
            ->modalContent(fn (self $component): View => view('filament-content-builder::components.block-gallery', [
                'items' => $component->getBlockGalleryItems(),
            ]))
            ->action(function (Action $action, array $arguments, self $component) use ($insert): void {
                $item = $component->getBlockGalleryItem($arguments['block'] ?? null);

                if (!$item || !$insert) {
                    return;
                }

                $keys = array_keys($component->getRawState() ?? []);

                $action->evaluate($insert);

                $variant = $arguments['variant'] ?? null;

                if (!is_string($variant) || !$item['class'] || !in_array($variant, array_column($item['previews'], 'variant'), true)) {
                    return;
                }

                $data = $item['class']::previewVariantData($variant);
                $key = Arr::first(array_diff(array_keys($component->getRawState() ?? []), $keys));

                if ($data !== [] && $key !== null) {
                    // Filled after the insert, so the block's other defaults are kept.
                    $component->getChildSchema($key)->fillPartially($data, array_keys($data));
                }
            });
    }

    protected static function getContentBuilderPlugin(): ?FilamentContentBuilderPlugin
    {
        if (!Filament::getCurrentPanel()?->hasPlugin('filament-content-builder')) {
            return null;
        }

        $plugin = filament('filament-content-builder');

        return $plugin instanceof FilamentContentBuilderPlugin ? $plugin : null;
    }
}
