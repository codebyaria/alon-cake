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
            // Outside the real menu sizes (15/18/20 cm), so factories never clash with the seeded catalog.
            'diameter_cm' => fake()->unique()->numberBetween(21, 99),
            'servings_min' => 6,
            'servings_max' => 8,
            'price_add' => 0,
            'is_active' => true,
            'sort' => 0,
        ];
    }
}
