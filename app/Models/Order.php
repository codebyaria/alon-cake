<?php

namespace App\Models;

use App\Enums\Fulfillment;
use App\Enums\OrderStage;
use App\Enums\PaymentMethod;
use App\Enums\TimeSlot;
use Carbon\CarbonInterface;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property Carbon $pickup_date
 * @property TimeSlot $time_slot
 * @property OrderStage $stage
 * @property int $cake_id
 * @property int $size_id
 * @property int $flavor_id
 * @property string|null $cake_text
 * @property string|null $card_message
 * @property string|null $card_from
 * @property Fulfillment $fulfillment
 * @property string|null $delivery_address
 * @property string $customer_name
 * @property string $customer_whatsapp
 * @property string $locale
 * @property int $cake_price
 * @property int $delivery_fee
 * @property int $total
 * @property PaymentMethod $payment_method
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Cake $cake
 * @property-read Size $size
 * @property-read Flavor $flavor
 */
#[Fillable([
    'pickup_date', 'time_slot', 'stage',
    'cake_id', 'size_id', 'flavor_id', 'cake_text', 'card_message', 'card_from',
    'fulfillment', 'delivery_address',
    'customer_name', 'customer_whatsapp', 'locale',
    'cake_price', 'delivery_fee', 'total', 'payment_method', 'paid_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'stage' => 'recorded',
        'locale' => 'id',
        'delivery_fee' => 0,
    ];

    /**
     * Fill `code` on insert without model events, so seeders that mute events still get one.
     */
    public $usesUniqueIds = true;

    /**
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['code'];
    }

    public function newUniqueId(): string
    {
        return static::newCode();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pickup_date' => 'date:Y-m-d',
            'time_slot' => TimeSlot::class,
            'stage' => OrderStage::class,
            'fulfillment' => Fulfillment::class,
            'payment_method' => PaymentMethod::class,
            'cake_price' => 'integer',
            'delivery_fee' => 'integer',
            'total' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Store as a plain Y-m-d so date lookups match exactly (SQLite compares strings).
     *
     * @return Attribute<never, CarbonInterface|string>
     */
    protected function pickupDate(): Attribute
    {
        return Attribute::set(fn (CarbonInterface|string $value) => Carbon::parse($value)->toDateString());
    }

    /**
     * Short, readable reference for WhatsApp and the kitchen, e.g. "ALN-7KQ2M".
     */
    public static function newCode(): string
    {
        // No 0/O or 1/I/L: the code gets read aloud and retyped.
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        do {
            $code = 'ALN-'.collect(range(1, 5))
                ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
                ->implode('');
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * @return BelongsTo<Cake, $this>
     */
    public function cake(): BelongsTo
    {
        return $this->belongsTo(Cake::class);
    }

    /**
     * @return BelongsTo<Size, $this>
     */
    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * @return BelongsTo<Flavor, $this>
     */
    public function flavor(): BelongsTo
    {
        return $this->belongsTo(Flavor::class);
    }

    /**
     * Orders that still take a place in their day's capacity.
     *
     * @param  Builder<self>  $query
     */
    public function scopeHoldingCapacity(Builder $query): void
    {
        $query->where('stage', '!=', OrderStage::Cancelled->value);
    }

    /**
     * Chronological order within a day (the enum values sort alphabetically otherwise).
     *
     * @param  Builder<self>  $query
     */
    public function scopeOrderBySlot(Builder $query): void
    {
        $cases = collect(TimeSlot::cases())
            ->map(fn (TimeSlot $slot, int $i) => "when '{$slot->value}' then {$i}")
            ->implode(' ');

        $query->orderByRaw("case time_slot {$cases} end");
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeForDate(Builder $query, CarbonInterface|string $date): void
    {
        $query->where('pickup_date', Carbon::parse($date)->toDateString());
    }
}
