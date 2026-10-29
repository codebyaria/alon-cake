<?php

namespace Database\Factories;

use App\Models\Cake;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cake>
 */
class CakeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::lower(fake()->unique()->words(3, true));

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'description' => ['id' => fake()->sentence(), 'en' => fake()->sentence()],
            'flavor_note' => ['id' => 'Vanila, krim susu', 'en' => 'Vanilla, milk cream'],
            'base_price' => 300000,
            'is_active' => true,
            'sort' => 0,
        ];
    }
}
