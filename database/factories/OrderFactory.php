<?php

namespace Database\Factories;

use App\Enums\Fulfillment;
use App\Enums\OrderStage;
use App\Enums\PaymentMethod;
use App\Enums\TimeSlot;
use App\Models\Cake;
use App\Models\Flavor;
use App\Models\Order;
use App\Models\Size;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pickup_date' => today()->addDays(7),
            'time_slot' => fake()->randomElement(TimeSlot::cases()),
            'stage' => OrderStage::Recorded,
            'cake_id' => Cake::factory(),
            'size_id' => Size::factory(),
            'flavor_id' => Flavor::factory(),
            'cake_text' => 'happy birthday, '.fake()->firstName(),
            'card_message' => null,
            'card_from' => null,
            'fulfillment' => Fulfillment::Pickup,
            'delivery_address' => null,
            'customer_name' => fake()->name(),
            'customer_whatsapp' => '08'.fake()->numerify('##########'),
            'locale' => 'id',
            'cake_price' => 300000,
            'delivery_fee' => 0,
            'total' => 300000,
            'payment_method' => PaymentMethod::Qris,
            'paid_at' => now(),
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'stage' => OrderStage::Cancelled,
        ]);
    }

    public function delivered(): static
    {
        $fee = Fulfillment::Delivery->fee();

        return $this->state(fn (array $attributes) => [
            'fulfillment' => Fulfillment::Delivery,
            'delivery_address' => fake()->streetAddress(),
            'delivery_fee' => $fee,
            'total' => $attributes['cake_price'] + $fee,
        ]);
    }
}
