<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class UiTranslations
{
    /**
     * Load nested UI strings for a locale from lang/{locale}.json.
     *
     * @return array<string, mixed>
     */
    public static function forLocale(string $locale): array
    {
        $path = self::pathFor($locale);

        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function pathFor(string $locale): string
    {
        return lang_path("{$locale}.json");
    }

    /**
     * Flatten nested arrays into dotted string keys (leaf strings only).
     *
     * @param  array<string, mixed>  $tree
     * @return array<string, string>
     */
    public static function flatten(array $tree, string $prefix = ''): array
    {
        $flat = [];

        foreach ($tree as $key => $value) {
            $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if (is_array($value)) {
                $flat = array_merge($flat, self::flatten($value, $path));

                continue;
            }

            if (is_string($value) || is_numeric($value)) {
                $flat[$path] = (string) $value;
            }
        }

        return $flat;
    }

    /**
     * Rebuild a nested array from dotted keys.
     *
     * @param  array<string, string>  $flat
     * @return array<string, mixed>
     */
    public static function nest(array $flat): array
    {
        $tree = [];

        foreach ($flat as $path => $value) {
            $segments = explode('.', (string) $path);
            $cursor = &$tree;

            foreach ($segments as $index => $segment) {
                $isLeaf = $index === count($segments) - 1;

                if ($isLeaf) {
                    $cursor[$segment] = $value;
                    continue;
                }

                if (! isset($cursor[$segment]) || ! is_array($cursor[$segment])) {
                    $cursor[$segment] = [];
                }

                $cursor = &$cursor[$segment];
            }

            unset($cursor);
        }

        return $tree;
    }
}
