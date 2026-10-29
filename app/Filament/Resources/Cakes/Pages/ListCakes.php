<?php

namespace App\Filament\Resources\Cakes\Pages;

use App\Filament\Resources\Cakes\CakeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCakes extends ListRecords
{
    protected static string $resource = CakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
