<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Pickup or delivery windows on the chosen day.
 */
enum TimeSlot: string implements HasLabel
{
    case Morning = 'morning';
    case Midday = 'midday';
    case Afternoon = 'afternoon';

    public function getLabel(): string
    {
        return match ($this) {
            self::Morning => '10.00–12.00',
            self::Midday => '13.00–15.00',
            self::Afternoon => '16.00–18.00',
        };
    }
}
