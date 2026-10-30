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
    decorNote: string;
    priceFrom: number;
    photoUrl: string | null;
};

/** What has been chosen so far in the order flow (OrderFlowController::summary). */
export type OrderSummary = {
    date: string;
    remaining: number;
    cake: string | null;
    size: string | null;
    flavor: string | null;
    cakeText: string | null;
    hasCard: boolean;
    cakePrice: number | null;
};
