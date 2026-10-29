<?php

namespace App\Support;

use App\Models\Cake;
use App\Models\Flavor;
use App\Models\Size;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Session\Session;

/**
 * The order in progress, kept in the session between steps.
 *
 * Steps: date → cake → details (size, flavor, writing) → card → checkout.
 * Nothing touches the database until the customer pays.
 */
class OrderDraft
{
    private const KEY = 'order_draft';

    public const STEPS = ['date', 'cake', 'details', 'card', 'checkout'];

    public function __construct(private Session $session) {}

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->session->get(self::KEY, []);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function put(array $values): void
    {
        $this->session->put(self::KEY, array_merge($this->all(), $values));
    }

    public function forget(): void
    {
        $this->session->forget(self::KEY);
    }

    public function date(): ?CarbonImmutable
    {
        $date = $this->all()['date'] ?? null;

        return $date ? CarbonImmutable::parse($date) : null;
    }

    public function cake(): ?Cake
    {
        return $this->find(Cake::class, 'cake_id');
    }

    public function size(): ?Size
    {
        return $this->find(Size::class, 'size_id');
    }

    public function flavor(): ?Flavor
    {
        return $this->find(Flavor::class, 'flavor_id');
    }

    /**
     * The first step before $step that still needs an answer, or null if all are done.
     */
    public function missingBefore(string $step): ?string
    {
        $done = [
            'date' => $this->date() !== null,
            'cake' => $this->cake() !== null,
            'details' => $this->size() !== null && $this->flavor() !== null,
            'card' => (bool) ($this->all()['card_done'] ?? false),
        ];

        foreach (array_slice(self::STEPS, 0, array_search($step, self::STEPS, true)) as $previous) {
            if (! $done[$previous]) {
                return $previous;
            }
        }

        return null;
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TModel>  $model
     * @return TModel|null
     */
    private function find(string $model, string $key)
    {
        $id = $this->all()[$key] ?? null;

        // Only items still on the menu: a cake taken off the menu mid-order is asked again.
        return $id ? $model::query()->where('is_active', true)->find($id) : null;
    }
}
