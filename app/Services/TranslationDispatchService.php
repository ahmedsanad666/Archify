<?php

namespace App\Services;

use App\Jobs\TranslateContentJob;
use App\Models\Language;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TranslationDispatchService
{
    public function __construct(
        private readonly SiteSettingRepositoryInterface $siteSettingRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
    ) {}

    /**
     * Dispatch one TranslateContentJob per non-source active locale when auto-translate is on.
     *
     * @param  array<int, string>  $fields  Translatable attribute names on the translation row
     * @param  array<string, string>  $slugFrom  Optional map of slug field => source title field
     */
    public function dispatchForModel(
        Model $model,
        Language $sourceLanguage,
        array $fields,
        array $slugFrom = [],
        bool $force = false,
    ): void {
        $settings = $this->siteSettingRepository->getSingleton();

        if (! $force && (! $settings || ! $settings->auto_translate_enabled)) {
            return;
        }

        $targets = $this->languageRepository
            ->allActive()
            ->filter(fn (Language $language) => $language->id !== $sourceLanguage->id);

        foreach ($targets as $targetLanguage) {
            $defaults = $this->emptyFieldDefaults($model, $targetLanguage, $fields, $slugFrom);

            $translation = $model->translations()->firstOrCreate(
                ['language_id' => $targetLanguage->id],
                $defaults,
            );

            if ($this->translationHasStatus($translation)) {
                $translation->update(['translation_status' => 'pending']);
            }

            TranslateContentJob::dispatch(
                modelClass: $model::class,
                modelId: (int) $model->getKey(),
                sourceLanguageId: $sourceLanguage->id,
                targetLanguageId: $targetLanguage->id,
                fields: $fields,
                slugFrom: $slugFrom,
            );
        }
    }

    /**
     * @param  array<int, string>  $fields
     * @param  array<string, string>  $slugFrom
     * @return array<string, mixed>
     */
    private function emptyFieldDefaults(
        Model $model,
        Language $targetLanguage,
        array $fields,
        array $slugFrom,
    ): array {
        $defaults = [];

        foreach ($fields as $field) {
            $defaults[$field] = '';
        }

        foreach ($slugFrom as $slugField => $fromField) {
            $defaults[$fromField] = $defaults[$fromField] ?? 'Pending';
            $defaults[$slugField] = sprintf(
                'pending-%s-%s-%s',
                Str::kebab(class_basename($model)),
                $model->getKey(),
                $targetLanguage->code,
            );
        }

        $probe = $model->translations()->getRelated();
        if ($this->translationHasStatus($probe)) {
            $defaults['translation_status'] = 'pending';
        }

        return $defaults;
    }

    private function translationHasStatus(Model $translation): bool
    {
        return in_array('translation_status', $translation->getFillable(), true);
    }
}
