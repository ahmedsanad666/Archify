<?php

namespace Tests\Feature;

use App\Jobs\TranslateContentJob;
use App\Models\Language;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\SiteSetting;
use App\Services\TranslationDispatchService;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TranslationPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(LanguageSeeder::class);
        $this->seed(SiteSettingSeeder::class);
    }

    public function test_dispatch_is_skipped_when_auto_translate_disabled(): void
    {
        Bus::fake();

        $english = Language::query()->where('code', 'en')->firstOrFail();
        $project = $this->makeProjectWithEnglishTranslation($english);

        app(TranslationDispatchService::class)->dispatchForModel(
            $project,
            $english,
            ['name', 'short_description', 'description', 'meta_title', 'meta_description', 'meta_keywords'],
            ['slug' => 'name'],
        );

        Bus::assertNotDispatched(TranslateContentJob::class);
    }

    public function test_dispatch_queues_one_job_per_target_locale_when_enabled(): void
    {
        Bus::fake();

        SiteSetting::query()->firstOrFail()->update(['auto_translate_enabled' => true]);

        $english = Language::query()->where('code', 'en')->firstOrFail();
        $project = $this->makeProjectWithEnglishTranslation($english);

        app(TranslationDispatchService::class)->dispatchForModel(
            $project,
            $english,
            ['name', 'short_description', 'description', 'meta_title', 'meta_description', 'meta_keywords'],
            ['slug' => 'name'],
        );

        Bus::assertDispatched(TranslateContentJob::class, 2);

        $this->assertSame(
            2,
            $project->translations()->where('translation_status', 'pending')->count(),
        );
    }

    public function test_job_marks_translation_failed_when_deepl_key_missing(): void
    {
        config(['services.deepl.api_key' => '']);

        Queue::fake(); // prevent sync side effects from other jobs if any

        SiteSetting::query()->firstOrFail()->update(['auto_translate_enabled' => true]);

        $english = Language::query()->where('code', 'en')->firstOrFail();
        $turkish = Language::query()->where('code', 'tr')->firstOrFail();
        $project = $this->makeProjectWithEnglishTranslation($english);

        $pending = $project->translations()->create([
            'language_id' => $turkish->id,
            'name' => 'Pending',
            'slug' => 'pending-project-tr',
            'translation_status' => 'pending',
        ]);

        try {
            (new TranslateContentJob(
                modelClass: Project::class,
                modelId: (int) $project->id,
                sourceLanguageId: (int) $english->id,
                targetLanguageId: (int) $turkish->id,
                fields: ['name', 'short_description', 'description', 'meta_title', 'meta_description', 'meta_keywords'],
                slugFrom: ['slug' => 'name'],
            ))->handle(app(\App\Services\DeepLTranslatorService::class));

            $this->fail('Expected RuntimeException when DEEPL_API_KEY is missing');
        } catch (\Throwable $e) {
            (new TranslateContentJob(
                modelClass: Project::class,
                modelId: (int) $project->id,
                sourceLanguageId: (int) $english->id,
                targetLanguageId: (int) $turkish->id,
                fields: ['name', 'short_description', 'description', 'meta_title', 'meta_description', 'meta_keywords'],
                slugFrom: ['slug' => 'name'],
            ))->failed($e);
        }

        $this->assertSame('failed', $pending->fresh()->translation_status);
    }

    private function makeProjectWithEnglishTranslation(Language $english): Project
    {
        $category = ProjectCategory::query()->create(['order' => 1]);

        $project = Project::query()->create([
            'project_category_id' => $category->id,
            'client_name' => 'Test Client',
            'location' => 'Istanbul',
            'year' => 2026,
        ]);

        $project->translations()->create([
            'language_id' => $english->id,
            'name' => 'Noir Residence',
            'slug' => 'noir-residence',
            'short_description' => 'A quiet home',
            'description' => 'Full description',
            'translation_status' => 'manual',
        ]);

        return $project->fresh(['translations']);
    }
}
