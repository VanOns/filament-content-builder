<?php

namespace VanOns\FilamentContentBuilder\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;
use VanOns\FilamentContentBuilder\FilamentContentBuilderPlugin;
use VanOns\FilamentContentBuilder\Usage\BlockUsageService;

class BlockUsage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'block-usage';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament-content-builder::pages.block-usage';

    public static function canAccess(): bool
    {
        // Fall back to a plain plugin instance so the config permission still
        // applies when the page is registered without the plugin.
        return (static::getPlugin() ?? FilamentContentBuilderPlugin::make())->isBlockUsageAuthorized();
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return static::getPlugin()?->getBlockUsageNavigationGroup();
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-content-builder-lang::usage.title');
    }

    public function getTitle(): string | Htmlable
    {
        return __('filament-content-builder-lang::usage.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label(__('filament-content-builder-lang::usage.refresh'))
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => app(BlockUsageService::class)->clearCache())
                ->visible(fn (): bool => app(BlockUsageService::class)->isCachingEnabled()),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): array => app(BlockUsageService::class)->getUsage())
            ->columns([
                IconColumn::make('icon')
                    ->label('')
                    ->icon(fn (array $record) => $record['icon']),
                TextColumn::make('title')
                    ->label(__('filament-content-builder-lang::usage.block'))
                    ->description(fn (array $record) => $record['registered']
                        ? null
                        : __('filament-content-builder-lang::usage.unregistered')),
                TextColumn::make('type')
                    ->label(__('filament-content-builder-lang::usage.type'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('total')
                    ->label(__('filament-content-builder-lang::usage.usages'))
                    ->badge()
                    ->color(fn (array $record) => $record['total'] > 0 ? 'primary' : 'gray'),
                TextColumn::make('records_count')
                    ->label(__('filament-content-builder-lang::usage.records')),
            ])
            ->recordActions([
                Action::make('records')
                    ->label(__('filament-content-builder-lang::usage.view_records'))
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (array $record) => $record['title'])
                    ->modalDescription(fn (array $record) => trans_choice('filament-content-builder-lang::usage.summary', $record['total'], [
                        'total' => $record['total'],
                        'records' => $record['records_count'],
                    ]))
                    ->modalContent(fn (array $record) => view(
                        'filament-content-builder::pages.block-usage-records',
                        ['usage' => $record]
                    ))
                    ->modalWidth(Width::Large)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->visible(fn (array $record) => $record['records'] !== []),
            ])
            ->paginated(false);
    }

    protected static function getPlugin(): ?FilamentContentBuilderPlugin
    {
        if (!Filament::getCurrentPanel()?->hasPlugin('filament-content-builder')) {
            return null;
        }

        $plugin = filament('filament-content-builder');

        return $plugin instanceof FilamentContentBuilderPlugin ? $plugin : null;
    }
}
