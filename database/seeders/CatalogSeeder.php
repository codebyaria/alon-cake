<?php

namespace Database\Seeders;

use App\Models\Cake;
use App\Models\Flavor;
use App\Models\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * The menu: three cakes, three sizes, three flavors (wireframe "Order 2–3").
 *
 * Prices and the names of cakes 2 and 3 are demo values; edit them in /admin.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Cakes differ by decoration; flavour is chosen separately in the order flow.
        $cakes = [
            [
                'slug' => 'good-things-take-time',
                'name' => 'Good things take time',
                'description' => [
                    'id' => 'Kue andalan kami. Garis piping bergelombang di tepi atas dan tulisan tangan pilihanmu di tengahnya.',
                    'en' => 'Our signature cake. A wavy piped border around the top and your own words handwritten in the middle.',
                ],
                'decor_note' => ['id' => 'Piping gelombang, tulisan tangan', 'en' => 'Wavy piping, handwriting'],
                'base_price' => 285000,
            ],
            [
                'slug' => 'slow-mornings',
                'name' => 'Slow mornings',
                'description' => [
                    'id' => 'Krim polos dengan lingkaran titik-titik cokelat kecil di tepinya. Tenang, seperti pagi tanpa terburu-buru.',
                    'en' => 'Plain cream with a ring of small chocolate dots around the edge. Quiet, like a morning with no rush.',
                ],
                'decor_note' => ['id' => 'Titik cokelat', 'en' => 'Chocolate dots'],
                'base_price' => 300000,
            ],
            [
                'slug' => 'small-joys',
                'name' => 'Small joys',
                'description' => [
                    'id' => 'Satu hati kecil berwarna dusty pink di atas, dan garis piping bergelombang di dasarnya.',
                    'en' => 'One small dusty pink heart on top, and a wavy piped line around the base.',
                ],
                'decor_note' => ['id' => 'Hati kecil, piping di dasar', 'en' => 'Small heart, piped base'],
                'base_price' => 300000,
            ],
        ];

        foreach ($cakes as $sort => $cake) {
            Cake::updateOrCreate(
                ['slug' => $cake['slug']],
                $cake + ['sort' => $sort, 'photo_path' => $this->publishPhoto($cake['slug'])],
            );
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

    /**
     * Copy a bundled demo photo onto the public disk, where admin uploads live too.
     */
    private function publishPhoto(string $slug): ?string
    {
        $source = database_path("seeders/photos/{$slug}.webp");

        if (! is_file($source)) {
            return null;
        }

        $path = "cakes/{$slug}.webp";
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }
}
