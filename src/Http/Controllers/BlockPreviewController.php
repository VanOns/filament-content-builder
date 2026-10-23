<?php

namespace VanOns\FilamentContentBuilder\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

class BlockPreviewController
{
    public function __invoke(string $type): BinaryFileResponse
    {
        $class = FilamentContentBuilder::getBlockClass($type);

        abort_unless($class && $class::hasPreview(), 404);

        return response()->file((string) $class::preview(), [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=31536000, immutable',
            // Neutralizes scripts when the SVG is opened directly.
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; img-src data:",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
