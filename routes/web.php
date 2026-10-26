<?php

use Illuminate\Support\Facades\Route;
use VanOns\FilamentContentBuilder\Http\Controllers\BlockPreviewController;
use VanOns\FilamentContentBuilder\Http\Middleware\AuthenticateBlockPreview;

Route::get('filament-content-builder/block-previews/{type}/{variant?}', BlockPreviewController::class)
    ->middleware(['web', AuthenticateBlockPreview::class])
    ->name('filament-content-builder.block-preview');
