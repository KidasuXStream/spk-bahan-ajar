<?php

namespace App\Filament\Resources\PengajuanBahanAjarResource\Pages;

use App\Filament\Resources\PengajuanBahanAjarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanBahanAjars extends ListRecords
{
    protected static string $resource = PengajuanBahanAjarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
