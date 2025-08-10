<?php

namespace App\Filament\Resources\PengajuanBahanAjarResource\Pages;

use App\Filament\Resources\PengajuanBahanAjarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanBahanAjar extends EditRecord
{
    protected static string $resource = PengajuanBahanAjarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
