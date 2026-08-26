<?php

namespace Tests\Feature\Admin;

use App\Models\Faq;
use App\Models\Language;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(LanguageSeeder::class);
        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function test_guest_cannot_access_faqs(): void
    {
        $this->get(route('admin.faqs.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_faqs_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.faqs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Faqs/Index')
                ->has('faqs'));
    }

    public function test_admin_can_create_faq(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.faqs.store'), [
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'question' => 'How do we start a project?',
                        'answer' => 'We begin with a discovery workshop.',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.faqs.index'));

        $this->assertDatabaseHas('faq_translations', [
            'question' => 'How do we start a project?',
            'answer' => 'We begin with a discovery workshop.',
        ]);
    }

    public function test_source_question_and_answer_are_required(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.faqs.store'), [
                'source_locale' => 'en',
                'translations' => [
                    'en' => [
                        'question' => '',
                        'answer' => '',
                    ],
                ],
            ])
            ->assertSessionHasErrors([
                'translations.en.question',
                'translations.en.answer',
            ]);
    }

    public function test_admin_can_update_faq(): void
    {
        $english = Language::query()->where('code', 'en')->firstOrFail();
        $faq = Faq::factory()->create(['order' => 0]);
        $faq->translations()->create([
            'language_id' => $english->id,
            'question' => 'Old question?',
            'answer' => 'Old answer.',
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.faqs.update', $faq), [
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'question' => 'Updated question?',
                        'answer' => 'Updated answer.',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.faqs.index'));

        $this->assertDatabaseHas('faq_translations', [
            'faq_id' => $faq->id,
            'question' => 'Updated question?',
            'answer' => 'Updated answer.',
        ]);
    }

    public function test_admin_can_delete_faq(): void
    {
        $faq = Faq::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.faqs.destroy', $faq))
            ->assertRedirect(route('admin.faqs.index'));

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_admin_can_reorder_faqs(): void
    {
        $first = Faq::factory()->create(['order' => 0]);
        $second = Faq::factory()->create(['order' => 1]);

        $this->actingAs($this->admin)
            ->post(route('admin.faqs.reorder'), [
                'ids' => [$second->id, $first->id],
            ])
            ->assertRedirect(route('admin.faqs.index'));

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
    }
}
