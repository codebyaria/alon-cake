<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Fulfillment: string implements HasLabel
{
    case Pickup = 'pickup';
    case Delivery = 'delivery';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pickup => __('Ambil sendiri'),
            self::Delivery => __('Diantar'),
        };
    }

    public function fee(): int
    {
        return $this === self::Delivery ? (int) config('alon.delivery_fee') : 0;
    }
}
