<?php

namespace VanOns\FilamentContentBuilder\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

class BlockPreviewController
{
    public function __invoke(string $type, ?string $variant = null): BinaryFileResponse
    {
        $class = FilamentContentBuilder::getBlockClass($type)
            ?? FilamentContentBuilder::getBlockClass($type, FilamentContentBuilder::getContainerBlocks());

        abort_unless($class && in_array($variant, array_column($class::previews(), 'variant'), true), 404);

        $path = (string) $class::preview($variant);

        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => $class::previewMimeType($path),
            'Cache-Control' => 'private, max-age=31536000, immutable',
            // Neutralizes scripts when the SVG is opened directly.
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; img-src data:",
            'X-Content-Type-Options' => 'nosniff',
        ])->setPrivate();
    }
}
