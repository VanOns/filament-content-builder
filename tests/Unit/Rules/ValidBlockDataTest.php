<?php

use VanOns\FilamentContentBuilder\Rules\ValidBlockData;

function validateBlock(mixed $value): array
{
    $errors = [];
    $rule = new ValidBlockData();
    $rule->validate('block', $value, function (string $message) use (&$errors) {
        $errors[] = $message;
    });

    return $errors;
}

it('fails when value is not valid JSON', function () {
    $errors = validateBlock('not-json');

    expect($errors)->not->toBeEmpty();
});

it('fails when JSON is not an array', function () {
    $errors = validateBlock('"just a string"');

    expect($errors)->not->toBeEmpty();
});

it('fails when type key is missing', function () {
    $errors = validateBlock(json_encode(['data' => []]));

    expect($errors)->not->toBeEmpty();
});

it('fails when data key is missing', function () {
    $errors = validateBlock(json_encode(['type' => 'TextBlock']));

    expect($errors)->not->toBeEmpty();
});

it('fails when type is empty string', function () {
    $errors = validateBlock(json_encode(['type' => '', 'data' => []]));

    expect($errors)->not->toBeEmpty();
});

it('fails when data is not array', function () {
    $errors = validateBlock(json_encode(['type' => 'TextBlock', 'data' => 'string']));

    expect($errors)->not->toBeEmpty();
});

it('fails when block type is unknown', function () {
    $errors = validateBlock(json_encode(['type' => 'NonExistentBlock', 'data' => []]));

    expect($errors)->not->toBeEmpty();
});

it('passes for valid known block', function () {
    $errors = validateBlock(json_encode(['type' => 'TextBlock', 'data' => ['content' => 'Hello']]));

    expect($errors)->toBeEmpty();
});

it('fails when data contains keys the block schema does not define', function () {
    $errors = validateBlock(json_encode([
        'type' => 'TextBlock',
        'data' => ['content' => 'Hello', 'blockIndex' => 1337, 'nested' => true],
    ]));

    expect($errors)->not->toBeEmpty()
        ->and($errors[0])->toContain('blockIndex');
});

it('passes when data contains the settings key', function () {
    $errors = validateBlock(json_encode([
        'type' => 'TextBlock',
        'data' => ['content' => 'Hello', 'settings' => ['foo' => 'bar']],
    ]));

    expect($errors)->toBeEmpty();
});
