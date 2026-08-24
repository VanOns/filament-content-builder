<?php

use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

function makeLabeledBlock(string $labelField): Block
{
    return new class (['title' => null]) extends Block {
        public static ?string $labelField = 'title';

        public static function schema(): array
        {
            return [];
        }
    };
}

it('returns null label when state is null', function () {
    $block = new class ([]) extends Block {
        public static ?string $labelField = 'title';

        public static function schema(): array
        {
            return [];
        }
    };

    expect($block::label(null))->toBeNull();
});

it('returns null label when labelField is not set', function () {
    $block = new class ([]) extends Block {
        public static ?string $labelField = null;

        public static function schema(): array
        {
            return [];
        }
    };

    expect($block::label(['title' => 'Hello']))->toBeNull();
});

it('returns label from state field', function () {
    $block = new class ([]) extends Block {
        public static ?string $labelField = 'title';

        public static function schema(): array
        {
            return [];
        }
    };

    expect($block::label(['title' => 'Hello']))->toBe('Hello');
});

it('strips HTML tags from label', function () {
    $block = new class ([]) extends Block {
        public static ?string $labelField = 'title';

        public static function schema(): array
        {
            return [];
        }
    };

    expect($block::label(['title' => '<b>Bold text</b>']))->toBe('Bold text');
});

it('decodes HTML entities in label', function () {
    $block = new class ([]) extends Block {
        public static ?string $labelField = 'title';

        public static function schema(): array
        {
            return [];
        }
    };

    expect($block::label(['title' => 'Hello &amp; World']))->toBe('Hello & World');
});

it('returns null label for empty string', function () {
    $block = new class ([]) extends Block {
        public static ?string $labelField = 'title';

        public static function schema(): array
        {
            return [];
        }
    };

    expect($block::label(['title' => '']))->toBeNull();
});

it('getLabel returns title when no label field set', function () {
    $block = new class ([]) extends Block {
        public static ?string $labelField = null;

        public static function schema(): array
        {
            return [];
        }
    };

    expect($block::getLabel([]))->toBe($block::title());
});

it('getLabel returns truncated label with title appended', function () {
    $block = new class ([]) extends Block {
        public static ?string $labelField = 'title';

        public static function schema(): array
        {
            return [];
        }
    };

    $longTitle = str_repeat('a', 40);
    $label = $block::getLabel(['title' => $longTitle]);

    expect($label)->toContain(' - ')
        ->and($label)->toContain($block::title());
});

it('getLabel returns just title when label is empty', function () {
    $block = new class ([]) extends Block {
        public static ?string $labelField = 'title';

        public static function schema(): array
        {
            return [];
        }
    };

    expect($block::getLabel(['title' => '']))->toBe($block::title());
});

it('does not resurrect tags hidden behind entities', function () {
    $block = new class ([]) extends Block {
        public static ?string $labelField = 'title';

        public static function schema(): array
        {
            return [];
        }
    };

    expect($block::label(['title' => '&lt;script&gt;alert(1)&lt;/script&gt;']))->toBe('alert(1)');
});
