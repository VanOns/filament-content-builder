<?php

use VanOns\FilamentContentBuilder\Blocks\TextBlock;

it('returns class basename as type', function () {
    expect(TextBlock::type())->toBe('TextBlock');
});

it('converts type to human-readable title', function () {
    expect(TextBlock::title())->toBeString()->not->toBeEmpty();
});

it('returns correct view path for package block', function () {
    expect(TextBlock::view())->toBe('filament-content-builder::blocks.text-block');
});

it('constructs with data array', function () {
    $block = new TextBlock(['content' => 'Hello world']);

    expect($block->data)->toBe(['content' => 'Hello world'])
        ->and($block->content)->toBe('Hello world');
});

it('make creates instance via container', function () {
    $block = TextBlock::make(['content' => 'Test']);

    expect($block)->toBeInstanceOf(TextBlock::class)
        ->and($block->content)->toBe('Test');
});

it('toArray returns type and data structure', function () {
    $block = new TextBlock(['content' => 'Hello']);

    expect($block->toArray())->toBe([
        'type' => 'TextBlock',
        'data' => ['content' => 'Hello'],
    ]);
});

it('parseArray returns type and parsed data', function () {
    $block = new TextBlock(['content' => 'Hello']);

    expect($block->parseArray())->toBe([
        'type' => 'TextBlock',
        'data' => ['content' => 'Hello'],
    ]);
});

it('toText returns null by default', function () {
    $block = new TextBlock(['content' => 'Hello']);

    expect($block->toText())->toBeNull();
});

it('nested defaults to false', function () {
    $block = new TextBlock([]);

    expect($block->nested)->toBeFalse();
});

it('nested can be set via data', function () {
    $block = new TextBlock(['nested' => true]);

    expect($block->nested)->toBeTrue();
});

it('package block view uses filament-content-builder namespace', function () {
    expect(TextBlock::view())->toStartWith('filament-content-builder::blocks.');
});
