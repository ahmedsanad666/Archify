<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => null,
            'service_id' => null,
            'interest_other' => null,
            'message' => fake()->paragraph(),
            'status' => fake()->randomElement(['pending', 'contacted']),
            'internal_notes' => null,
            'language_id' => null,
            'ip_address' => fake()->ipv4(),
        ];
    }
}
