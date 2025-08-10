<?php

namespace App\Filament\Resources\AhpComparisonResource\Pages;

use App\Filament\Resources\AhpComparisonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAhpComparisons extends ListRecords
{
    protected static string $resource = AhpComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
