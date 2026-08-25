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
        $path = lang_path("{$locale}.json");

        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
