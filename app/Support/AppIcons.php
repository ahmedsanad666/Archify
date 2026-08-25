<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class AppIcons
{
    /**
     * Allowed icon name keys shared with the Vue IconPicker registry.
     *
     * @return list<string>
     */
    public static function names(): array
    {
        $path = resource_path('js/icons/names.json');

        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, fn ($name) => is_string($name) && $name !== ''));
    }
}
