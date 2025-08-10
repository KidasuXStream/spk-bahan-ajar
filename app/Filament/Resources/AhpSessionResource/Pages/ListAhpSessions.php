<?php

namespace App\Filament\Resources\AhpSessionResource\Pages;

use App\Filament\Resources\AhpSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAhpSessions extends ListRecords
{
    protected static string $resource = AhpSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
