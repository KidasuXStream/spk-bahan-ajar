<?php

namespace App\Filament\Resources\AhpComparisonResource\Pages;

use App\Filament\Resources\AhpComparisonResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAhpComparison extends ViewRecord
{
    protected static string $resource = AhpComparisonResource::class;

    protected static ?string $title = 'View AHP Analysis';
}
