<?php

namespace App\Filament\Resources\AhpResultResource\Pages;

use App\Filament\Resources\AhpResultResource;
use Filament\Resources\Pages\ViewRecord;

class CreateAhpResult extends ViewRecord
{
    protected static string $resource = AhpResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions needed for view-only page
        ];
    }

    public function getTitle(): string
    {
        return 'Detail Hasil AHP & Ranking';
    }
}
