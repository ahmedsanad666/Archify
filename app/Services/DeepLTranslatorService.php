<?php

namespace App\Services;

use DeepL\DeepLException;
use DeepL\Translator;
use DeepL\TranslatorOptions;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DeepLTranslatorService
{
    private ?Translator $translator = null;

    /**
     * Map app locale codes to DeepL language codes.
     *
     * @var array<string, string>
     */
    private const LOCALE_MAP = [
        'en' => 'EN',
        'tr' => 'TR',
        'ar' => 'AR',
    ];

    public function translate(string $text, string $sourceLocale, string $targetLocale): string
    {
        if (trim($text) === '') {
            return $text;
        }

        $source = $this->toDeepLCode($sourceLocale);
        $target = $this->toDeepLCode($targetLocale);

        if ($source === $target) {
            return $text;
        }

        try {
            $result = $this->client()->translateText($text, $source, $target);

            return is_array($result) ? $result[0]->text : $result->text;
        } catch (DeepLException $e) {
            Log::error('DeepL translation failed', [
                'message' => $e->getMessage(),
                'source' => $source,
                'target' => $target,
            ]);

            throw $e;
        }
    }

    /**
     * @param  array<string, string|null>  $fields
     * @return array<string, string>
     */
    public function translateFields(array $fields, string $sourceLocale, string $targetLocale): array
    {
        $translated = [];

        foreach ($fields as $key => $value) {
            if ($value === null || $value === '') {
                $translated[$key] = $value ?? '';

                continue;
            }

            $translated[$key] = $this->translate((string) $value, $sourceLocale, $targetLocale);
        }

        return $translated;
    }

    private function client(): Translator
    {
        if ($this->translator instanceof Translator) {
            return $this->translator;
        }

        $apiKey = config('services.deepl.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('DEEPL_API_KEY is not configured.');
        }

        $options = [];
        $apiUrl = config('services.deepl.api_url');

        if (is_string($apiUrl) && $apiUrl !== '') {
            $options[TranslatorOptions::SERVER_URL] = rtrim($apiUrl, '/');
        }

        $this->translator = new Translator($apiKey, $options);

        return $this->translator;
    }

    private function toDeepLCode(string $locale): string
    {
        $normalized = strtolower($locale);

        if (! isset(self::LOCALE_MAP[$normalized])) {
            throw new RuntimeException("Unsupported locale for DeepL: {$locale}");
        }

        return self::LOCALE_MAP[$normalized];
    }
}
