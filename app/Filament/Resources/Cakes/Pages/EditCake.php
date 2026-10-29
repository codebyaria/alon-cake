<?php

namespace App\Filament\Resources\Cakes\Pages;

use App\Filament\Resources\Cakes\CakeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCake extends EditRecord
{
    protected static string $resource = CakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
