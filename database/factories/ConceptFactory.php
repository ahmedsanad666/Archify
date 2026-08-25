<?php

namespace Database\Factories;

use App\Models\Concept;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Concept>
 */
class ConceptFactory extends Factory
{
    protected $model = Concept::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'icon' => fake()->randomElement([
                'compass',
                'leaf',
                'ruler',
                'users',
                'building',
                'sofa',
                'home',
            ]),
        ];
    }
}
