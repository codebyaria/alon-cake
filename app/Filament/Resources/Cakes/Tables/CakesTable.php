<?php

namespace App\Filament\Resources\Cakes\Tables;

use App\Models\Cake;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CakesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('')
                    ->disk('public')
                    ->square(),
                TextColumn::make('name')
                    ->label('Kue')
                    ->weight('medium')
                    ->description(fn (Cake $record) => $record->translate('decor_note'))
                    ->searchable(),
                TextColumn::make('base_price')
                    ->label('Mulai dari')
                    ->money('IDR', locale: 'id', decimalPlaces: 0),
                TextColumn::make('orders_count')
                    ->label('Pesanan')
                    ->counts('orders'),
                ToggleColumn::make('is_active')
                    ->label('Ditampilkan'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
