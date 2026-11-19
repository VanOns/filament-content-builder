<?php

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Fixtures\UnregisteredBlock;
use VanOns\FilamentContentBuilder\Blocks\TextBlock;
use VanOns\FilamentContentBuilder\Http\Controllers\BlockPreviewController;

beforeEach(function () {
    $this->previewPath = resource_path('block-previews/unregistered-block.svg');

    @mkdir(dirname($this->previewPath), recursive: true);
    file_put_contents($this->previewPath, '<svg xmlns="http://www.w3.org/2000/svg"/>');
});

afterEach(function () {
    @unlink($this->previewPath);
});

function requestBlockPreview(string $type, ?string $block = null): mixed
{
    return app(BlockPreviewController::class)(Request::create('/', 'GET', array_filter(['block' => $block])), $type);
}

it('names the class in the preview url of a block that is not registered', function () {
    parse_str((string) parse_url(UnregisteredBlock::previewUrl(), PHP_URL_QUERY), $query);
    parse_str((string) parse_url(TextBlock::previewUrl(), PHP_URL_QUERY), $registeredQuery);

    expect($query['block'] ?? null)->toBe(UnregisteredBlock::class)
        ->and($registeredQuery)->not->toHaveKey('block');
});

it('serves the preview of a block that is not registered', function () {
    expect(requestBlockPreview('UnregisteredBlock', UnregisteredBlock::class)->getFile()->getPathname())->toBe($this->previewPath);
});

it('does not serve a preview for a class that is not a block or has another type', function (string $type, string $block) {
    requestBlockPreview($type, $block);
})->with([
    'not a block' => ['UnregisteredBlock', stdClass::class],
    'another type' => ['OtherBlock', UnregisteredBlock::class],
    'no class' => ['UnregisteredBlock', ''],
])->throws(NotFoundHttpException::class);
