<?php

namespace App\Filament\Resources\DayCapacities;

use App\Filament\Resources\DayCapacities\Pages\ManageDayCapacities;
use App\Models\DayCapacity;
use App\Services\OrderCalendar;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Exceptions to the default daily capacity: a lighter day, or the kitchen closed.
 */
class DayCapacityResource extends Resource
{
    protected static ?string $model = DayCapacity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Dapur';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'kapasitas';

    protected static ?string $pluralModelLabel = 'kapasitas harian';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Tanggal')
                    ->native(false)
                    ->displayFormat('l, j F Y')
                    ->minDate(today())
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('capacity')
                    ->label('Maksimal kue')
                    ->helperText('Bawaan: '.config('alon.default_capacity').' kue per hari.')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(50)
                    ->default(config('alon.default_capacity'))
                    ->required(),
                Toggle::make('is_closed')
                    ->label('Tutup tanggal ini')
                    ->helperText('Pesanan yang sudah ada tetap tercatat.'),
                TextInput::make('note')
                    ->label('Catatan')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('date', '>=', today()->toDateString()))
            ->defaultSort('date')
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('l, j F Y')
                    ->sortable(),
                TextColumn::make('capacity')
                    ->label('Maksimal kue')
                    ->numeric(),
                TextColumn::make('booked')
                    ->label('Terisi')
                    ->state(fn (DayCapacity $record) => app(OrderCalendar::class)->forDate($record->date)->booked),
                IconColumn::make('is_closed')
                    ->label('Tutup')
                    ->boolean(),
                TextColumn::make('note')
                    ->label('Catatan')
                    ->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Semua hari memakai kapasitas bawaan')
            ->emptyStateDescription('Tambahkan tanggal untuk mengurangi kapasitas atau menutup dapur.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDayCapacities::route('/'),
        ];
    }
}
