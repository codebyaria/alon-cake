<?php

namespace App\Http\Controllers;

use App\Models\Cake;
use App\Services\KitchenToday;
use App\Services\OrderCalendar;
use App\Support\DayAvailability;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /** Two weeks, starting on the Monday of the first week a cake can still be ordered for. */
    private const CALENDAR_DAYS = 14;

    public function __invoke(OrderCalendar $calendar, KitchenToday $kitchen): Response
    {
        $start = $calendar->earliestDate()->startOfWeek();
        $days = $calendar->between($start, $start->addDays(self::CALENDAR_DAYS - 1));

        return Inertia::render('home', [
            'kitchenToday' => $kitchen->headline(),
            'leadDays' => (int) config('alon.lead_days'),
            'cakes' => Cake::onMenu()->get()->map(fn (Cake $cake) => [
                'slug' => $cake->slug,
                'name' => $cake->name,
                'description' => $cake->translate('description'),
                'decorNote' => $cake->translate('decor_note'),
                'priceFrom' => $cake->base_price,
                'photoUrl' => $cake->photoUrl(),
            ]),
            'calendar' => [
                'days' => $days->map(fn (DayAvailability $day) => $day->toArray()),
                'note' => $this->calendarNote($days),
            ],
            'deliveryFee' => (int) config('alon.delivery_fee'),
        ]);
    }

    /**
     * An honest sentence about the coming weeks. Never urgency, only where there is room.
     *
     * @param  Collection<int, DayAvailability>  $days
     */
    private function calendarNote(Collection $days): ?string
    {
        [$thisWeek, $nextWeek] = $days->chunk(7)->map->values()->all() + [1 => collect()];

        $openThisWeek = $thisWeek->filter->isBookable();
        $openNextWeek = $nextWeek->filter->isBookable();
        $placesThisWeek = $openThisWeek->sum(fn (DayAvailability $day) => $day->remaining());

        return match (true) {
            $openThisWeek->isEmpty() && $openNextWeek->isNotEmpty() => __('Minggu ini sudah penuh. Masih ada tempat untuk minggu depan.'),
            $placesThisWeek <= 3 && $openNextWeek->isNotEmpty() => __('Minggu ini hampir penuh. Masih ada tempat untuk minggu depan.'),
            $openThisWeek->isEmpty() && $openNextWeek->isEmpty() => __('Dua minggu ini sudah penuh. Kalender bulan berikutnya tetap terbuka.'),
            default => null,
        };
    }
}
