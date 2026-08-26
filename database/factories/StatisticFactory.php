<?php

namespace Database\Factories;

use App\Models\Statistic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Statistic>
 */
class StatisticFactory extends Factory
{
    protected $model = Statistic::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'count' => fake()->numberBetween(10, 500),
            'order' => 0,
        ];
    }
}
