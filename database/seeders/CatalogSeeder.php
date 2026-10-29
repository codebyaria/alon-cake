<?php

namespace Database\Seeders;

use App\Models\Cake;
use App\Models\Flavor;
use App\Models\Size;
use Illuminate\Database\Seeder;

/**
 * The menu: three cakes, three sizes, three flavors (wireframe "Order 2–3").
 *
 * Prices and the names of cakes 2 and 3 are demo values; edit them in /admin.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $cakes = [
            [
                'slug' => 'good-things-take-time',
                'name' => 'Good things take time',
                'description' => [
                    'id' => 'Kue andalan kami. Krim polos, garis piping cokelat, dan tulisan tangan di atasnya.',
                    'en' => 'Our signature cake. Plain cream, chocolate piping, and handwriting on top.',
                ],
                'flavor_note' => ['id' => 'Vanila, krim susu', 'en' => 'Vanilla, milk cream'],
                'base_price' => 285000,
            ],
            [
                'slug' => 'slow-mornings',
                'name' => 'Slow mornings',
                'description' => [
                    'id' => 'Sponge cokelat yang lembap, ganache tipis, dan titik-titik kecil di tepinya.',
                    'en' => 'Moist chocolate sponge, a thin ganache, and small dots around the edge.',
                ],
                'flavor_note' => ['id' => 'Cokelat, ganache', 'en' => 'Chocolate, ganache'],
                'base_price' => 315000,
            ],
            [
                'slug' => 'small-joys',
                'name' => 'Small joys',
                'description' => [
                    'id' => 'Segar dan ringan. Lemon curd di antara lapisan, hati kecil di atasnya.',
                    'en' => 'Fresh and light. Lemon curd between the layers, a small heart on top.',
                ],
                'flavor_note' => ['id' => 'Lemon, krim keju', 'en' => 'Lemon, cream cheese'],
                'base_price' => 325000,
            ],
        ];

        foreach ($cakes as $sort => $cake) {
            Cake::updateOrCreate(['slug' => $cake['slug']], $cake + ['sort' => $sort]);
        }

        $sizes = [
            ['diameter_cm' => 15, 'servings_min' => 6, 'servings_max' => 8, 'price_add' => 0],
            ['diameter_cm' => 18, 'servings_min' => 10, 'servings_max' => 12, 'price_add' => 90000],
            ['diameter_cm' => 20, 'servings_min' => 14, 'servings_max' => 16, 'price_add' => 160000],
        ];

        foreach ($sizes as $sort => $size) {
            Size::updateOrCreate(['diameter_cm' => $size['diameter_cm']], $size + ['sort' => $sort]);
        }

        $flavors = [
            ['slug' => 'vanila', 'name' => ['id' => 'Vanila', 'en' => 'Vanilla']],
            ['slug' => 'cokelat', 'name' => ['id' => 'Cokelat', 'en' => 'Chocolate']],
            ['slug' => 'lemon', 'name' => ['id' => 'Lemon', 'en' => 'Lemon']],
        ];

        foreach ($flavors as $sort => $flavor) {
            Flavor::updateOrCreate(['slug' => $flavor['slug']], $flavor + ['sort' => $sort]);
        }
    }
}
