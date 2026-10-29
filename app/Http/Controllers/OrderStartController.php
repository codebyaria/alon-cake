<?php

namespace App\Http\Controllers;

use App\Services\OrderCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * First step of the order flow: the chosen date, circled.
 * The remaining steps (cake → details → card → pay) come next.
 */
class OrderStartController extends Controller
{
    public function __invoke(Request $request, OrderCalendar $calendar): Response
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $date = isset($validated['tanggal'])
            ? CarbonImmutable::parse($validated['tanggal'])
            : $calendar->firstBookableDate();

        return Inertia::render('order/start', [
            'day' => $date ? $calendar->forDate($date)->toArray() : null,
            'firstBookable' => $calendar->firstBookableDate()?->toDateString(),
        ]);
    }
}
