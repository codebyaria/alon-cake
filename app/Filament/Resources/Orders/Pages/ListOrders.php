<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

/**
 * The kitchen looks at days, not transactions: tabs are moments in time.
 */
class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        $today = today();

        return [
            'today' => Tab::make('Hari ini')
                ->badge(fn () => Order::holdingCapacity()->forDate($today)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->forDate($today)),
            'tomorrow' => Tab::make('Besok')
                ->badge(fn () => Order::holdingCapacity()->forDate($today->copy()->addDay())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->forDate($today->copy()->addDay())),
            'week' => Tab::make('7 hari ke depan')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('pickup_date', [
                    $today->toDateString(), $today->copy()->addDays(7)->toDateString(),
                ])),
            'upcoming' => Tab::make('Semua yang akan datang')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('pickup_date', '>=', $today->toDateString())),
            'past' => Tab::make('Lewat')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('pickup_date', '<', $today->toDateString())),
        ];
    }

    public function getDefaultActiveTab(): string
    {
        return 'week';
    }
}
