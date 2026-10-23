<?php

namespace VanOns\FilamentContentBuilder\Actions;

use Filament\Actions\Action;
use VanOns\FilamentContentBuilder\Fields\ContentBlocksRenderer;

class PreviewPageAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'previewPage';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-content-builder-lang::fields.preview_page'))
            ->icon('heroicon-o-eye')
            ->modalHeading(__('filament-content-builder-lang::fields.preview_page_heading'))
            ->modalDescription(__('filament-content-builder-lang::fields.preview_page_description'))
            ->modalWidth('3xl')
            // Autofocus would focus the first variant arrow and scroll the modal down to it.
            ->modalAutofocus(false)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('filament-content-builder-lang::fields.close'))
            ->modalContent(fn (ContentBlocksRenderer $component, ?array $state) => view('filament-content-builder::actions.preview-page', [
                'items' => $this->getItems($component, $state ?? []),
            ]))
            ->visible(fn (ContentBlocksRenderer $component, ?array $state): bool => collect($state ?? [])->contains(
                fn (mixed $item): bool => ($class = $component->getContentBlockClass($item['type'] ?? null)) && $class::hasPreview()
            ));
    }

    /**
     * @return list<array{label: string, icon: ?string, previews: list<array{url: string, label: ?string, width: int, height: int}>}>
     */
    protected function getItems(ContentBlocksRenderer $component, array $state): array
    {
        return collect($state)
            ->filter(fn (mixed $item): bool => is_array($item))
            ->map(function (array $item) use ($component): array {
                $class = $component->getContentBlockClass($item['type'] ?? null);

                if (!$class) {
                    return ['label' => (string) ($item['type'] ?? '?'), 'icon' => null, 'previews' => []];
                }

                return [
                    'label' => $class::getLabel($item['data'] ?? []),
                    'icon' => $class::icon(),
                    'previews' => array_map(function (array $preview) use ($class): array {
                        [$width, $height] = $class::previewSize($preview['variant'], 400);

                        return [
                            'url' => (string) $class::previewUrl($preview['variant']),
                            'label' => $preview['label'],
                            'width' => $width,
                            'height' => $height,
                        ];
                    }, $class::previews()),
                ];
            })
            ->values()
            ->all();
    }
}
