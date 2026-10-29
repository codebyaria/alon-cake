<?php

namespace App\Filament\Resources\DayCapacities\Pages;

use App\Filament\Resources\DayCapacities\DayCapacityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDayCapacities extends ManageRecords
{
    protected static string $resource = DayCapacityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
