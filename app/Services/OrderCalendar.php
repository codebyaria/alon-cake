<?php

namespace App\Services;

use App\Enums\DayStatus;
use App\Models\DayCapacity;
use App\Models\Order;
use App\Support\DayAvailability;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * The single source of truth for "can this day take another cake?".
 *
 * Rules (CLAUDE.md > Aturan produk):
 * - pre-order at least H-{lead_days}
 * - limited capacity per day (default, or a DayCapacity override)
 * - Sunday is a day off; an admin can close any other day
 */
class OrderCalendar
{
    public function today(): CarbonImmutable
    {
        return CarbonImmutable::today();
    }

    public function earliestDate(): CarbonImmutable
    {
        return $this->today()->addDays((int) config('alon.lead_days'));
    }

    public function lastDate(): CarbonImmutable
    {
        return $this->today()->addDays((int) config('alon.booking_horizon_days'));
    }

    public function forDate(CarbonInterface|string $date): DayAvailability
    {
        $date = CarbonImmutable::parse($date)->startOfDay();

        return $this->between($date, $date)->first();
    }

    public function isBookable(CarbonInterface|string $date): bool
    {
        return $this->forDate($date)->isBookable();
    }

    /**
     * Every day from $start to $end (inclusive), in two queries.
     *
     * @return Collection<int, DayAvailability>
     */
    public function between(CarbonInterface|string $start, CarbonInterface|string $end): Collection
    {
        $start = CarbonImmutable::parse($start)->startOfDay();
        $end = CarbonImmutable::parse($end)->startOfDay();

        $overrides = DayCapacity::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (DayCapacity $day) => $day->date->toDateString());

        $booked = Order::query()
            ->holdingCapacity()
            ->whereBetween('pickup_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('date(pickup_date) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(CarbonPeriod::create($start, $end))
            ->map(function (CarbonInterface $day) use ($overrides, $booked) {
                $day = CarbonImmutable::instance($day);
                $key = $day->toDateString();
                $override = $overrides->get($key);
                $capacity = $override?->capacity ?? (int) config('alon.default_capacity');
                $count = (int) ($booked[$key] ?? 0);

                return new DayAvailability(
                    date: $day,
                    status: $this->statusFor($day, $override, $capacity, $count),
                    capacity: $capacity,
                    booked: $count,
                );
            })
            ->values();
    }

    /**
     * A whole month, for the customer calendar.
     *
     * @return Collection<int, DayAvailability>
     */
    public function month(int $year, int $month): Collection
    {
        $first = CarbonImmutable::create($year, $month, 1);

        return $this->between($first, $first->endOfMonth());
    }

    /**
     * The first day a new order can still be placed, or null if none within the horizon.
     */
    public function firstBookableDate(): ?CarbonImmutable
    {
        return $this->between($this->earliestDate(), $this->lastDate())
            ->first(fn (DayAvailability $day) => $day->isBookable())
            ?->date;
    }

    private function statusFor(CarbonImmutable $day, ?DayCapacity $override, int $capacity, int $booked): DayStatus
    {
        // Order matters: the most permanent reason wins, so the label stays honest.
        return match (true) {
            in_array($day->dayOfWeek, config('alon.closed_weekdays'), true) => DayStatus::DayOff,
            (bool) $override?->is_closed => DayStatus::Closed,
            $day->lt($this->earliestDate()) => DayStatus::TooSoon,
            $day->gt($this->lastDate()) => DayStatus::BeyondHorizon,
            $booked >= $capacity => DayStatus::Full,
            default => DayStatus::Available,
        };
    }
}
