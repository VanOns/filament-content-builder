<?php

namespace VanOns\FilamentContentBuilder\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

class ValidBlockData implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $block = json_decode($value, true);

        if (! is_array($block)) {
            $fail(__('filament-content-builder-lang::validation.block_invalid_json'));

            return;
        }

        if (! array_key_exists('type', $block) || ! array_key_exists('data', $block)) {
            $fail(__('filament-content-builder-lang::validation.block_missing_keys'));

            return;
        }

        if (! is_string($block['type']) || $block['type'] === '') {
            $fail(__('filament-content-builder-lang::validation.block_missing_keys'));

            return;
        }

        if (! is_array($block['data'])) {
            $fail(__('filament-content-builder-lang::validation.block_data_not_array'));

            return;
        }

        $class = FilamentContentBuilder::getBlockClass($block['type']);
        if ($class === null) {
            $fail(__('filament-content-builder-lang::validation.block_unknown_type', ['type' => $block['type']]));

            return;
        }

        $unknown = array_diff(array_keys($block['data']), static::allowedKeys($class));
        if ($unknown !== []) {
            $fail(__('filament-content-builder-lang::validation.block_unknown_keys', [
                'keys' => implode(', ', $unknown),
            ]));
        }
    }

    /**
     * @param class-string<Block> $class
     *
     * @return array<string>
     */
    public static function allowedKeys(string $class): array
    {
        $keys = [$class::settingsPrefix()];
        foreach ($class::schema() as $component) {
            if (method_exists($component, 'getName') && filled($name = $component->getName())) {
                $keys[] = $name;
            }
        }

        return $keys;
    }
}
