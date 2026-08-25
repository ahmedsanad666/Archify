<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TeamMember;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class TeamMemberService
{
    public function __construct(
        private readonly TeamMemberRepositoryInterface $teamMemberRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function all(): Collection
    {
        return $this->teamMemberRepository->all();
    }

    public function find(int $id): ?TeamMember
    {
        return $this->teamMemberRepository->find($id);
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds): void {
            $this->teamMemberRepository->reorder($orderedIds);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TeamMember
    {
        return DB::transaction(function () use ($data) {
            $nextOrder = $data['order'] ?? ($this->teamMemberRepository->all()->max('order') + 1);

            $member = $this->teamMemberRepository->create([
                'name' => $data['name'],
                'order' => (int) $nextOrder,
                'linkedin_url' => $data['linkedin_url'] ?? null,
                'behance_url' => $data['behance_url'] ?? null,
                'instagram_url' => $data['instagram_url'] ?? null,
            ]);

            $this->syncTranslations($member, $data['translations'] ?? []);
            $this->syncMedia($member, $data);
            $this->maybeDispatch($member, $data);

            return $this->teamMemberRepository->find((int) $member->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(TeamMember $member, array $data): TeamMember
    {
        return DB::transaction(function () use ($member, $data) {
            $this->teamMemberRepository->update($member, [
                'name' => $data['name'] ?? $member->name,
                'order' => (int) ($data['order'] ?? $member->order),
                'linkedin_url' => array_key_exists('linkedin_url', $data)
                    ? $data['linkedin_url']
                    : $member->linkedin_url,
                'behance_url' => array_key_exists('behance_url', $data)
                    ? $data['behance_url']
                    : $member->behance_url,
                'instagram_url' => array_key_exists('instagram_url', $data)
                    ? $data['instagram_url']
                    : $member->instagram_url,
            ]);

            $this->syncTranslations($member, $data['translations'] ?? []);
            $this->syncMedia($member, $data);
            $this->maybeDispatch($member, $data);

            return $this->teamMemberRepository->find((int) $member->id);
        });
    }

    public function delete(TeamMember $member): void
    {
        $this->teamMemberRepository->delete($member);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(TeamMember $member, array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            $language = $this->languageRepository->findByCode($locale);

            if (! $language instanceof Language) {
                continue;
            }

            $role = trim((string) ($fields['role'] ?? ''));
            if ($role === '') {
                continue;
            }

            $member->translations()->updateOrCreate(
                ['language_id' => $language->id],
                ['role' => $role],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncMedia(TeamMember $member, array $data): void
    {
        if (! empty($data['remove_avatar'])) {
            $member->clearMediaCollection('avatar');
        }

        $file = $data['avatar'] ?? null;
        if ($file instanceof UploadedFile) {
            $member->clearMediaCollection('avatar');
            $member->addMedia($file)->toMediaCollection('avatar');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function maybeDispatch(TeamMember $member, array $data): void
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
            $member->fresh(),
            $sourceLanguage,
            ['role'],
            [],
            force: true,
        );
    }
}
