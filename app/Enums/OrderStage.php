<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Where a cake is in the kitchen. Mirrors the timeline on the confirmation
 * page: recorded (H-3), sponge baked (H-2), decorated (H-1), ready (day H).
 */
enum OrderStage: string implements HasColor, HasLabel
{
    case Recorded = 'recorded';
    case Baking = 'baking';
    case Decorating = 'decorating';
    case Ready = 'ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Recorded => __('Dicatat'),
            self::Baking => __('Sponge dipanggang'),
            self::Decorating => __('Dihias dan ditulis tangan'),
            self::Ready => __('Siap'),
            self::Completed => __('Selesai'),
            self::Cancelled => __('Dibatalkan'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Cancelled => 'danger',
            self::Completed => 'gray',
            self::Ready => 'success',
            default => 'primary',
        };
    }

    /**
     * Whether the order still takes up a place in the day's capacity.
     */
    public function holdsCapacity(): bool
    {
        return $this !== self::Cancelled;
    }
}
