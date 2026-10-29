<?php

namespace Database\Factories;

use App\Models\DayCapacity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DayCapacity>
 */
class DayCapacityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => today()->addWeek(),
            'capacity' => 3,
            'is_closed' => false,
        ];
    }

    /**
     * The kitchen is closed that day.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_closed' => true,
        ]);
    }
}
