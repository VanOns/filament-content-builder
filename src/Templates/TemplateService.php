<?php

namespace VanOns\FilamentContentBuilder\Templates;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use VanOns\FilamentContentBuilder\Templates\Contracts\Template;

class TemplateService
{
    public function render($item): mixed
    {
        return $this->resolve($item->template)?->render($item);
    }

    public function resolve(string $template): ?Template
    {
        $template = $this->templates()->first(fn (string $class) => $class::type() === $template);

        if (!$template) {
            return null;
        }

        return new $template();
    }

    public function templates(): Collection
    {
        $classes = [];

        foreach (config('filament-content-builder.template_directories') as $dir) {
            foreach (glob($dir . '/{,*/}*.php', GLOB_BRACE) as $file) {
                $class = Str::of($file)->replace(app_path(), 'App')->replace('/', '\\')->replace('.php', '')->toString();
                if (class_exists($class) && is_subclass_of($class, Template::class)) {
                    $classes[] = $class;
                }
            }
        }

        return collect($classes);
    }

    public function options(): array
    {
        return $this->templates()->mapWithKeys(fn (string $template) => [$template::type() => $template::name()])->toArray();
    }

    public function templateFieldSets(): array
    {
        return $this->templates()->mapWithKeys(fn (string $template) => [$template::type() => $template::fieldSet()])->toArray();
    }

    public function templateGroups(): array
    {
        return $this->templates()->mapWithKeys(fn (string $template) => [$template::type() => $template::group()])->toArray();
    }
}
