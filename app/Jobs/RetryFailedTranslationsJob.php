<?php

namespace App\Jobs;

use App\Models\Blog;
use App\Models\BlogTranslation;
use App\Models\Language;
use App\Models\Project;
use App\Models\ProjectTranslation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RetryFailedTranslationsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 120;

    /**
     * Field maps for models that track translation_status.
     *
     * @var array<class-string, array{parent: class-string, fields: array<int, string>, slugFrom: array<string, string>, foreignKey: string}>
     */
    private const RETRY_MAP = [
        ProjectTranslation::class => [
            'parent' => Project::class,
            'foreignKey' => 'project_id',
            'fields' => [
                'name',
                'short_description',
                'description',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ],
            'slugFrom' => ['slug' => 'name'],
        ],
        BlogTranslation::class => [
            'parent' => Blog::class,
            'foreignKey' => 'blog_id',
            'fields' => [
                'title',
                'content',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ],
            'slugFrom' => ['slug' => 'title'],
        ],
    ];

    public function handle(): void
    {
        $defaultLanguage = Language::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first()
            ?? Language::query()->where('is_active', true)->orderBy('order')->first();

        if (! $defaultLanguage) {
            Log::warning('RetryFailedTranslationsJob: no active languages');

            return;
        }

        foreach (self::RETRY_MAP as $translationClass => $config) {
            $failed = $translationClass::query()
                ->where('translation_status', 'failed')
                ->get();

            foreach ($failed as $translation) {
                $parentId = $translation->{$config['foreignKey']};
                $parent = $config['parent']::query()->find($parentId);

                if (! $parent) {
                    continue;
                }

                $sourceExists = $parent->translations()
                    ->where('language_id', $defaultLanguage->id)
                    ->exists();

                if (! $sourceExists) {
                    Log::warning('RetryFailedTranslationsJob: missing source translation', [
                        'translation' => $translationClass,
                        'id' => $translation->id,
                    ]);

                    continue;
                }

                if ((int) $translation->language_id === (int) $defaultLanguage->id) {
                    continue;
                }

                $translation->update(['translation_status' => 'pending']);

                TranslateContentJob::dispatch(
                    modelClass: $config['parent'],
                    modelId: (int) $parent->getKey(),
                    sourceLanguageId: (int) $defaultLanguage->id,
                    targetLanguageId: (int) $translation->language_id,
                    fields: $config['fields'],
                    slugFrom: $config['slugFrom'],
                );
            }
        }
    }
}
