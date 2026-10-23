<?php

use Illuminate\Support\Facades\Route;
use VanOns\FilamentContentBuilder\Http\Controllers\BlockPreviewController;

Route::get('filament-content-builder/block-previews/{type}', BlockPreviewController::class)
    ->name('filament-content-builder.block-preview');
