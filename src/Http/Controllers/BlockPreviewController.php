<?php

namespace VanOns\FilamentContentBuilder\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;

class BlockPreviewController
{
    public function __invoke(Request $request, string $type, ?string $variant = null): BinaryFileResponse
    {
        $class = FilamentContentBuilder::getRegisteredBlockClass($type)
            ?? $this->unregisteredBlockClass($request->query('block'), $type);

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

    /**
     * A block that is not in the config but only given to a builder, e.g. with `contentBlocks()`. Its preview URL
     * names its class, as its type can't be looked up.
     *
     * @return class-string<Block>|null
     */
    protected function unregisteredBlockClass(mixed $class, string $type): ?string
    {
        return is_string($class) && is_subclass_of($class, Block::class) && $class::type() === $type ? $class : null;
    }
}
