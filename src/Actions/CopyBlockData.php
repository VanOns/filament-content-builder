<?php

namespace VanOns\FilamentContentBuilder\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Livewire\Component;
use VanOns\FilamentContentBuilder\Traits\ResolvesBlockClass;

class CopyBlockData extends Action
{
    use ResolvesBlockClass;

    public static function getDefaultName(): ?string
    {
        return 'copy';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-content-builder-lang::fields.copy_block'))
            ->icon('heroicon-o-clipboard-document')
            ->action(function (array $arguments, array $state, Component $livewire) {
                $data = $this->getBlockItemData($arguments, $state);

                if (! $data) {
                    return;
                }

                $livewire->js('navigator.clipboard.writeText(' . json_encode(json_encode($data)) . ')');

                Notification::make()
                    ->title(__('filament-content-builder-lang::fields.copy_block_success'))
                    ->success()
                    ->send();
            });
    }
}
