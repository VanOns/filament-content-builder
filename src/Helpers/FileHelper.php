<?php

namespace VanOns\FilamentContentBuilder\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use VanOns\FilamentContentBuilder\Stubs\Stubs;

class FileHelper
{
    public static function makeBlock(string $name, string $title): string
    {
        $dir = app_path('View/Blocks');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, recursive: true);
        }

        $file = "$dir/$name.php";

        Stubs::createFromStub($file, 'block', [
            'class' => $name,
            'title' => $title,
        ]);

        static::makeBlockView($name);

        return $file;
    }

    protected static function makeBlockView(string $name): void
    {
        $dir = resource_path('views/blocks');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, recursive: true);
        }

        $filename = Str::kebab($name);
        $file = "$dir/$filename.blade.php";

        Stubs::createFromStub($file, 'block-view', [
            'class' => $name,
        ]);
    }
}
