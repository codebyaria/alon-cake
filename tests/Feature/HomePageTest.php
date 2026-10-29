<?php

namespace Tests\Feature;

use App\Models\Cake;
use App\Models\Flavor;
use App\Models\Order;
use App\Models\Size;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Friday 30 October 2026: H-3 is Monday 2 November.
        $this->travelTo(Carbon::parse('2026-10-30 10:00', 'Asia/Jakarta'));
        $this->seed(CatalogSeeder::class);
    }

    public function test_home_shows_menu_kitchen_line_and_two_week_calendar()
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('home')
                ->where('locale', 'id')
                ->where('kitchenToday', 'Hari ini di dapur: menyiapkan bahan untuk pesanan berikutnya.')
                ->has('cakes', 3)
                ->where('cakes.0.name', 'Good things take time')
                ->where('cakes.0.priceFrom', 285000)
                ->has('calendar.days', 14)
                ->where('calendar.days.0.date', '2026-11-02')
                ->where('calendar.days.6.status', 'day_off'));
    }

    public function test_english_version_lives_under_en()
    {
        Order::factory()->create(['pickup_date' => '2026-10-31']);

        $this->get('/en')
            ->assertOk()
            ->assertSee('<html lang="en"', false)
            ->assertInertia(fn (Assert $page) => $page
                ->component('home')
                ->where('locale', 'en')
                ->where('kitchenToday', 'Today in the kitchen: decorating 1 cake.')
                ->where('cakes.0.flavorNote', 'Vanilla, milk cream')
                ->where('translations.Pesan', 'Order'));
    }

    public function test_calendar_note_says_when_this_week_is_full()
    {
        $menu = ['cake_id' => Cake::first()->id, 'size_id' => Size::first()->id, 'flavor_id' => Flavor::first()->id];

        foreach (['2026-11-02', '2026-11-03', '2026-11-04', '2026-11-05', '2026-11-06', '2026-11-07'] as $date) {
            Order::factory()->count(5)->create(['pickup_date' => $date] + $menu);
        }

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('calendar.note', 'Minggu ini sudah penuh. Masih ada tempat untuk minggu depan.'));
    }

    public function test_calendar_has_no_note_when_there_is_room()
    {
        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('calendar.note', null));
    }

    public function test_order_start_shows_the_chosen_date()
    {
        $this->get('/pesan?tanggal=2026-11-04')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('order/start')
                ->where('day.date', '2026-11-04')
                ->where('day.bookable', true));
    }

    public function test_order_start_explains_an_unavailable_date()
    {
        $this->get('/en/pesan?tanggal=2026-11-08')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('day.status', 'day_off')
                ->where('day.bookable', false)
                ->where('firstBookable', '2026-11-02'));
    }

    public function test_order_start_rejects_a_malformed_date()
    {
        $this->get('/pesan?tanggal=besok')->assertRedirect();
    }

    public function test_admin_stays_indonesian()
    {
        $this->get('/admin/login')->assertSee('lang="id"', false);
    }
}
