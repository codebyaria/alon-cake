<?php

namespace App\Support;

use App\Enums\DayStatus;
use Carbon\CarbonImmutable;

/**
 * One calendar day as the customer sees it: honest capacity, never urgency.
 */
final readonly class DayAvailability
{
    public function __construct(
        public CarbonImmutable $date,
        public DayStatus $status,
        public int $capacity,
        public int $booked,
    ) {}

    public function remaining(): int
    {
        return max(0, $this->capacity - $this->booked);
    }

    public function isBookable(): bool
    {
        return $this->status->isBookable();
    }

    /**
     * @return array{date: string, status: string, label: string, capacity: int, booked: int, remaining: int, bookable: bool}
     */
    public function toArray(): array
    {
        return [
            'date' => $this->date->toDateString(),
            'status' => $this->status->value,
            'label' => $this->status->label(),
            'capacity' => $this->capacity,
            'booked' => $this->booked,
            'remaining' => $this->remaining(),
            'bookable' => $this->isBookable(),
        ];
    }
}
