<?php

use VanOns\FilamentContentBuilder\Helpers\UrlHelper;

it('allows absolute urls with an allowed scheme', function (string $url) {
    expect(UrlHelper::sanitize($url))->toBe($url);
})->with([
    'https://example.com/page?a=b#c',
    'http://example.com',
    'mailto:someone@example.com',
    'tel:+31201234567',
]);

it('allows scheme-less urls', function (string $url) {
    expect(UrlHelper::sanitize($url))->toBe($url);
})->with([
    '/contact',
    '#anchor',
    'page/subpage',
    '//example.com/protocol-relative',
]);

it('rejects dangerous schemes', function (string $url) {
    expect(UrlHelper::sanitize($url))->toBeNull();
})->with([
    'javascript:alert(document.domain)',
    'JavaScript:alert(1)',
    'data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==',
    'vbscript:msgbox(1)',
    'file:///etc/passwd',
]);

it('rejects schemes hidden behind entities or control characters', function (string $url) {
    expect(UrlHelper::sanitize($url))->toBeNull();
})->with([
    "java\tscript:alert(1)",
    "javascript\n:alert(1)",
    '&#106;avascript:alert(1)',
    '&#x6a;avascript:alert(1)',
    ' javascript:alert(1)',
]);

it('rejects values that are not strings', function (mixed $url) {
    expect(UrlHelper::sanitize($url))->toBeNull();
})->with([
    [null],
    [[]],
    [42],
    [true],
]);

it('honours the configured schemes', function () {
    config()->set('filament-content-builder.allowed_link_schemes', ['https']);

    expect(UrlHelper::sanitize('https://example.com'))->toBe('https://example.com')
        ->and(UrlHelper::sanitize('http://example.com'))->toBeNull()
        ->and(UrlHelper::sanitize('mailto:a@example.com'))->toBeNull();
});

it('honours explicitly passed schemes over the config', function () {
    expect(UrlHelper::sanitize('mailto:a@example.com', ['http', 'https']))->toBeNull();
});
