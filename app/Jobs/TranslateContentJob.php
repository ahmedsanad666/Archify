<?php

namespace App\Jobs;

use App\Models\Language;
use App\Services\DeepLTranslatorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class TranslateContentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 300];

    public int $timeout = 60;

    public int $maxExceptions = 2;

    /**
     * @param  array<int, string>  $fields
     * @param  array<string, string>  $slugFrom
     */
    public function __construct(
        public string $modelClass,
        public int $modelId,
        public int $sourceLanguageId,
        public int $targetLanguageId,
        public array $fields,
        public array $slugFrom = [],
    ) {}

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        $key = sprintf(
            'translate:%s:%d:%d',
            $this->modelClass,
            $this->modelId,
            $this->targetLanguageId,
        );

        return [
            (new WithoutOverlapping($key))->releaseAfter(60)->expireAfter(120),
        ];
    }

    public function handle(DeepLTranslatorService $translator): void
    {
        /** @var Model $model */
        $model = $this->modelClass::query()->findOrFail($this->modelId);

        $sourceLanguage = Language::query()->findOrFail($this->sourceLanguageId);
        $targetLanguage = Language::query()->findOrFail($this->targetLanguageId);

        $sourceTranslation = $model->translations()
            ->where('language_id', $this->sourceLanguageId)
            ->firstOrFail();

        $targetTranslation = $model->translations()
            ->where('language_id', $this->targetLanguageId)
            ->firstOrFail();

        $payload = [];

        foreach ($this->fields as $field) {
            if (array_key_exists($field, $this->slugFrom)) {
                continue;
            }

            $payload[$field] = (string) ($sourceTranslation->{$field} ?? '');
        }

        $translated = RateLimiter::attempt(
            'deepl-api',
            8,
            fn () => $translator->translateFields(
                $payload,
                $sourceLanguage->code,
                $targetLanguage->code,
            ),
            60,
        );

        if ($translated === false) {
            $this->release(30);

            return;
        }

        foreach ($this->slugFrom as $slugField => $fromField) {
            $sourceForSlug = $translated[$fromField]
                ?? (string) ($sourceTranslation->{$fromField} ?? '');
            $translated[$slugField] = $this->uniqueSlug(
                $model,
                $slugField,
                Str::slug($sourceForSlug) ?: 'item',
                $this->targetLanguageId,
                (int) $targetTranslation->getKey(),
            );
        }

        if ($this->translationHasStatus($targetTranslation)) {
            $translated['translation_status'] = 'translated';
        }

        $targetTranslation->update($translated);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('TranslateContentJob failed permanently', [
            'model' => $this->modelClass,
            'model_id' => $this->modelId,
            'target_language_id' => $this->targetLanguageId,
            'error' => $exception?->getMessage(),
        ]);

        try {
            /** @var Model $model */
            $model = $this->modelClass::query()->find($this->modelId);

            if (! $model) {
                return;
            }

            $targetTranslation = $model->translations()
                ->where('language_id', $this->targetLanguageId)
                ->first();

            if ($targetTranslation && $this->translationHasStatus($targetTranslation)) {
                $targetTranslation->update(['translation_status' => 'failed']);
            }
        } catch (Throwable $e) {
            Log::error('Unable to mark translation as failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function translationHasStatus(Model $translation): bool
    {
        return in_array('translation_status', $translation->getFillable(), true);
    }

    private function uniqueSlug(
        Model $model,
        string $slugField,
        string $base,
        int $languageId,
        int $ignoreTranslationId,
    ): string {
        $slug = $base;
        $suffix = 1;

        while (
            $model->translations()
                ->where('language_id', $languageId)
                ->where($slugField, $slug)
                ->whereKeyNot($ignoreTranslationId)
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
