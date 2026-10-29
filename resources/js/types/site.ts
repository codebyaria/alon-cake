export type DayStatus =
    | 'available'
    | 'full'
    | 'closed'
    | 'day_off'
    | 'too_soon'
    | 'beyond_horizon';

/** Mirrors App\Support\DayAvailability::toArray(). */
export type CalendarDay = {
    date: string;
    status: DayStatus;
    label: string;
    capacity: number;
    booked: number;
    remaining: number;
    bookable: boolean;
};

export type MenuCake = {
    slug: string;
    name: string;
    description: string;
    flavorNote: string;
    priceFrom: number;
    photoUrl: string | null;
};
