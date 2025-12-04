<?php

namespace VanOns\FilamentContentBuilder\Stubs;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

class Stubs
{
    /**
     * @param string        $filePath
     * @param string        $stub
     * @param array<string> $replacements
     *
     * @return void
     * @throws FileNotFoundException
     */
    public static function createFromStub(string $filePath, string $stub, array $replacements): void
    {
        $stub = File::get(self::getStubPath($stub));
        $stub = self::replaceContent($stub, $replacements);

        File::put($filePath, $stub);
    }

    /**
     * @param string $stub
     *
     * @return string
     */
    public static function getStubPath(string $stub): string
    {
        $base = base_path("stubs/{$stub}.stub");
        $default = __DIR__ . "/../../stubs/{$stub}.stub";

        return File::exists($base)
            ? $base
            : $default;
    }

    /**
     * @param string                 $content
     * @param array<string, ?string> $replacements
     *
     * @return string
     */
    public static function replaceContent(string $content, array $replacements): string
    {
        foreach ($replacements as $search => $replace) {
            $content = is_null($replace)
                ? preg_replace('/^.*{{\s*' . preg_quote($search, '/') . '\s*}}.*$\n?/m', '', $content)
                : str_replace("{{ {$search} }}", $replace, $content);
        }

        return $content;
    }
}
