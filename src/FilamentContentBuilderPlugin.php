<?php

namespace VanOns\FilamentContentBuilder;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Facades\Gate;
use UnitEnum;
use VanOns\FilamentContentBuilder\Pages\BlockUsage;

class FilamentContentBuilderPlugin implements Plugin
{
    use EvaluatesClosures;

    protected bool | Closure | null $blockUsage = null;

    protected ?Closure $blockUsageAuthorization = null;

    protected string | UnitEnum | null $blockUsageNavigationGroup = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-content-builder';
    }

    /**
     * Toggle the block usage page for this panel. Defaults to the
     * `usage.enabled` config value.
     */
    public function blockUsage(bool | Closure $condition = true): static
    {
        $this->blockUsage = $condition;

        return $this;
    }

    public function authorizeBlockUsageUsing(Closure $callback): static
    {
        $this->blockUsageAuthorization = $callback;

        return $this;
    }

    public function blockUsageNavigationGroup(string | UnitEnum | null $group): static
    {
        $this->blockUsageNavigationGroup = $group;

        return $this;
    }

    public function register(Panel $panel): void
    {
        if ($this->isBlockUsageEnabled()) {
            $panel->pages([
                BlockUsage::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
    }

    public function isBlockUsageEnabled(): bool
    {
        return (bool) $this->evaluate($this->blockUsage ?? config('filament-content-builder.usage.enabled', false));
    }

    public function isBlockUsageAuthorized(): bool
    {
        if ($this->blockUsageAuthorization) {
            return (bool) $this->evaluate($this->blockUsageAuthorization);
        }

        $permission = config('filament-content-builder.usage.permission');

        if (!$permission) {
            return true;
        }

        $user = Filament::auth()->user();

        return $user !== null && Gate::forUser($user)->allows($permission);
    }

    public function getBlockUsageNavigationGroup(): string | UnitEnum | null
    {
        return $this->blockUsageNavigationGroup;
    }
}
