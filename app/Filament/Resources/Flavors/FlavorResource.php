<?php

namespace App\Filament\Resources\Flavors;

use App\Filament\Resources\Flavors\Pages\ManageFlavors;
use App\Models\Flavor;
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
use Illuminate\Support\Str;
use UnitEnum;

class FlavorResource extends Resource
{
    protected static ?string $model = Flavor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Menu';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'rasa';

    protected static ?string $pluralModelLabel = 'rasa';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name.id')
                    ->label('Nama (Indonesia)')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set, ?Flavor $record) => $record ? null : $set('slug', Str::slug($state)))
                    ->required(),
                TextInput::make('name.en')
                    ->label('Nama (English)')
                    ->required(),
                TextInput::make('slug')
                    ->unique(ignoreRecord: true)
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
                TextColumn::make('name.id')
                    ->label('Rasa'),
                TextColumn::make('name.en')
                    ->label('English'),
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
            'index' => ManageFlavors::route('/'),
        ];
    }
}
