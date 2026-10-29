<?php

namespace App\Models;

use Database\Factories\SizeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $diameter_cm
 * @property int $servings_min
 * @property int $servings_max
 * @property int $price_add
 * @property bool $is_active
 * @property int $sort
 * @property-read string $label
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['diameter_cm', 'servings_min', 'servings_max', 'price_add', 'is_active', 'sort'])]
class Size extends Model
{
    /** @use HasFactory<SizeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'diameter_cm' => 'integer',
            'servings_min' => 'integer',
            'servings_max' => 'integer',
            'price_add' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOnMenu(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort');
    }

    /**
     * "18 cm", used wherever a size is shown.
     */
    public function getLabelAttribute(): string
    {
        return "{$this->diameter_cm} cm";
    }

    /**
     * "10–12 orang" / "10–12 people".
     */
    public function servingsLabel(): string
    {
        return __(':min–:max orang', ['min' => $this->servings_min, 'max' => $this->servings_max]);
    }
}
