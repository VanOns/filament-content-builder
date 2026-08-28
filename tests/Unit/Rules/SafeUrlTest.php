<?php

use VanOns\FilamentContentBuilder\Rules\SafeUrl;

function validateUrl(mixed $value): array
{
    $errors = [];
    (new SafeUrl())->validate('url', $value, function (string $message) use (&$errors) {
        $errors[] = $message;
    });

    return $errors;
}

it('passes for safe urls', function (mixed $url) {
    expect(validateUrl($url))->toBeEmpty();
})->with([
    'https://example.com',
    'mailto:someone@example.com',
    '/contact',
    '#anchor',
]);

it('passes for empty values so the field stays optional', function (mixed $url) {
    expect(validateUrl($url))->toBeEmpty();
})->with([
    [null],
    [''],
]);

it('fails for dangerous schemes', function (string $url) {
    expect(validateUrl($url))->not->toBeEmpty();
})->with([
    'javascript:alert(1)',
    'data:text/html,<script>alert(1)</script>',
    '&#106;avascript:alert(1)',
]);
