<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_category_id' => ProjectCategory::factory(),
            'client_name' => fake()->company(),
            'location' => fake()->city().', '.fake()->country(),
            'year' => fake()->numberBetween(2018, (int) date('Y')),
            'video_url' => null,
            'views_count' => 0,
        ];
    }
}
