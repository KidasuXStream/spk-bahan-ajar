<?php

namespace App\Filament\Widgets;

use App\Models\PengajuanBahanAjar;
use App\Filament\Widgets\Traits\HasRoleVisibility;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LatestPengajuanWidget extends BaseWidget
{
    use HasRoleVisibility;
    
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $query = PengajuanBahanAjar::query();

        // Filter by user role
        if ($user->roles->contains('name', 'Kaprodi')) {
            $query->where('prodi', $user->prodi);
        }

        return $table
            ->query($query->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('nama_bahan_ajar')
                    ->label('Nama Bahan Ajar')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('prodi')
                    ->label('Program Studi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ganjil' => 'info',
                        'genap' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('tahun_akademik')
                    ->label('Tahun Akademik'),

                Tables\Columns\TextColumn::make('urgensi_tim_pengadaan')
                    ->label('Urgensi Tim Pengadaan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'sangat_urgent' => 'danger',
                        'urgent' => 'warning',
                        'cukup_urgent' => 'info',
                        'kurang_urgent' => 'success',
                        'tidak_urgent' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getTableHeading(): string
    {
        $user = Auth::user();
        if ($user->roles->contains('name', 'Kaprodi')) {
            return 'Pengajuan Terbaru - ' . $user->prodi;
        }
        return 'Pengajuan Terbaru';
    }
    
    protected static function getRequiredRoles(): array
    {
        return ['Super Admin', 'Tim Pengadaan', 'Kaprodi'];
    }
}
