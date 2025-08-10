<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Traits\HasRoleVisibility;
use Filament\Widgets\Widget;
use Filament\Widgets\Card;

class TimPengadaanExportWidget extends Widget
{
    use HasRoleVisibility;

    protected static string $view = 'filament.widgets.tim-pengadaan-export-widget';

    protected int | string | array $columnSpan = 'full';

    protected static function getRequiredRoles(): array
    {
        return ['Tim Pengadaan', 'super_admin'];
    }
}
