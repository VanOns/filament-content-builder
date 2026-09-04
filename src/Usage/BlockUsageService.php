<?php

namespace VanOns\FilamentContentBuilder\Usage;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

class BlockUsageService
{
    /**
     * @var array<class-string, class-string|false>
     */
    protected array $resources = [];

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
        $ttl = config('filament-content-builder.usage.cache');

        if (!$ttl) {
            return $this->computeUsage();
        }

        return Cache::remember($this->getCacheKey(), $ttl, fn () => $this->computeUsage());
    }

    public function clearCache(): void
    {
        Cache::forget($this->getCacheKey());
    }

    /**
     * @return array<string, array{type: string, title: string, icon: ?string, registered: bool, total: int, records: array, records_count: int}>
     */
    protected function computeUsage(): array
    {
        $usage = [];

        foreach (FilamentContentBuilder::getBlocks() as $block) {
            $usage[$block::type()] = [
                'type' => $block::type(),
                'title' => $block::title(),
                'icon' => $block::icon(),
                'registered' => true,
                'total' => 0,
                'records' => [],
            ];
        }

        foreach ($this->getSources() as $model => $source) {
            $query = $model::query();

            if ($columns = $this->getSelectColumns($model, $source)) {
                $query->select($columns);
            }

            $query->chunkById(100, function (Collection $records) use (&$usage, $source) {
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

    /**
     * @return array{type: string, title: string, icon: ?string, registered: bool, total: int, records: array, records_count: int}
     */
    public function getUsageFor(string $type): array
    {
        return $this->getUsage()[$type] ?? [
            'type' => $type,
            'title' => $type,
            'icon' => null,
            'registered' => false,
            'total' => 0,
            'records' => [],
            'records_count' => 0,
        ];
    }

    protected function collectRecordUsage(Model $record, array $source, array &$usage): void
    {
        $counts = [];

        foreach ($source['columns'] as $column) {
            $this->countBlocks($this->getBlockData($record, $column), $counts);
        }

        foreach ($counts as $type => $count) {
            $usage[$type] ??= [
                'type' => $type,
                'title' => $type,
                'icon' => null,
                'registered' => false,
                'total' => 0,
                'records' => [],
            ];

            $usage[$type]['total'] += $count;
            $usage[$type]['records'][] = [
                'model' => $record->getMorphClass(),
                'model_label' => str(class_basename($record))->headline()->toString(),
                'title' => $this->getRecordTitle($record, $source['title_attribute']),
                'url' => $this->getRecordUrl($record),
                'count' => $count,
            ];
        }
    }

    /**
     * Limit the query to the columns the usage scan needs. Returns null when
     * a configured column or title attribute is not a real table column (e.g.
     * an accessor), in which case the full model is loaded.
     *
     * @param class-string<Model> $model
     * @return ?array<string>
     */
    protected function getSelectColumns(string $model, array $source): ?array
    {
        $instance = new $model();
        $available = $instance->getConnection()->getSchemaBuilder()->getColumnListing($instance->getTable());

        $titleAttribute = $source['title_attribute'];

        if ($titleAttribute && !in_array($titleAttribute, $available, true)) {
            return null;
        }

        if (array_diff($source['columns'], $available)) {
            return null;
        }

        $titleColumns = $titleAttribute
            ? [$titleAttribute]
            : array_intersect(['title', 'name', 'label'], $available);

        return array_values(array_unique([
            $instance->getKeyName(),
            ...$source['columns'],
            ...$titleColumns,
        ]));
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

    protected function getRecordUrl(Model $record): ?string
    {
        $resource = $this->resources[$record::class] ??= Filament::getModelResource($record) ?? false;

        if (!$resource) {
            return null;
        }

        try {
            return $resource::getUrl('edit', ['record' => $record]);
        } catch (\Throwable) {
            return null;
        }
    }
}
