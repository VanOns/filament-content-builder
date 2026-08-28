<?php

use Illuminate\Support\Facades\Blade;

function renderBlocks(array $blocks): string
{
    return Blade::render(
        '<x-filament-content-builder::block-renderer :blocks="$blocks" />',
        ['blocks' => $blocks]
    );
}

it('strips scripts and event handlers from text block content', function () {
    $html = renderBlocks([
        ['type' => 'TextBlock', 'data' => ['content' => '<script>alert(1)</script><img src=x onerror=alert(1)><p>keep</p>']],
    ]);

    expect($html)->not->toContain('<script')
        ->not->toContain('onerror')
        ->toContain('keep');
});

it('drops cta links with a dangerous scheme but keeps the text', function (string $url) {
    $html = renderBlocks([
        ['type' => 'CtaBlock', 'data' => ['text' => 'Click', 'url' => $url, 'target' => '_self']],
    ]);

    expect($html)->not->toContain('<a ')
        ->not->toContain($url)
        ->toContain('Click');
})->with([
    'javascript:alert(document.domain)',
    'data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==',
]);

it('keeps cta links with an allowed scheme', function () {
    $html = renderBlocks([
        ['type' => 'CtaBlock', 'data' => ['text' => 'Click', 'url' => 'https://example.com', 'target' => '_blank']],
    ]);

    expect($html)->toContain('href="https://example.com"')
        ->toContain('target="_blank"');
});

it('does not embed a url that is not embeddable', function () {
    $html = renderBlocks([
        ['type' => 'EmbedBlock', 'data' => ['url' => 'https://evil.example.com/not-allowed']],
    ]);

    expect($html)->not->toContain('evil.example.com')
        ->not->toContain('<iframe');
});

it('embeds a url from an allowed service', function () {
    $html = renderBlocks([
        ['type' => 'EmbedBlock', 'data' => ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']],
    ]);

    expect($html)->toContain('<iframe');
});

it('renders blocks whose stored data is null, missing or of the wrong type', function (array $blocks) {
    expect(renderBlocks($blocks))->toBeString();
})->with([
    'cta all null' => [[['type' => 'CtaBlock', 'data' => ['text' => null, 'url' => null, 'target' => null]]]],
    'cta empty data' => [[['type' => 'CtaBlock', 'data' => []]]],
    'text null content' => [[['type' => 'TextBlock', 'data' => ['content' => null]]]],
    'text array content' => [[['type' => 'TextBlock', 'data' => ['content' => []]]]],
    'list missing items' => [[['type' => 'ListBlock', 'data' => ['title' => 't', 'type' => 'ordered']]]],
    'list null items' => [[['type' => 'ListBlock', 'data' => ['items' => null]]]],
    'list scalar items' => [[['type' => 'ListBlock', 'data' => ['items' => ['a', 'b']]]]],
    'list items without text' => [[['type' => 'ListBlock', 'data' => ['items' => [['foo' => 'bar']]]]]],
    'embed null url' => [[['type' => 'EmbedBlock', 'data' => ['url' => null]]]],
    'container null content' => [[['type' => 'Container', 'data' => ['content' => null]]]],
    'container empty data' => [[['type' => 'Container', 'data' => []]]],
    'unknown block type' => [[['type' => 'NotARealBlock', 'data' => []]]],
]);

it('escapes list titles and items', function () {
    $html = renderBlocks([
        ['type' => 'ListBlock', 'data' => [
            'title' => '<img src=x onerror=alert(1)>',
            'type' => 'unordered',
            'items' => [['text' => '<b>bold</b>']],
        ]],
    ]);

    expect($html)->not->toContain('<img')
        ->not->toContain('<b>')
        ->toContain('&lt;img');
});
