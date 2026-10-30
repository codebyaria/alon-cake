<?php

namespace App\Http\Controllers;

use App\Actions\PlaceOrder;
use App\Enums\Fulfillment;
use App\Enums\OrderStage;
use App\Enums\PaymentMethod;
use App\Enums\TimeSlot;
use App\Models\Cake;
use App\Models\Flavor;
use App\Models\Order;
use App\Models\Size;
use App\Services\OrderCalendar;
use App\Services\WhatsAppPreview;
use App\Support\DayAvailability;
use App\Support\Localized;
use App\Support\OrderDraft;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The order flow (CLAUDE.md): date → cake → size/flavor/writing → message card
 * → pickup or delivery & pay → confirmation. No cart, no account.
 */
class OrderFlowController extends Controller
{
    private const PLACED_KEY = 'placed_orders';

    public function __construct(private OrderDraft $draft, private OrderCalendar $calendar) {}

    // ── 1 · Date ────────────────────────────────────────────────────────────

    public function date(Request $request): Response
    {
        $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'bulan' => ['nullable', 'date_format:Y-m'],
        ]);

        // A date picked on the homepage calendar arrives as ?tanggal=…
        $selected = $request->query('tanggal') ?? $this->draft->date()?->toDateString();
        $earliest = $this->calendar->earliestDate();
        $last = $this->calendar->lastDate();

        $month = CarbonImmutable::parse(
            $request->query('bulan') ?? $selected ?? $this->calendar->firstBookableDate() ?? $earliest,
        )->startOfMonth();
        $month = $month->max($earliest->startOfMonth())->min($last->startOfMonth());

        return Inertia::render('order/date', [
            'month' => $month->format('Y-m'),
            'days' => $this->calendar->month($month->year, $month->month)
                ->map(fn (DayAvailability $day) => $day->toArray()),
            'prevMonth' => $month->gt($earliest->startOfMonth()) ? $month->subMonth()->format('Y-m') : null,
            'nextMonth' => $month->lt($last->startOfMonth()) ? $month->addMonth()->format('Y-m') : null,
            'selected' => $selected,
            'earliest' => $earliest->toDateString(),
            'leadDays' => (int) config('alon.lead_days'),
        ]);
    }

    public function storeDate(Request $request): RedirectResponse
    {
        $data = $request->validate(['date' => ['required', 'date_format:Y-m-d']]);

        if (! $this->calendar->isBookable($data['date'])) {
            return back()->withErrors(['date' => __('Tanggal itu tidak bisa dipesan. Pilih tanggal lain, ya.')]);
        }

        $this->draft->put(['date' => $data['date']]);

        return redirect(Localized::route('order.cake'));
    }

    // ── 2 · Cake ────────────────────────────────────────────────────────────

    public function cake(): Response|RedirectResponse
    {
        if ($redirect = $this->guard('cake')) {
            return $redirect;
        }

        return Inertia::render('order/cake', [
            'summary' => $this->summary(),
            'cakes' => Cake::onMenu()->get()->map(fn (Cake $cake) => [
                'id' => $cake->id,
                'name' => $cake->name,
                'description' => $cake->translate('description'),
                'decorNote' => $cake->translate('decor_note'),
                'priceFrom' => $cake->base_price,
                'photoUrl' => $cake->photoUrl(),
            ]),
            'selected' => $this->draft->all()['cake_id'] ?? null,
        ]);
    }

    public function storeCake(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cake_id' => ['required', Rule::exists('cakes', 'id')->where('is_active', true)],
        ]);

        $this->draft->put($data);

        return redirect(Localized::route('order.details'));
    }

    // ── 3 · Size, flavor, writing ───────────────────────────────────────────

    public function details(): Response|RedirectResponse
    {
        if ($redirect = $this->guard('details')) {
            return $redirect;
        }

        $cake = $this->draft->cake();
        $values = $this->draft->all();

        return Inertia::render('order/details', [
            'summary' => $this->summary(),
            'sizes' => Size::onMenu()->get()->map(fn (Size $size) => [
                'id' => $size->id,
                'label' => $size->label,
                'servings' => $size->servingsLabel(),
                'price' => $cake->priceFor($size),
            ]),
            'flavors' => Flavor::onMenu()->get()->map(fn (Flavor $flavor) => [
                'id' => $flavor->id,
                'name' => $flavor->translate('name'),
            ]),
            'values' => [
                'size_id' => $values['size_id'] ?? null,
                'flavor_id' => $values['flavor_id'] ?? null,
                'cake_text' => $values['cake_text'] ?? '',
            ],
            'cakeTextMax' => (int) config('alon.cake_text_max'),
        ]);
    }

    public function storeDetails(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'size_id' => ['required', Rule::exists('sizes', 'id')->where('is_active', true)],
            'flavor_id' => ['required', Rule::exists('flavors', 'id')->where('is_active', true)],
            'cake_text' => ['nullable', 'string', 'max:'.config('alon.cake_text_max')],
        ]);

        $this->draft->put($data);

        return redirect(Localized::route('order.card'));
    }

    // ── 4 · Message card ────────────────────────────────────────────────────

    public function card(): Response|RedirectResponse
    {
        if ($redirect = $this->guard('card')) {
            return $redirect;
        }

        $values = $this->draft->all();

        return Inertia::render('order/card', [
            'summary' => $this->summary(),
            'values' => [
                'card_message' => $values['card_message'] ?? '',
                'card_from' => $values['card_from'] ?? '',
            ],
            'messageMax' => (int) config('alon.card_message_max'),
            'fromMax' => (int) config('alon.card_from_max'),
        ]);
    }

    public function storeCard(Request $request): RedirectResponse
    {
        if ($request->boolean('skip')) {
            $this->draft->put(['card_message' => null, 'card_from' => null, 'card_done' => true]);

            return redirect(Localized::route('order.checkout'));
        }

        $data = $request->validate([
            'card_message' => ['required', 'string', 'max:'.config('alon.card_message_max')],
            'card_from' => ['nullable', 'string', 'max:'.config('alon.card_from_max')],
        ]);

        $this->draft->put($data + ['card_done' => true]);

        return redirect(Localized::route('order.checkout'));
    }

    // ── 5 · Pickup or delivery & pay ────────────────────────────────────────

    public function checkout(): Response|RedirectResponse
    {
        if ($redirect = $this->guard('checkout')) {
            return $redirect;
        }

        return Inertia::render('order/checkout', [
            'summary' => $this->summary(),
            'slots' => collect(TimeSlot::cases())->map(fn (TimeSlot $slot) => [
                'value' => $slot->value,
                'label' => $slot->getLabel(),
            ]),
            'payments' => collect(PaymentMethod::cases())->map(fn (PaymentMethod $method) => [
                'value' => $method->value,
                'label' => $method->getLabel(),
            ]),
            'deliveryFee' => Fulfillment::Delivery->fee(),
        ]);
    }

    public function placeOrder(Request $request, PlaceOrder $placeOrder): RedirectResponse
    {
        if ($redirect = $this->guard('checkout')) {
            return $redirect;
        }

        $request->merge([
            'customer_whatsapp' => $this->normaliseWhatsApp((string) $request->input('customer_whatsapp')),
        ]);

        $data = $request->validate([
            'fulfillment' => ['required', Rule::enum(Fulfillment::class)],
            'time_slot' => ['required', Rule::enum(TimeSlot::class)],
            'delivery_address' => ['nullable', 'required_if:fulfillment,delivery', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:80'],
            'customer_whatsapp' => ['required', 'regex:/^08\d{7,12}$/'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ], [
            'customer_whatsapp.regex' => __('Nomor WhatsApp diawali 08 atau +62, misalnya 0812 3456 7890.'),
            'delivery_address.required_if' => __('Tulis alamat pengantaran, ya.'),
        ]);

        $order = $placeOrder($this->draft, $data);

        // Only this browser may see the confirmation: it shows a name and phone number.
        $request->session()->push(self::PLACED_KEY, $order->code);

        return redirect(Localized::route('order.done', ['code' => $order->code]));
    }

    // ── 6 · Confirmation ────────────────────────────────────────────────────

    public function done(Request $request, string $code, WhatsAppPreview $whatsApp): Response
    {
        $order = $this->placedOrder($request, $code);
        $date = CarbonImmutable::parse($order->pickup_date);
        $handover = $order->fulfillment === Fulfillment::Delivery
            ? __('Diantar, :slot', ['slot' => $order->time_slot->getLabel()])
            : __('Siap diambil, :slot', ['slot' => $order->time_slot->getLabel()]);

        // The kitchen timeline (H-2 sponge, H-1 decorate, day H ready), from today.
        $timeline = collect([
            [CarbonImmutable::parse($order->created_at)->startOfDay(), OrderStage::Recorded->getLabel()],
            [$date->subDays(2), OrderStage::Baking->getLabel()],
            [$date->subDay(), OrderStage::Decorating->getLabel()],
            [$date, $handover],
        ])->map(fn (array $row) => ['date' => $row[0]->toDateString(), 'label' => $row[1]]);

        return Inertia::render('order/done', [
            'order' => [
                'code' => $order->code,
                'date' => $date->toDateString(),
                'cake' => $order->cake->name,
                'size' => $order->size->label,
                'flavor' => $order->flavor->translate('name'),
                'cakeText' => $order->cake_text,
                'cardMessage' => $order->card_message,
                'cardFrom' => $order->card_from,
                'handover' => $handover,
                'total' => $order->total,
                'paymentMethod' => $order->payment_method->getLabel(),
            ],
            'timeline' => $timeline,
            'whatsAppPreview' => $whatsApp->recorded($order),
            'calendarUrl' => Localized::route('order.calendar', ['code' => $order->code]),
        ]);
    }

    /**
     * "Simpan ke kalender": a one-event .ics file for the pickup or delivery window.
     */
    public function calendar(Request $request, string $code): HttpResponse
    {
        $order = $this->placedOrder($request, $code);
        [$start, $end] = $order->time_slot->hours();
        $date = $order->pickup_date->format('Ymd');
        $tz = config('app.timezone');
        $title = $order->fulfillment === Fulfillment::Delivery
            ? __('Kue Alon diantar')
            : __('Ambil kue di Alon');
        $escape = fn (string $text) => addcslashes($text, ',;\\');

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Alon Cake//Demo//ID',
            'BEGIN:VEVENT',
            "UID:{$order->code}@alon.cake",
            'DTSTAMP:'.now('UTC')->format('Ymd\THis\Z'),
            "DTSTART;TZID={$tz}:{$date}T".str_replace(':', '', $start).'00',
            "DTEND;TZID={$tz}:{$date}T".str_replace(':', '', $end).'00',
            'SUMMARY:'.$escape("{$title} · {$order->code}"),
            'DESCRIPTION:'.$escape("{$order->cake->name}, {$order->size->label}"),
            'END:VEVENT',
            'END:VCALENDAR',
        ])."\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"alon-{$order->code}.ics\"",
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Send the customer back to the first unanswered step, or to the calendar
     * if their date filled up meanwhile.
     */
    private function guard(string $step): ?RedirectResponse
    {
        if ($missing = $this->draft->missingBefore($step)) {
            return redirect(Localized::route($missing === 'date' ? 'order.date' : "order.{$missing}"));
        }

        if (! $this->calendar->isBookable($this->draft->date())) {
            return redirect(Localized::route('order.date'))
                ->withErrors(['date' => __('Tanggal itu baru saja penuh. Pilih tanggal lain, ya.')]);
        }

        return null;
    }

    /**
     * What has been chosen so far, for the summary line under each step.
     *
     * @return array<string, mixed>
     */
    private function summary(): array
    {
        $cake = $this->draft->cake();
        $size = $this->draft->size();
        $values = $this->draft->all();

        return [
            'date' => $this->draft->date()?->toDateString(),
            'remaining' => $this->calendar->forDate($this->draft->date())->remaining(),
            'cake' => $cake?->name,
            'size' => $size?->label,
            'flavor' => $this->draft->flavor()?->translate('name'),
            'cakeText' => $values['cake_text'] ?? null,
            'hasCard' => filled($values['card_message'] ?? null),
            'cakePrice' => $cake && $size ? $cake->priceFor($size) : null,
        ];
    }

    private function placedOrder(Request $request, string $code): Order
    {
        abort_unless(in_array($code, $request->session()->get(self::PLACED_KEY, []), true), 404);

        return Order::with(['cake', 'size', 'flavor'])->where('code', $code)->firstOrFail();
    }

    /**
     * "+62 812-3456-7890" and "0812 3456 7890" both become "081234567890".
     */
    private function normaliseWhatsApp(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number) ?? '';

        return str_starts_with($digits, '62') ? '0'.substr($digits, 2) : $digits;
    }
}
