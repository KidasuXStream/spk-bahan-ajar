<?php

namespace App\Filament\Resources\AhpResultResource\Pages;

use App\Filament\Resources\AhpResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAhpResults extends ListRecords
{
    protected static string $resource = AhpResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Create action hidden - this resource is view-only
            // Actions\CreateAction::make()
            //     ->label('Lihat Hasil AHP')
            //     ->url(route('filament.admin.resources.ahp-results.create')),
        ];
    }
}
