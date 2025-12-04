<?php

namespace VanOns\FilamentContentBuilder\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use VanOns\FilamentContentBuilder\Stubs\Stubs;

class FileHelper
{
    public static array $reservedWords = [
        '__halt_compiler', 'abstract', 'and', 'array', 'as', 'break', 'callable',
        'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default',
        'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor',
        'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends',
        'final', 'finally', 'fn', 'for', 'foreach', 'function', 'global', 'goto',
        'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof',
        'interface', 'isset', 'list', 'match', 'namespace', 'new', 'or', 'print',
        'private', 'protected', 'public', 'readonly', 'require', 'require_once',
        'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use',
        'var', 'while', 'xor', 'yield',
    ];

    public static function makeBlock(string $name, string $title): string
    {
        $dir = app_path('View/Blocks');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, recursive: true);
        }

        $file = "{$dir}/{$name}.php";

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
        $file = "{$dir}/{$filename}.blade.php";

        Stubs::createFromStub($file, 'block-view', [
            'class' => $name,
        ]);
    }

    public static function isReservedWord(string $name): bool
    {
        return in_array(strtolower($name), static::$reservedWords, true);
    }
}
