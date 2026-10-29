<?php

namespace App\Services;

use App\Enums\Fulfillment;
use App\Models\Order;

/**
 * The WhatsApp message the kitchen would send. Demo only: shown as a preview,
 * never sent (CLAUDE.md > Stack).
 */
class WhatsAppPreview
{
    public function recorded(Order $order): string
    {
        $order->loadMissing(['cake', 'size', 'flavor']);
        $firstName = strtok($order->customer_name, ' ');

        $handover = $order->fulfillment === Fulfillment::Delivery
            ? __('Kami antar pukul :slot.', ['slot' => $order->time_slot->getLabel()])
            : __('Bisa diambil pukul :slot.', ['slot' => $order->time_slot->getLabel()]);

        return implode("\n\n", array_filter([
            __('Halo :name, terima kasih sudah memesan di Alon.', ['name' => $firstName]),
            __('Pesanan :code untuk :date sudah kami catat: :cake, :size, :flavor.', [
                'code' => $order->code,
                'date' => $order->pickup_date->locale(app()->getLocale())->translatedFormat('l, j F'),
                'cake' => $order->cake->name,
                'size' => $order->size->label,
                'flavor' => $order->flavor->translate('name'),
            ]),
            $handover,
            __('Kami kabari lagi saat sponge-nya dipanggang.'),
        ]));
    }
}
