<?php

namespace Tests\Feature;

use App\Models\Cake;
use App\Models\Flavor;
use App\Models\Order;
use App\Models\Size;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
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
                ->where('cakes.0.photoUrl', fn (string $url) => str_ends_with($url, '/storage/cakes/good-things-take-time.webp'))
                ->has('calendar.days', 14)
                ->where('calendar.days.0.date', '2026-11-02')
                ->where('calendar.days.6.status', 'day_off'));
    }

    public function test_seeded_menu_photos_are_published_to_the_public_disk()
    {
        foreach (['good-things-take-time', 'slow-mornings', 'small-joys'] as $slug) {
            Storage::disk('public')->assertExists("cakes/{$slug}.webp");
        }
    }

    public function test_link_previews_and_language_alternates_are_server_rendered()
    {
        $this->get('/en')
            ->assertSee('<meta name="description" content="Birthday cakes made to order', false)
            ->assertSee('<meta property="og:image" content="'.url('/images/og.jpg').'"', false)
            ->assertSee('<link rel="canonical" href="'.url('/en').'"', false)
            ->assertSee('<link rel="alternate" hreflang="id" href="'.url('/').'"', false);

        $this->get('/pesan/kue')->assertRedirect('/pesan');
        $this->get('/playground')->assertSee('<meta name="robots" content="noindex">', false);
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
                ->where('cakes.0.decorNote', 'Wavy piping, handwriting')
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

    public function test_admin_stays_indonesian()
    {
        $this->get('/admin/login')->assertSee('lang="id"', false);
    }
}
