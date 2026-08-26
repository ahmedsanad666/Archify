<?php

namespace Database\Factories;

use App\Models\Slider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slider>
 */
class SliderFactory extends Factory
{
    protected $model = Slider::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order' => 0,
            'is_active' => true,
        ];
    }
}
