<?php

namespace App\Actions;

use App\Enums\Fulfillment;
use App\Enums\OrderStage;
use App\Enums\PaymentMethod;
use App\Enums\TimeSlot;
use App\Models\Order;
use App\Services\OrderCalendar;
use App\Support\OrderDraft;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Turns a finished draft into an order. Payment is simulated (CLAUDE.md: demo,
 * no integrations), so a submitted checkout counts as paid.
 */
class PlaceOrder
{
    public function __construct(private OrderCalendar $calendar) {}

    /**
     * @param  array{fulfillment: string, time_slot: string, delivery_address?: string|null, customer_name: string, customer_whatsapp: string, payment_method: string}  $checkout
     *
     * @throws ValidationException when the date filled up while the customer was ordering
     */
    public function __invoke(OrderDraft $draft, array $checkout): Order
    {
        return DB::transaction(function () use ($draft, $checkout) {
            $date = $draft->date();

            // Checked again here, inside the transaction: someone else may have taken the last place.
            if (! $date || ! $this->calendar->isBookable($date)) {
                throw ValidationException::withMessages([
                    'date' => __('Tanggal itu baru saja penuh. Pilih tanggal lain, ya.'),
                ]);
            }

            $cake = $draft->cake();
            $size = $draft->size();
            $flavor = $draft->flavor();
            $values = $draft->all();
            $fulfillment = Fulfillment::from($checkout['fulfillment']);
            $cakePrice = $cake->priceFor($size);
            $fee = $fulfillment->fee();

            $order = Order::create([
                'pickup_date' => $date,
                'time_slot' => TimeSlot::from($checkout['time_slot']),
                'stage' => OrderStage::Recorded,
                'cake_id' => $cake->id,
                'size_id' => $size->id,
                'flavor_id' => $flavor->id,
                'cake_text' => $values['cake_text'] ?? null,
                'card_message' => $values['card_message'] ?? null,
                'card_from' => $values['card_from'] ?? null,
                'fulfillment' => $fulfillment,
                'delivery_address' => $fulfillment === Fulfillment::Delivery ? $checkout['delivery_address'] : null,
                'customer_name' => $checkout['customer_name'],
                'customer_whatsapp' => $checkout['customer_whatsapp'],
                'locale' => App::getLocale(),
                'cake_price' => $cakePrice,
                'delivery_fee' => $fee,
                'total' => $cakePrice + $fee,
                'payment_method' => PaymentMethod::from($checkout['payment_method']),
                'paid_at' => now(),
            ]);

            $draft->forget();

            return $order;
        });
    }
}
