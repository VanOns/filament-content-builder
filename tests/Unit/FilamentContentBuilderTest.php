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
