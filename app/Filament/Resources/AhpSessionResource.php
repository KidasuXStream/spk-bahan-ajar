<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AhpSessionResource\Pages;
use App\Filament\Resources\AhpSessionResource\RelationManagers;
use App\Models\AhpSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Services\AhpService;
use Filament\Notifications\Notification;

class AhpSessionResource extends Resource
{
    protected static ?string $model = AhpSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'AHP';
    protected static ?string $navigationLabel = 'Tahun Ajaran ~ Semester';
    protected static ?string $pluralModelLabel = 'Tahun Ajaran ~ Semester';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->required()
                    ->placeholder('Contoh: 2024/2025'),

                Select::make('semester')
                    ->label('Semester')
                    ->options([
                        'Ganjil' => 'Ganjil',
                        'Genap' => 'Genap',
                    ])
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Session aktif untuk semester berjalan')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Status')
                    ->onColor('success')
                    ->offColor('danger')
                    ->onIcon('heroicon-o-check-circle')
                    ->offIcon('heroicon-o-x-circle'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Tidak ada data ditemukan')
            ->emptyStateDescription('Belum ada data tahun ajaran ~ semester yang dimasukkan.')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Session')
                    ->placeholder('Semua Session')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('generateAhp')
                    ->label('Generate Hasil AHP')
                    ->color('success')
                    ->icon('heroicon-o-calculator')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $service = new AhpService();
                        $service->generate($record->id); // ID dari AhpSession
                        Notification::make()
                            ->title('Berhasil menghitung AHP')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                Tables\Actions\BulkAction::make('activate')
                    ->label('Aktifkan Session')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function ($records) {
                        $records->each->activate();
                        Notification::make()
                            ->title('Session berhasil diaktifkan')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\BulkAction::make('deactivate')
                    ->label('Nonaktifkan Session')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->action(function ($records) {
                        $records->each->deactivate();
                        Notification::make()
                            ->title('Session berhasil dinonaktifkan')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAhpSessions::route('/'),
            'create' => Pages\CreateAhpSession::route('/create'),
            'edit' => Pages\EditAhpSession::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $activeCount = AhpSession::active()->count();
        return $activeCount > 0 ? (string) $activeCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $activeCount = AhpSession::active()->count();
        if ($activeCount === 0) {
            return 'danger'; // Red if no active sessions
        } elseif ($activeCount === 1) {
            return 'success'; // Green if exactly 1 active session
        } else {
            return 'warning'; // Yellow if multiple active sessions
        }
    }
}
