<?php

namespace Database\Factories;

use App\Models\Flavor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Flavor>
 */
class FlavorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $word = fake()->unique()->word();

        return [
            'slug' => $word,
            'name' => ['id' => ucfirst($word), 'en' => ucfirst($word)],
            'is_active' => true,
            'sort' => 0,
        ];
    }
}
