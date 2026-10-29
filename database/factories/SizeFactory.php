<?php

namespace Database\Factories;

use App\Models\Size;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Size>
 */
class SizeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'diameter_cm' => fake()->unique()->numberBetween(10, 30),
            'servings_min' => 6,
            'servings_max' => 8,
            'price_add' => 0,
            'is_active' => true,
            'sort' => 0,
        ];
    }
}
