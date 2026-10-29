<?php

/*
|--------------------------------------------------------------------------
| Alon Cake product rules
|--------------------------------------------------------------------------
|
| The business rules from CLAUDE.md, in one place. Each value is a promise
| to the customer (patient, personal, honest), so change them deliberately.
|
*/

return [

    // Pre-order at least H-3: every cake needs three days (sponge, rest, decorate).
    'lead_days' => 3,

    // How far ahead the calendar opens.
    'booking_horizon_days' => 60,

    // Cakes per day when an admin has not set a capacity for that date.
    'default_capacity' => 5,

    // Days the kitchen is closed, as Carbon day-of-week numbers (0 = Sunday).
    'closed_weekdays' => [0],

    // Flat delivery fee in rupiah (demo: no courier integration).
    'delivery_fee' => 25000,

    // Character limits shown in the order form.
    'cake_text_max' => 24,
    'card_message_max' => 160,
    'card_from_max' => 40,

];
