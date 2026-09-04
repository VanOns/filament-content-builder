<?php

namespace VanOns\FilamentContentBuilder\Usage;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

class BlockUsageService
{
    /**
     * @var array<class-string<Model>, class-string|false>
     */
    protected array $resources = [];

    protected ?array $computed = null;

    /**
     * @return array<class-string<Model>, array{columns: array<string>, title_attribute: ?string}>
     */
    public function getSources(): array
    {
        $sources = [];

        foreach (config('filament-content-builder.usage.sources', []) as $model => $source) {
            if (!is_a($model, Model::class, true)) {
                continue;
            }

            if (!is_array($source) || array_is_list($source)) {
                $source = ['columns' => (array) $source];
            }

            $sources[$model] = [
                'columns' => (array) ($source['columns'] ?? []),
                'title_attribute' => $source['title_attribute'] ?? null,
            ];
        }

        return $sources;
    }

    /**
     * Usage per block type, ordered by total usages. Registered blocks are
     * always included; unregistered types found in stored content as well.
     *
     * @return array<string, array{type: string, title: string, icon: ?string, registered: bool, total: int, records: array, records_count: int}>
     */
    public function getUsage(): array
    {
        return $this->computed ??= $this->isCachingEnabled()
            ? Cache::remember($this->getCacheKey(), config('filament-content-builder.usage.cache'), fn () => $this->computeUsage())
            : $this->computeUsage();
    }

    public function isCachingEnabled(): bool
    {
        return (bool) config('filament-content-builder.usage.cache');
    }

    public function clearCache(): void
    {
        $this->computed = null;

        Cache::forget($this->getCacheKey());
    }

    /**
     * @return array<string, array{type: string, title: string, icon: ?string, registered: bool, total: int, records: array, records_count: int}>
     */
    protected function computeUsage(): array
    {
        $usage = [];

        foreach (FilamentContentBuilder::getBlocks() as $block) {
            $usage[$block::type()] = $this->makeRow($block::type(), $block);
        }

        foreach ($this->getSources() as $model => $source) {
            $source['model_label'] = $this->getModelLabel($model);

            $model::query()->chunkById(500, function (Collection $records) use (&$usage, $source) {
                foreach ($records as $record) {
                    $this->collectRecordUsage($record, $source, $usage);
                }
            });
        }

        foreach ($usage as &$row) {
            $row['records_count'] = count($row['records']);
        }

        uasort($usage, fn (array $a, array $b) => $b['total'] <=> $a['total']);

        return $usage;
    }

    protected function makeRow(string $type, Block|string|null $block = null): array
    {
        return [
            'type' => $type,
            'title' => $block ? $block::title() : $type,
            'icon' => $block ? $block::icon() : null,
            'registered' => $block !== null,
            'total' => 0,
            'records' => [],
        ];
    }

    protected function collectRecordUsage(Model $record, array $source, array &$usage): void
    {
        $counts = [];

        foreach ($source['columns'] as $column) {
            $this->countBlocks($this->getBlockData($record, $column), $counts);
        }

        if ($counts === []) {
            return;
        }

        $entry = [
            'model_label' => $source['model_label'],
            'title' => $this->getRecordTitle($record, $source['title_attribute']),
            'url' => $this->getRecordUrl($record),
        ];

        foreach ($counts as $type => $count) {
            $usage[$type] ??= $this->makeRow($type);

            $usage[$type]['total'] += $count;
            $usage[$type]['records'][] = [...$entry, 'count' => $count];
        }
    }

    protected function getBlockData(Model $record, string $column): array
    {
        $data = $record->getAttribute($column);

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Count block entries, recursing into block data for nested blocks.
     *
     * @param array<string, int> $counts
     */
    protected function countBlocks(mixed $blocks, array &$counts): void
    {
        if (!is_array($blocks)) {
            return;
        }

        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }

            if (is_string($block['type'] ?? null) && is_array($block['data'] ?? null)) {
                $counts[$block['type']] = ($counts[$block['type']] ?? 0) + 1;
                $this->countBlocks($block['data'], $counts);

                continue;
            }

            $this->countBlocks($block, $counts);
        }
    }

    protected function getRecordTitle(Model $record, ?string $attribute): string
    {
        foreach (array_filter([$attribute, 'title', 'name', 'label']) as $candidate) {
            $title = $record->getAttribute($candidate);

            if (is_string($title) && $title !== '') {
                return $title;
            }
        }

        return '#' . $record->getKey();
    }

    // Usage is scoped by panel and tenant, since global scopes (e.g. multisite)
    // can change what each query sees.
    protected function getCacheKey(): string
    {
        return implode(':', array_filter([
            'filament-content-builder.usage',
            Filament::getCurrentPanel()?->getId(),
            Filament::getTenant()?->getKey(),
        ]));
    }

    /**
     * @param class-string<Model> $model
     */
    protected function getModelLabel(string $model): string
    {
        $resource = $this->getResource($model);

        return $resource
            ? $resource::getTitleCaseModelLabel()
            : str(class_basename($model))->headline()->toString();
    }

    protected function getRecordUrl(Model $record): ?string
    {
        $resource = $this->getResource($record::class);

        if (!$resource || !$resource::hasPage('edit')) {
            return null;
        }

        try {
            return $resource::getUrl('edit', ['record' => $record]);
        } catch (UrlGenerationException) {
            // No URL outside a panel request, e.g. a missing tenant parameter.
            return null;
        }
    }

    /**
     * @param class-string<Model> $model
     * @return class-string|null
     */
    protected function getResource(string $model): ?string
    {
        $resource = $this->resources[$model] ??= Filament::getModelResource($model) ?? false;

        return $resource ?: null;
    }
}
