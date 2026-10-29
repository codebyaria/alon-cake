<?php

namespace Tests\Feature;

use App\Enums\DayStatus;
use App\Enums\TimeSlot;
use App\Models\DayCapacity;
use App\Models\Order;
use App\Services\OrderCalendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderCalendarTest extends TestCase
{
    use RefreshDatabase;

    private OrderCalendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        // Wednesday 4 November 2026: H-3 is Saturday 7, Sunday 8 is a day off.
        $this->travelTo(Carbon::parse('2026-11-04 15:30', 'Asia/Jakarta'));
        config(['alon.lead_days' => 3, 'alon.default_capacity' => 5, 'alon.booking_horizon_days' => 60]);

        $this->calendar = new OrderCalendar;
    }

    public function test_days_before_h_minus_3_are_too_soon()
    {
        foreach (['2026-11-04', '2026-11-05', '2026-11-06'] as $date) {
            $this->assertSame(DayStatus::TooSoon, $this->calendar->forDate($date)->status, $date);
        }

        $this->assertSame(DayStatus::Available, $this->calendar->forDate('2026-11-07')->status);
    }

    public function test_sunday_is_a_day_off()
    {
        $this->assertSame(DayStatus::DayOff, $this->calendar->forDate('2026-11-08')->status);
        $this->assertFalse($this->calendar->isBookable('2026-11-08'));
    }

    public function test_a_day_is_full_once_it_reaches_default_capacity()
    {
        Order::factory()->count(4)->create(['pickup_date' => '2026-11-10']);
        $day = $this->calendar->forDate('2026-11-10');

        $this->assertSame(DayStatus::Available, $day->status);
        $this->assertSame(1, $day->remaining());

        Order::factory()->create(['pickup_date' => '2026-11-10']);

        $this->assertSame(DayStatus::Full, $this->calendar->forDate('2026-11-10')->status);
    }

    public function test_cancelled_orders_free_their_place()
    {
        Order::factory()->count(5)->cancelled()->create(['pickup_date' => '2026-11-10']);

        $day = $this->calendar->forDate('2026-11-10');

        $this->assertSame(0, $day->booked);
        $this->assertTrue($day->isBookable());
    }

    public function test_admin_capacity_override_applies_to_a_single_day_lookup()
    {
        DayCapacity::factory()->create(['date' => '2026-11-10', 'capacity' => 2]);
        Order::factory()->count(2)->create(['pickup_date' => '2026-11-10']);

        $day = $this->calendar->forDate('2026-11-10');

        $this->assertSame(2, $day->capacity);
        $this->assertSame(DayStatus::Full, $day->status);
    }

    public function test_admin_can_close_a_day()
    {
        DayCapacity::factory()->closed()->create(['date' => '2026-11-11']);

        $this->assertSame(DayStatus::Closed, $this->calendar->forDate('2026-11-11')->status);
    }

    public function test_days_past_the_booking_horizon_are_not_open_yet()
    {
        $this->assertSame(DayStatus::Available, $this->calendar->forDate('2027-01-02')->status);
        $this->assertSame(DayStatus::BeyondHorizon, $this->calendar->forDate('2027-01-04')->status);
    }

    public function test_first_bookable_date_skips_full_days_and_sundays()
    {
        Order::factory()->count(5)->create(['pickup_date' => '2026-11-07']);

        $this->assertSame('2026-11-09', $this->calendar->firstBookableDate()?->toDateString());
    }

    public function test_a_month_has_one_entry_per_day()
    {
        $november = $this->calendar->month(2026, 11);

        $this->assertCount(30, $november);
        $this->assertSame('2026-11-01', $november->first()->date->toDateString());
        $this->assertSame(DayStatus::DayOff, $november->first()->status);
    }

    public function test_orders_sort_by_time_of_day_not_alphabetically()
    {
        foreach ([TimeSlot::Afternoon, TimeSlot::Morning, TimeSlot::Midday] as $slot) {
            Order::factory()->create(['pickup_date' => '2026-11-10', 'time_slot' => $slot]);
        }

        $this->assertSame(
            [TimeSlot::Morning, TimeSlot::Midday, TimeSlot::Afternoon],
            Order::forDate('2026-11-10')->orderBySlot()->pluck('time_slot')->all(),
        );
    }
}
