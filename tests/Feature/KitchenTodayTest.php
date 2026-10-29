<?php

namespace Tests\Feature;

use App\Models\Flavor;
use App\Models\Order;
use App\Models\Size;
use App\Services\KitchenToday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KitchenTodayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Wednesday: sponges for Friday, decorating for Thursday.
        $this->travelTo(Carbon::parse('2026-11-04 09:00', 'Asia/Jakarta'));
    }

    public function test_headline_is_built_from_real_orders()
    {
        Order::factory()->count(2)->create(['pickup_date' => '2026-11-05']);
        Order::factory()->create(['pickup_date' => '2026-11-06']);

        $this->assertSame(
            'Hari ini di dapur: menghias 2 kue dan memanggang 1 sponge.',
            app(KitchenToday::class)->headline(),
        );
    }

    public function test_cancelled_orders_are_left_out()
    {
        Order::factory()->cancelled()->create(['pickup_date' => '2026-11-05']);

        $this->assertSame(
            'Hari ini di dapur: menyiapkan bahan untuk pesanan berikutnya.',
            app(KitchenToday::class)->headline(),
        );
    }

    public function test_sponges_are_grouped_by_flavor_and_size()
    {
        $vanilla = Flavor::factory()->create(['name' => ['id' => 'Vanila', 'en' => 'Vanilla']]);
        $size = Size::factory()->create(['diameter_cm' => 18]);

        Order::factory()->count(2)->create([
            'pickup_date' => '2026-11-06', 'flavor_id' => $vanilla->id, 'size_id' => $size->id,
        ]);

        $this->assertSame(['Vanila 18 cm' => 2], app(KitchenToday::class)->sponges()->all());
    }

    public function test_the_kitchen_rests_on_sunday()
    {
        $this->travelTo(Carbon::parse('2026-11-08 09:00', 'Asia/Jakarta'));

        $this->assertSame('Hari ini dapur libur. Besok kami mulai lagi.', app(KitchenToday::class)->headline());
    }

    public function test_orders_get_a_readable_code()
    {
        $order = Order::factory()->create();

        $this->assertMatchesRegularExpression('/^ALN-[A-HJKMNP-Z2-9]{5}$/', $order->code);
    }
}
