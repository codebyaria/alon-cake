<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\Fulfillment;
use App\Enums\OrderStage;
use App\Models\Order;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Mirrors the "Admin · pesanan per tanggal" wireframe:
 * slot, customer, cake, handwriting & card, stage.
 */
class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['cake', 'size', 'flavor']))
            ->defaultGroup(
                Group::make('pickup_date')
                    ->label('Tanggal')
                    ->getTitleFromRecordUsing(fn (Order $record) => $record->pickup_date->translatedFormat('l, j F Y'))
                    ->collapsible(),
            )
            ->defaultSort(fn (Builder $query) => $query->orderBy('pickup_date')->orderBySlot())
            ->columns([
                TextColumn::make('time_slot')
                    ->label('Jam'),
                TextColumn::make('customer_name')
                    ->label('Pemesan')
                    ->weight('medium')
                    ->description(fn (Order $record) => $record->fulfillment === Fulfillment::Delivery
                        ? 'Diantar'
                        : 'Ambil sendiri')
                    ->searchable(),
                TextColumn::make('cake.name')
                    ->label('Kue')
                    ->description(fn (Order $record) => "{$record->size->label} · {$record->flavor->translate('name')}"),
                TextColumn::make('cake_text')
                    ->label('Tulisan & kartu')
                    ->placeholder('Tanpa tulisan')
                    ->description(fn (Order $record) => $record->card_message ? 'Ada kartu pesan' : 'Tanpa kartu')
                    ->wrap(),
                SelectColumn::make('stage')
                    ->label('Tahap')
                    ->options(OrderStage::class)
                    ->selectablePlaceholder(false),
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('stage')
                    ->label('Tahap')
                    ->options(OrderStage::class),
                SelectFilter::make('fulfillment')
                    ->label('Ambil/antar')
                    ->options(Fulfillment::class),
            ])
            ->recordActions([
                EditAction::make()->label('Detail'),
            ])
            ->paginated([25, 50, 100]);
    }
}
