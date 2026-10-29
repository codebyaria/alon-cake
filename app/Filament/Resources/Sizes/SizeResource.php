<?php

namespace App\Filament\Resources\Sizes;

use App\Filament\Resources\Sizes\Pages\ManageSizes;
use App\Models\Size;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class SizeResource extends Resource
{
    protected static ?string $model = Size::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsPointingOut;

    protected static string|UnitEnum|null $navigationGroup = 'Menu';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'ukuran';

    protected static ?string $pluralModelLabel = 'ukuran';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('diameter_cm')
                    ->label('Diameter')
                    ->suffix('cm')
                    ->numeric()
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('price_add')
                    ->label('Tambahan harga')
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                TextInput::make('servings_min')
                    ->label('Porsi minimal')
                    ->numeric()
                    ->required(),
                TextInput::make('servings_max')
                    ->label('Porsi maksimal')
                    ->numeric()
                    ->gte('servings_min')
                    ->required(),
                TextInput::make('sort')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Ditampilkan')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('label')
                    ->label('Ukuran'),
                TextColumn::make('servings')
                    ->label('Porsi')
                    ->state(fn (Size $record) => $record->servingsLabel()),
                TextColumn::make('price_add')
                    ->label('Tambahan harga')
                    ->money('IDR', locale: 'id', decimalPlaces: 0),
                ToggleColumn::make('is_active')
                    ->label('Ditampilkan'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSizes::route('/'),
        ];
    }
}
