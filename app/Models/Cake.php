<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\CakeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property array<string, string> $description
 * @property array<string, string> $decor_note
 * @property int $base_price
 * @property string|null $photo_path
 * @property bool $is_active
 * @property int $sort
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['slug', 'name', 'description', 'decor_note', 'base_price', 'photo_path', 'is_active', 'sort'])]
class Cake extends Model
{
    /** @use HasFactory<CakeFactory> */
    use HasFactory, HasTranslations;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'description' => 'array',
            'decor_note' => 'array',
            'base_price' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Active cakes in display order.
     *
     * @param  Builder<self>  $query
     */
    public function scopeOnMenu(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort');
    }

    /**
     * Public URL of the still photo uploaded in the admin, if any.
     */
    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    /**
     * Price of this cake in the given size, in rupiah.
     */
    public function priceFor(Size $size): int
    {
        return $this->base_price + $size->price_add;
    }
}
