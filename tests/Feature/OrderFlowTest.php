<?php

namespace Tests\Feature;

use App\Enums\Fulfillment;
use App\Enums\OrderStage;
use App\Models\Cake;
use App\Models\Flavor;
use App\Models\Order;
use App\Models\Size;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private Cake $cake;

    private Size $size;

    private Flavor $flavor;

    protected function setUp(): void
    {
        parent::setUp();

        // Friday 30 October 2026: H-3 is Monday 2 November, Sunday 8 is a day off.
        $this->travelTo(Carbon::parse('2026-10-30 10:00', 'Asia/Jakarta'));
        $this->seed(CatalogSeeder::class);

        $this->cake = Cake::where('slug', 'slow-mornings')->first();
        $this->size = Size::where('diameter_cm', 18)->first();
        $this->flavor = Flavor::where('slug', 'cokelat')->first();
    }

    /**
     * Walk steps 1–4 and return the checkout payload.
     *
     * @return array<string, string>
     */
    private function fillDraft(string $prefix = '', string $date = '2026-11-05'): array
    {
        $this->post("{$prefix}/pesan", ['date' => $date])->assertRedirect("{$prefix}/pesan/kue");
        $this->post("{$prefix}/pesan/kue", ['cake_id' => $this->cake->id])->assertRedirect("{$prefix}/pesan/detail");
        $this->post("{$prefix}/pesan/detail", [
            'size_id' => $this->size->id,
            'flavor_id' => $this->flavor->id,
            'cake_text' => 'happy birthday, Sekar',
        ])->assertRedirect("{$prefix}/pesan/kartu");
        $this->post("{$prefix}/pesan/kartu", [
            'card_message' => 'Untuk hari-hari pelan yang kita suka.',
            'card_from' => 'Raka',
        ])->assertRedirect("{$prefix}/pesan/bayar");

        return [
            'fulfillment' => 'pickup',
            'time_slot' => 'morning',
            'customer_name' => 'Raka Pratama',
            'customer_whatsapp' => '0812 3456 7890',
            'payment_method' => 'qris',
        ];
    }

    public function test_a_customer_can_order_a_cake_from_date_to_confirmation()
    {
        $checkout = $this->fillDraft();

        $this->get('/pesan/bayar')->assertInertia(fn (Assert $page) => $page
            ->component('order/checkout')
            ->where('summary.cake', 'Slow mornings')
            ->where('summary.cakePrice', 390000)
            ->where('summary.hasCard', true));

        $response = $this->post('/pesan/bayar', $checkout);

        $order = Order::sole();
        $response->assertRedirect("/pesan/selesai/{$order->code}");

        $this->assertSame('2026-11-05', $order->pickup_date->toDateString());
        $this->assertSame(OrderStage::Recorded, $order->stage);
        $this->assertSame(390000, $order->total);
        $this->assertSame('081234567890', $order->customer_whatsapp);
        $this->assertSame('happy birthday, Sekar', $order->cake_text);
        $this->assertNotNull($order->paid_at);

        $this->get("/pesan/selesai/{$order->code}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('order/done')
                ->where('order.date', '2026-11-05')
                ->where('timeline.1.date', '2026-11-03')
                ->where('timeline.3.label', 'Siap diambil, 10.00–12.00')
                ->where('whatsAppPreview', fn (string $text) => str_contains($text, $order->code)));

        // The draft is gone: starting again begins at the date.
        $this->get('/pesan/kue')->assertRedirect('/pesan');
    }

    public function test_delivery_adds_the_fee_and_needs_an_address()
    {
        $checkout = ['fulfillment' => 'delivery', 'time_slot' => 'afternoon'] + $this->fillDraft();

        $this->post('/pesan/bayar', $checkout)->assertSessionHasErrors([
            'delivery_address' => 'Tulis alamat pengantaran, ya.',
        ]);

        $this->post('/pesan/bayar', $checkout + ['delivery_address' => 'Jl. Kaliurang Km 5'])->assertRedirect();

        $order = Order::sole();
        $this->assertSame(Fulfillment::Delivery, $order->fulfillment);
        $this->assertSame(25000, $order->delivery_fee);
        $this->assertSame(415000, $order->total);
    }

    public function test_the_card_can_be_skipped()
    {
        $this->post('/pesan', ['date' => '2026-11-05']);
        $this->post('/pesan/kue', ['cake_id' => $this->cake->id]);
        $this->post('/pesan/detail', ['size_id' => $this->size->id, 'flavor_id' => $this->flavor->id]);

        $this->post('/pesan/kartu', ['skip' => true])->assertRedirect('/pesan/bayar');
        $this->get('/pesan/bayar')->assertInertia(fn (Assert $page) => $page->where('summary.hasCard', false));
    }

    public function test_steps_cannot_be_skipped()
    {
        $this->get('/pesan/kue')->assertRedirect('/pesan');

        $this->post('/pesan', ['date' => '2026-11-05']);
        $this->post('/pesan/kue', ['cake_id' => $this->cake->id]);

        $this->get('/pesan/bayar')->assertRedirect('/pesan/detail');
    }

    public function test_sundays_and_too_soon_dates_are_refused()
    {
        $this->post('/pesan', ['date' => '2026-11-08'])->assertSessionHasErrors('date');
        $this->post('/pesan', ['date' => '2026-11-01'])->assertSessionHasErrors('date');
        $this->post('/pesan', ['date' => '2026-10-31'])->assertSessionHasErrors('date');
    }

    public function test_a_date_that_fills_up_before_payment_is_not_overbooked()
    {
        $checkout = $this->fillDraft();

        // Meanwhile, someone else takes the last places.
        Order::factory()->count(5)->create([
            'pickup_date' => '2026-11-05',
            'cake_id' => $this->cake->id,
            'size_id' => $this->size->id,
            'flavor_id' => $this->flavor->id,
        ]);

        $this->post('/pesan/bayar', $checkout)->assertRedirect('/pesan');
        $this->assertSame(5, Order::forDate('2026-11-05')->count());
    }

    public function test_whatsapp_number_must_look_like_an_indonesian_mobile()
    {
        $checkout = $this->fillDraft();

        $this->post('/pesan/bayar', ['customer_whatsapp' => '12345'] + $checkout)
            ->assertSessionHasErrors('customer_whatsapp');

        $this->post('/pesan/bayar', ['customer_whatsapp' => '+62 812-3456-7890'] + $checkout)->assertRedirect();
        $this->assertSame('081234567890', Order::sole()->customer_whatsapp);
    }

    public function test_writing_on_the_cake_is_limited_to_24_characters()
    {
        $this->post('/pesan', ['date' => '2026-11-05']);
        $this->post('/pesan/kue', ['cake_id' => $this->cake->id]);

        $this->post('/pesan/detail', [
            'size_id' => $this->size->id,
            'flavor_id' => $this->flavor->id,
            'cake_text' => str_repeat('a', 25),
        ])->assertSessionHasErrors(['cake_text' => 'Tulisan di kue paling banyak 24 huruf.']);
    }

    public function test_a_cake_off_the_menu_cannot_be_ordered()
    {
        $this->cake->update(['is_active' => false]);
        $this->post('/pesan', ['date' => '2026-11-05']);

        $this->post('/pesan/kue', ['cake_id' => $this->cake->id])->assertSessionHasErrors('cake_id');
    }

    public function test_the_confirmation_is_only_visible_to_the_customer_who_ordered()
    {
        $this->post('/pesan/bayar', $this->fillDraft());
        $code = Order::sole()->code;

        $this->get("/pesan/selesai/{$code}")->assertOk();

        $this->flushSession();
        $this->get("/pesan/selesai/{$code}")->assertNotFound();
    }

    public function test_the_calendar_file_holds_the_pickup_window()
    {
        $this->post('/pesan/bayar', $this->fillDraft());
        $code = Order::sole()->code;

        $response = $this->get("/pesan/selesai/{$code}/kalender.ics");

        $response->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('DTSTART;TZID=Asia/Jakarta:20261105T100000', $response->getContent());
        $this->assertStringContainsString('DTEND;TZID=Asia/Jakarta:20261105T120000', $response->getContent());
    }

    public function test_the_english_flow_stays_in_english()
    {
        $checkout = $this->fillDraft('/en');

        $this->post('/en/pesan/bayar', ['customer_whatsapp' => '123'] + $checkout)
            ->assertSessionHasErrors([
                'customer_whatsapp' => 'Use a WhatsApp number starting with 08 or +62, for example 0812 3456 7890.',
            ]);

        $this->post('/en/pesan/bayar', $checkout);
        $order = Order::sole();

        $this->assertSame('en', $order->locale);
        $this->get("/en/pesan/selesai/{$order->code}")->assertInertia(fn (Assert $page) => $page
            ->where('timeline.3.label', 'Ready to pick up, 10.00–12.00')
            ->where('whatsAppPreview', fn (string $text) => str_starts_with($text, 'Hi Raka, thank you for ordering from Alon.')));
    }

    public function test_date_step_preselects_a_date_from_the_homepage()
    {
        $this->get('/pesan?tanggal=2026-11-12')->assertInertia(fn (Assert $page) => $page
            ->component('order/date')
            ->where('selected', '2026-11-12')
            ->where('month', '2026-11')
            ->where('prevMonth', null)
            ->where('nextMonth', '2026-12'));
    }
}
