<?php

namespace Database\Seeders;

use App\Enums\Fulfillment;
use App\Enums\OrderStage;
use App\Enums\PaymentMethod;
use App\Enums\TimeSlot;
use App\Models\Cake;
use App\Models\DayCapacity;
use App\Models\Flavor;
use App\Models\Order;
use App\Models\Size;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Orders and capacity around *today*, so the demo always looks alive:
 * past days done, today's cakes ready, the next days in the kitchen,
 * a couple of full days soon after, and quieter weeks further out.
 *
 * Seeded randomness keeps every run the same for the same date.
 */
class DemoOrderSeeder extends Seeder
{
    /** Orders on the first open days from H-3 on: two full days, then easing off. */
    private const BUSY = [5, 5, 4, 3];

    private const NAMES = [
        'Nara', 'Bima', 'Laras', 'Dimas', 'Sekar', 'Raka', 'Ayu', 'Tirta', 'Kinan', 'Galih',
        'Wulan', 'Arya', 'Citra', 'Bayu', 'Maya', 'Satria', 'Anjani', 'Yoga', 'Kirana', 'Damar',
    ];

    private const CARDS = [
        ['Selamat ulang tahun. Pelan-pelan saja, semua yang baik akan datang.', 'Ibu'],
        ['Terima kasih sudah sabar menemani tahun ini.', 'Raka'],
        ['Tiga puluh tahun, dan kamu masih yang paling hangat.', 'Kami semua'],
        ['Untuk hari-hari pelan yang kita suka.', 'Sekar'],
        [null, null],
        [null, null],
    ];

    public function run(): void
    {
        $today = CarbonImmutable::today();
        mt_srand((int) $today->format('Ymd'));
        fake()->seed((int) $today->format('Ymd'));

        $cakes = Cake::orderBy('sort')->get();
        $sizes = Size::orderBy('sort')->get();
        $flavors = Flavor::orderBy('sort')->get();

        // The kitchen closes one weekday for restocking, and runs lighter on another.
        $closed = $this->nextWeekday($today->addDays(10));
        $lighter = $this->nextWeekday($closed->addDay());

        DayCapacity::updateOrCreate(['date' => $closed->toDateString()], [
            'capacity' => 0, 'is_closed' => true, 'note' => 'Belanja bahan dan bersih-bersih dapur',
        ]);
        DayCapacity::updateOrCreate(['date' => $lighter->toDateString()], [
            'capacity' => 3, 'is_closed' => false, 'note' => 'Satu tangan saja hari ini',
        ]);

        $openDaysAhead = 0;

        for ($offset = -7; $offset <= 21; $offset++) {
            $date = $today->addDays($offset);

            if ($date->isSunday() || $date->equalTo($closed)) {
                continue;
            }

            $count = match (true) {
                $offset < (int) config('alon.lead_days') => mt_rand(2, 4),
                default => self::BUSY[$openDaysAhead++] ?? mt_rand(0, 2),
            };

            if ($date->equalTo($lighter)) {
                $count = min($count, 3);
            }

            for ($i = 0; $i < $count; $i++) {
                $this->makeOrder($date, $offset, $i, $cakes->random(), $sizes->random(), $flavors->random());
            }
        }

        mt_srand();
    }

    private function makeOrder(CarbonImmutable $date, int $offset, int $index, Cake $cake, Size $size, Flavor $flavor): void
    {
        $name = self::NAMES[mt_rand(0, count(self::NAMES) - 1)];
        [$message, $from] = self::CARDS[mt_rand(0, count(self::CARDS) - 1)];
        $fulfillment = mt_rand(0, 2) === 0 ? Fulfillment::Delivery : Fulfillment::Pickup;
        $cakePrice = $cake->priceFor($size);
        $fee = $fulfillment->fee();
        // Most orders are placed a week or two ahead; never later than H-3.
        $placedAt = $date->subDays(mt_rand(3, 14))->setTime(mt_rand(8, 21), mt_rand(0, 59));

        Order::create([
            'pickup_date' => $date,
            'time_slot' => TimeSlot::cases()[$index % 3],
            'stage' => $this->stageFor($offset),
            'cake_id' => $cake->id,
            'size_id' => $size->id,
            'flavor_id' => $flavor->id,
            'cake_text' => mt_rand(0, 3) === 0 ? null : "happy birthday, {$name}",
            'card_message' => $message,
            'card_from' => $from,
            'fulfillment' => $fulfillment,
            'delivery_address' => $fulfillment === Fulfillment::Delivery ? fake()->streetAddress().', Yogyakarta' : null,
            'customer_name' => fake()->name(),
            'customer_whatsapp' => '08'.mt_rand(11, 99).mt_rand(1000, 9999).mt_rand(1000, 9999),
            'locale' => mt_rand(0, 5) === 0 ? 'en' : 'id',
            'cake_price' => $cakePrice,
            'delivery_fee' => $fee,
            'total' => $cakePrice + $fee,
            'payment_method' => mt_rand(0, 1) ? PaymentMethod::Qris : PaymentMethod::BankTransfer,
            'paid_at' => $placedAt->addMinutes(mt_rand(2, 30)),
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ]);
    }

    /**
     * Stage follows the kitchen timeline relative to the pickup day.
     */
    private function stageFor(int $offset): OrderStage
    {
        return match (true) {
            $offset < 0 => OrderStage::Completed,
            $offset === 0 => OrderStage::Ready,
            $offset === 1 => OrderStage::Decorating,
            $offset === 2 => OrderStage::Baking,
            default => OrderStage::Recorded,
        };
    }

    private function nextWeekday(CarbonImmutable $date): CarbonImmutable
    {
        return $date->isSunday() ? $date->addDay() : $date;
    }
}
