<?php

namespace App\Filament\Resources\AhpComparisonResource\Pages;

use App\Filament\Resources\AhpComparisonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAhpComparison extends EditRecord
{
    protected static string $resource = AhpComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
