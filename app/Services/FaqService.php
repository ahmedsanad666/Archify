<?php

namespace App\Services;

use App\Models\Faq;
use App\Models\Language;
use App\Repositories\Contracts\FaqRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FaqService
{
    public function __construct(
        private readonly FaqRepositoryInterface $faqRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function all(): Collection
    {
        return $this->faqRepository->all();
    }

    public function find(int $id): ?Faq
    {
        return $this->faqRepository->find($id);
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds): void {
            $this->faqRepository->reorder($orderedIds);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Faq
    {
        return DB::transaction(function () use ($data) {
            $nextOrder = $data['order'] ?? ($this->faqRepository->all()->max('order') + 1);

            $faq = $this->faqRepository->create([
                'order' => (int) $nextOrder,
            ]);

            $this->syncTranslations($faq, $data['translations'] ?? []);
            $this->maybeDispatch($faq, $data);

            return $this->faqRepository->find((int) $faq->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Faq $faq, array $data): Faq
    {
        return DB::transaction(function () use ($faq, $data) {
            if (array_key_exists('order', $data)) {
                $this->faqRepository->update($faq, [
                    'order' => (int) $data['order'],
                ]);
            }

            $this->syncTranslations($faq, $data['translations'] ?? []);
            $this->maybeDispatch($faq, $data);

            return $this->faqRepository->find((int) $faq->id);
        });
    }

    public function delete(Faq $faq): void
    {
        $this->faqRepository->delete($faq);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(Faq $faq, array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            $language = $this->languageRepository->findByCode($locale);

            if (! $language instanceof Language) {
                continue;
            }

            $question = trim((string) ($fields['question'] ?? ''));
            $answer = trim((string) ($fields['answer'] ?? ''));

            if ($question === '' && $answer === '') {
                continue;
            }

            $faq->translations()->updateOrCreate(
                ['language_id' => $language->id],
                [
                    'question' => $question,
                    'answer' => $answer,
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function maybeDispatch(Faq $faq, array $data): void
    {
        if (! ($data['auto_translate'] ?? false)) {
            return;
        }

        $sourceLanguage = $this->languageRepository->findByCode(
            $data['source_locale'] ?? 'en',
        );

        if (! $sourceLanguage) {
            return;
        }

        $this->translationDispatchService->dispatchForModel(
            $faq->fresh(),
            $sourceLanguage,
            ['question', 'answer'],
            [],
            force: true,
        );
    }
}
