<?php

namespace App\Services;

use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * What the kitchen is doing today, derived from real orders (never hard-coded).
 *
 * Feeds the hero line "Hari ini di dapur: ..." and the admin's daily prep list.
 * The timeline matches the confirmation page: sponge on H-2, decorate on H-1,
 * ready on day H.
 */
class KitchenToday
{
    public function __construct(private ?CarbonImmutable $today = null) {}

    public function today(): CarbonImmutable
    {
        return $this->today ?? CarbonImmutable::today();
    }

    /**
     * Sponges to bake today, grouped like the prep list: "Vanila 18 cm" => 2.
     *
     * @return Collection<string, int>
     */
    public function sponges(): Collection
    {
        return $this->ordersFor($this->today()->addDays(2))
            ->groupBy(fn (Order $order) => $order->flavor->translate('name').' '.$order->size->label)
            ->map->count()
            ->sortDesc();
    }

    /**
     * @return EloquentCollection<int, Order>
     */
    public function toDecorate(): EloquentCollection
    {
        return $this->ordersFor($this->today()->addDay());
    }

    public function cardsToWrite(): int
    {
        return $this->toDecorate()->whereNotNull('card_message')->count();
    }

    /**
     * @return EloquentCollection<int, Order>
     */
    public function toHandOver(): EloquentCollection
    {
        return $this->ordersFor($this->today());
    }

    /**
     * One honest line for the hero. Mentions the most hands-on task of the day.
     */
    public function headline(): string
    {
        if (in_array($this->today()->dayOfWeek, config('alon.closed_weekdays'), true)) {
            return __('Hari ini dapur libur. Besok kami mulai lagi.');
        }

        $decorate = $this->toDecorate()->count();
        $sponges = $this->sponges()->sum();
        $handOver = $this->toHandOver()->count();

        $parts = array_filter([
            // Indonesian nouns don't pluralise; lang/en.json can use trans_choice keys later.
            $decorate > 0 ? __('menghias :count kue', ['count' => $decorate]) : null,
            $sponges > 0 ? __('memanggang :count sponge', ['count' => $sponges]) : null,
            $handOver > 0 ? __('menyerahkan :count kue', ['count' => $handOver]) : null,
        ]);

        if ($parts === []) {
            return __('Hari ini di dapur: menyiapkan bahan untuk pesanan berikutnya.');
        }

        return __('Hari ini di dapur: :tasks.', ['tasks' => $this->joinList($parts)]);
    }

    /**
     * @return EloquentCollection<int, Order>
     */
    private function ordersFor(CarbonImmutable $date): EloquentCollection
    {
        return Order::query()
            ->holdingCapacity()
            ->forDate($date)
            ->with(['cake', 'size', 'flavor'])
            ->orderBySlot()
            ->get();
    }

    /**
     * @param  array<int, string>  $items
     */
    private function joinList(array $items): string
    {
        $items = array_values($items);
        $last = array_pop($items);

        return $items === [] ? $last : implode(', ', $items).' '.__('dan').' '.$last;
    }
}
