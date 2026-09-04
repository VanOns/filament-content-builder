<?php

namespace VanOns\FilamentContentBuilder\Fields;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use VanOns\FilamentContentBuilder\Templates\TemplateService;

class Template extends Select
{
    public static function getDefaultName(): ?string
    {
        return 'template';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $service = app(TemplateService::class);

        $this->options($service->options())
            ->live()
            ->default($service->defaultType())
            ->selectablePlaceholder(false)
            ->afterStateHydrated(fn (Template $component) => $component->hydrateTemplateFields())
            ->afterStateUpdated(fn (Template $component) => $component->hydrateTemplateFields());
    }

    /**
     * Hydrate the fields that the selected template added to the schema after
     * the form was filled: applies their defaults and writes their state keys,
     * leaving state that is already set untouched.
     */
    public function hydrateTemplateFields(): void
    {
        $state = (array) $this->getLivewire();

        foreach ($this->findTemplateFields($this->getRootContainer()) as $fields) {
            if ($fields->needsStateHydration()) {
                $fields->hydrateState($state);
            }
        }
    }

    /**
     * @return array<TemplateFields>
     */
    protected function findTemplateFields(Schema $schema): array
    {
        $found = [];

        foreach ($schema->getComponents(withActions: false, withHidden: true) as $component) {
            if ($component instanceof TemplateFields && $component->getTemplateField() === $this->getName()) {
                $found[] = $component;

                continue;
            }

            // Fields carry state of their own, so they never hold these containers.
            if (!$component instanceof Component || $component->hasStatePath()) {
                continue;
            }

            foreach ($component->getChildSchemas(withHidden: true) as $childSchema) {
                $found = [...$found, ...$this->findTemplateFields($childSchema)];
            }
        }

        return $found;
    }
}
