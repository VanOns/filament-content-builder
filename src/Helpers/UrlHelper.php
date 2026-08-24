<?php

namespace VanOns\FilamentContentBuilder\Helpers;

class UrlHelper
{
    /**
     * @return array<string>
     */
    public static function allowedSchemes(): array
    {
        $schemes = config('filament-content-builder.allowed_link_schemes', ['http', 'https', 'mailto', 'tel']);

        return is_array($schemes) ? array_values(array_filter($schemes, 'is_string')) : [];
    }

    /**
     * Returns the URL when its scheme is allowed, `null` otherwise. Scheme-less URLs are allowed.
     *
     * Mirrors `Str::sanitizeUrl()`, which only exists from Filament v4.11 onwards.
     *
     * @param array<string>|null $allowedSchemes
     */
    public static function sanitize(mixed $url, ?array $allowedSchemes = null): ?string
    {
        if (!is_string($url) || blank($url)) {
            return null;
        }

        $allowedSchemes ??= static::allowedSchemes();

        // Legitimate URLs percent-encode whitespace and control characters.
        if (preg_match('/[\x00-\x20\x7F]/', $url)) {
            return null;
        }

        $decoded = static::decodeForSchemeCheck($url);

        if (
            preg_match('/[\x00-\x1F\x7F]/', $decoded) ||
            preg_match('/[\x00-\x1F\x7F]/', rawurldecode($decoded))
        ) {
            return null;
        }

        // Browsers do not percent-decode the scheme, so only the HTML-decoded form matters here.
        if (
            preg_match('/^([a-z][a-z0-9+\-.]*):/i', $decoded, $matches) &&
            !in_array(strtolower($matches[1]), array_map('strtolower', $allowedSchemes), true)
        ) {
            return null;
        }

        return $url;
    }

    // Numeric entities go first: `html_entity_decode()` turns control entities into U+FFFD.
    protected static function decodeForSchemeCheck(string $url): string
    {
        $decoded = preg_replace_callback(
            '/&#(?:x([0-9a-f]+)|([0-9]+));?/i',
            function (array $match): string {
                $code = $match[1] !== '' ? (int) hexdec($match[1]) : (int) $match[2];

                return $code <= 127 ? chr($code) : $match[0];
            },
            $url
        );

        return html_entity_decode((string) $decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
