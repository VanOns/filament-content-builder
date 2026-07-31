<?php

use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

it('returns settings from data', function () {
    $block = new class (['settings' => ['color' => 'red']]) extends Block {
        public static function schema(): array
        {
            return [];
        }
    };

    expect($block->getSettings())->toBe(['color' => 'red']);
});

it('returns empty array when no settings in data', function () {
    $block = new class ([]) extends Block {
        public static function schema(): array
        {
            return [];
        }
    };

    expect($block->getSettings())->toBe([]);
});

it('settings prefix is settings', function () {
    expect(Block::settingsPrefix())->toBe('settings');
});

it('settings schema returns empty array by default', function () {
    $block = new class ([]) extends Block {
        public static function schema(): array
        {
            return [];
        }
    };

    expect($block::settingsSchema())->toBe([]);
});

it('mutateSettingsData returns data unchanged by default', function () {
    $data = ['foo' => 'bar'];

    $block = new class ([]) extends Block {
        public static function schema(): array
        {
            return [];
        }
    };

    expect($block::mutateSettingsData($data))->toBe($data);
});
