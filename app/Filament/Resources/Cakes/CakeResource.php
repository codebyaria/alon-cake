<?php

namespace App\Filament\Resources\Cakes;

use App\Filament\Resources\Cakes\Pages\CreateCake;
use App\Filament\Resources\Cakes\Pages\EditCake;
use App\Filament\Resources\Cakes\Pages\ListCakes;
use App\Filament\Resources\Cakes\Schemas\CakeForm;
use App\Filament\Resources\Cakes\Tables\CakesTable;
use App\Models\Cake;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CakeResource extends Resource
{
    protected static ?string $model = Cake::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCake;

    protected static string|UnitEnum|null $navigationGroup = 'Menu';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'kue';

    protected static ?string $pluralModelLabel = 'kue';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CakeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CakesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCakes::route('/'),
            'create' => CreateCake::route('/create'),
            'edit' => EditCake::route('/{record}/edit'),
        ];
    }
}
