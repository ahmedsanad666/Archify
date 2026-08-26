<?php

namespace Database\Factories;

use App\Models\CoreValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoreValue>
 */
class CoreValueFactory extends Factory
{
    protected $model = CoreValue::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'icon' => fake()->randomElement(['leaf', 'compass', 'users', 'hammer', 'world']),
            'order' => 0,
        ];
    }
}
