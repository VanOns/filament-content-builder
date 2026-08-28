<?php

namespace VanOns\FilamentContentBuilder\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use VanOns\FilamentContentBuilder\Helpers\UrlHelper;

class SafeUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (UrlHelper::sanitize($value) === null) {
            $fail(__('filament-content-builder-lang::validation.url_scheme_not_allowed', [
                'schemes' => implode(', ', UrlHelper::allowedSchemes()),
            ]));
        }
    }
}
