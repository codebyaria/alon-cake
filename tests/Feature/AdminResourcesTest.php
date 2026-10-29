<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Cake;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\DemoOrderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([CatalogSeeder::class, DemoOrderSeeder::class]);
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_every_admin_page_renders()
    {
        $order = Order::first();
        $cake = Cake::first();

        foreach ([
            '/admin',
            '/admin/orders',
            "/admin/orders/{$order->id}/edit",
            '/admin/cakes',
            '/admin/cakes/create',
            "/admin/cakes/{$cake->id}/edit",
            '/admin/sizes',
            '/admin/flavors',
            '/admin/day-capacities',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_orders_cannot_be_created_from_admin()
    {
        $this->get('/admin/orders/create')->assertNotFound();
    }

    public function test_today_tab_shows_only_todays_orders()
    {
        $today = Order::forDate(today())->get();
        $other = Order::where('pickup_date', '!=', today()->toDateString())->get();

        Livewire::test(ListOrders::class, ['activeTab' => 'today'])
            ->assertCanSeeTableRecords($today)
            ->assertCanNotSeeTableRecords($other);
    }
}
