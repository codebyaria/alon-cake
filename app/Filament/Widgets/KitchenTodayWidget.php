<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Services\KitchenToday;
use App\Services\OrderCalendar;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * "Disiapkan hari ini" from the admin wireframe: the day's work, not revenue.
 */
class KitchenTodayWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getHeading(): string
    {
        return today()->translatedFormat('l, j F');
    }

    protected function getDescription(): string
    {
        return app(KitchenToday::class)->headline();
    }

    protected function getStats(): array
    {
        $kitchen = app(KitchenToday::class);
        $calendar = app(OrderCalendar::class);
        $sponges = $kitchen->sponges();
        $next = $calendar->firstBookableDate();

        return [
            Stat::make('Sponge dipanggang', $sponges->sum())
                ->description($sponges->isEmpty()
                    ? 'Tidak ada untuk lusa'
                    : $sponges->map(fn ($count, $label) => "{$label} ×{$count}")->implode(', ')),
            Stat::make('Dihias untuk besok', $kitchen->toDecorate()->count())
                ->description("{$kitchen->cardsToWrite()} kartu pesan ditulis"),
            Stat::make('Diserahkan hari ini', $kitchen->toHandOver()->count())
                ->url(OrderResource::getUrl('index', ['activeTab' => 'today'])),
            Stat::make('Tanggal terdekat yang masih kosong', $next?->translatedFormat('l, j M') ?? '—')
                ->description($next ? $calendar->forDate($next)->remaining().' tempat tersisa' : 'Semua penuh sampai batas kalender'),
        ];
    }
}
