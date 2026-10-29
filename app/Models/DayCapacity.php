<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\DayCapacityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * An override of the default daily capacity: fewer cakes, or the kitchen closed.
 *
 * @property int $id
 * @property Carbon $date
 * @property int $capacity
 * @property bool $is_closed
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['date', 'capacity', 'is_closed', 'note'])]
class DayCapacity extends Model
{
    /** @use HasFactory<DayCapacityFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'capacity' => 'integer',
            'is_closed' => 'boolean',
        ];
    }

    /**
     * Store as a plain Y-m-d so date lookups match exactly (SQLite compares strings).
     *
     * @return Attribute<never, CarbonInterface|string>
     */
    protected function date(): Attribute
    {
        return Attribute::set(fn (CarbonInterface|string $value) => Carbon::parse($value)->toDateString());
    }
}
