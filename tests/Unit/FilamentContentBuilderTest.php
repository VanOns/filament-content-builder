<?php

use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;
use VanOns\FilamentContentBuilder\Blocks\TextBlock;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

beforeEach(function () {
    FilamentContentBuilder::$blockIndex = 0;
});

it('returns blocks from config', function () {
    $blocks = FilamentContentBuilder::getBlocks();

    expect($blocks)->toBeArray()->not->toBeEmpty();

    foreach ($blocks as $block) {
        expect(is_a($block, Block::class, true))->toBeTrue();
    }
});

it('returns container blocks from config', function () {
    $blocks = FilamentContentBuilder::getContainerBlocks();

    expect($blocks)->toBeArray()->not->toBeEmpty();

    foreach ($blocks as $block) {
        expect(is_a($block, Block::class, true))->toBeTrue();
    }
});

it('returns block class for known type', function () {
    $class = FilamentContentBuilder::getBlockClass('TextBlock');

    expect($class)->toBe(TextBlock::class);
});

it('returns null for unknown block type', function () {
    $class = FilamentContentBuilder::getBlockClass('NonExistentBlock');

    expect($class)->toBeNull();
});

it('returns null for null block type', function () {
    $class = FilamentContentBuilder::getBlockClass(null);

    expect($class)->toBeNull();
});

it('confirms known block exists', function () {
    expect(FilamentContentBuilder::blockExists('TextBlock'))->toBeTrue();
});

it('confirms unknown block does not exist', function () {
    expect(FilamentContentBuilder::blockExists('NonExistentBlock'))->toBeFalse();
});

it('creates block instance for known type', function () {
    $block = FilamentContentBuilder::getBlock('TextBlock', ['content' => 'Hello']);

    expect($block)->toBeInstanceOf(TextBlock::class)
        ->and($block->data['content'])->toBe('Hello');
});

it('increments block index on each getBlock call', function () {
    FilamentContentBuilder::$blockIndex = 0;

    $first = FilamentContentBuilder::getBlock('TextBlock', []);
    $second = FilamentContentBuilder::getBlock('TextBlock', []);

    expect($first->blockIndex)->toBe(0)
        ->and($second->blockIndex)->toBe(1);
});

it('returns null for unknown block in getBlock', function () {
    $block = FilamentContentBuilder::getBlock('NonExistentBlock', []);

    expect($block)->toBeNull();
});

it('parses block instances filtering unknown types', function () {
    $blocks = [
        ['type' => 'TextBlock', 'data' => ['content' => 'Hello']],
        ['type' => 'NonExistent', 'data' => []],
    ];

    $instances = FilamentContentBuilder::parseBlockInstances($blocks);

    expect($instances)->toHaveCount(1)
        ->and($instances[0])->toBeInstanceOf(TextBlock::class);
});

it('parses blocks passing through unknown types', function () {
    $unknown = ['type' => 'NonExistent', 'data' => ['foo' => 'bar']];
    $blocks = [
        ['type' => 'TextBlock', 'data' => ['content' => 'Hello']],
        $unknown,
    ];

    $result = FilamentContentBuilder::parseBlocks($blocks);

    expect($result)->toHaveCount(2)
        ->and($result[1])->toBe($unknown);
});
