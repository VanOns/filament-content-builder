<?php

namespace VanOns\FilamentContentBuilder\Fields;

use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use VanOns\FilamentContentBuilder\Templates\Contracts\Template as TemplateContract;
use VanOns\FilamentContentBuilder\Templates\TemplateService;

class TemplateFields extends Group
{
    protected string | Closure $templateField = 'template';

    protected bool | Closure $isFieldset = false;

    protected ?string $cachedTemplateType = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schema(fn (): array => $this->getTemplateFields())
            ->columns(1);
    }

    /**
     * The field holding the selected template.
     */
    public function templateField(string | Closure $name): static
    {
        $this->templateField = $name;

        return $this;
    }

    public function getTemplateField(): string
    {
        return $this->evaluate($this->templateField);
    }

    /**
     * Wrap the fields in a fieldset labelled with the template name.
     */
    public function fieldset(bool | Closure $condition = true): static
    {
        $this->isFieldset = $condition;

        return $this;
    }

    public function isFieldset(): bool
    {
        return (bool) $this->evaluate($this->isFieldset);
    }

    public function getTemplateType(): ?string
    {
        $type = $this->evaluate(fn (Get $get): mixed => $get($this->getTemplateField()));

        return is_string($type) ? $type : null;
    }

    public function getTemplate(): ?TemplateContract
    {
        return app(TemplateService::class)->resolve((string) $this->getTemplateType());
    }

    /**
     * @return array<Component>
     */
    public function getTemplateFields(): array
    {
        $template = $this->getTemplate();
        $fields = $template ? $template::fields() : [];

        if (empty($fields) || !$this->isFieldset()) {
            return $fields;
        }

        return [
            Fieldset::make($template::type())
                ->label($template::name())
                ->schema($fields)
                ->columns(1),
        ];
    }

    /**
     * @param array-key | null $key
     */
    public function getChildSchema($key = null): ?Schema
    {
        $this->refreshTemplateType();

        return parent::getChildSchema($key);
    }

    /**
     * @return array<Schema>
     */
    public function getChildSchemas(bool $withHidden = false): array
    {
        $this->refreshTemplateType();

        return parent::getChildSchemas($withHidden);
    }

    /**
     * Drop the cached fields when another template is selected. Not every
     * Filament version has the clearing method, but those versions do not cache
     * child schemas either, so skipping it is safe.
     */
    protected function refreshTemplateType(): void
    {
        $type = $this->getTemplateType();

        if ($type === $this->cachedTemplateType) {
            return;
        }

        $this->cachedTemplateType = $type;

        /** @phpstan-ignore function.alreadyNarrowedType */
        if (method_exists($this, 'clearCachedDefaultChildSchemas')) {
            $this->clearCachedDefaultChildSchemas();
        }
    }

    /**
     * Whether any field of the selected template is missing from the state.
     */
    public function needsStateHydration(): bool
    {
        $state = (array) $this->getLivewire();

        foreach ($this->getChildSchemas(withHidden: true) as $schema) {
            foreach ($schema->getFlatFields(withHidden: true) as $field) {
                if (!Arr::has($state, $field->getStatePath())) {
                    return true;
                }
            }
        }

        return false;
    }
}
