<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\FlavorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property array<string, string> $name
 * @property bool $is_active
 * @property int $sort
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['slug', 'name', 'is_active', 'sort'])]
class Flavor extends Model
{
    /** @use HasFactory<FlavorFactory> */
    use HasFactory, HasTranslations;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
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
}
