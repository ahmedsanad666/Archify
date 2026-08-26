<?php

namespace App\Services;

use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Support\UiTranslations;
use InvalidArgumentException;
use Illuminate\Support\Facades\File;

class UiTranslationService
{
    public function __construct(
        private readonly LanguageRepositoryInterface $languageRepository,
    ) {}

    /**
     * @return list<string>
     */
    public function allowedLocales(): array
    {
        $codes = $this->languageRepository
            ->allActive()
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        if ($codes === []) {
            return ['en', 'tr', 'ar'];
        }

        return $codes;
    }

    /**
     * @return array<string, mixed>
     */
    public function tree(string $locale): array
    {
        $this->assertLocale($locale);

        return UiTranslations::forLocale($locale);
    }

    /**
     * @return array<string, string>
     */
    public function flat(string $locale): array
    {
        return UiTranslations::flatten($this->tree($locale));
    }

    /**
     * @return list<string>
     */
    public function groups(string $locale): array
    {
        $groups = [];

        foreach (array_keys($this->flat($locale)) as $key) {
            $group = explode('.', $key, 2)[0] ?? '';
            if ($group !== '') {
                $groups[$group] = true;
            }
        }

        $list = array_keys($groups);
        sort($list);

        return $list;
    }

    /**
     * Merge only existing keys; ignore unknown keys.
     *
     * @param  array<string, mixed>  $incoming
     */
    public function update(string $locale, array $incoming): void
    {
        $this->assertLocale($locale);

        $current = $this->flat($locale);
        $merged = $current;

        foreach ($incoming as $key => $value) {
            if (! is_string($key) || ! array_key_exists($key, $current)) {
                continue;
            }

            if (! is_string($value) && ! is_numeric($value)) {
                continue;
            }

            $merged[$key] = (string) $value;
        }

        $tree = UiTranslations::nest($merged);
        $path = UiTranslations::pathFor($locale);
        $json = json_encode(
            $tree,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        ).PHP_EOL;

        $handle = fopen($path, 'c+');
        if ($handle === false) {
            throw new InvalidArgumentException("Unable to open translation file for {$locale}.");
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                throw new InvalidArgumentException("Unable to lock translation file for {$locale}.");
            }

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, $json);
            fflush($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }
    }

    private function assertLocale(string $locale): void
    {
        if (! in_array($locale, $this->allowedLocales(), true)) {
            throw new InvalidArgumentException("Locale [{$locale}] is not allowed.");
        }

        if (preg_match('/[^a-z0-9_-]/i', $locale)) {
            throw new InvalidArgumentException('Invalid locale format.');
        }

        if (! File::exists(UiTranslations::pathFor($locale))) {
            throw new InvalidArgumentException("Translation file for [{$locale}] was not found.");
        }
    }
}
