<?php

namespace App\Enums;

/**
 * Why a calendar day can or cannot take a new order.
 */
enum DayStatus: string
{
    case Available = 'available';
    case Full = 'full';
    case Closed = 'closed';
    case DayOff = 'day_off';
    case TooSoon = 'too_soon';
    case BeyondHorizon = 'beyond_horizon';

    public function isBookable(): bool
    {
        return $this === self::Available;
    }

    /**
     * Short, warm label for the calendar (CLAUDE.md: honest, never urgent).
     */
    public function label(): string
    {
        return match ($this) {
            self::Available => __('Tersedia'),
            self::Full => __('Penuh'),
            self::Closed => __('Dapur tutup'),
            self::DayOff => __('Libur'),
            self::TooSoon => __('Terlalu dekat'),
            self::BeyondHorizon => __('Belum dibuka'),
        };
    }
}
