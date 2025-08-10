<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KriteriaResource\Pages;
use App\Models\Kriteria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KriteriaResource extends Resource
{
    protected static ?string $model = Kriteria::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Kriteria';
    protected static ?string $modelLabel = 'Kriteria';
    protected static ?string $pluralModelLabel = 'Kriteria';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('kode_kriteria')
                ->required()
                ->maxLength(10)
                ->unique(ignoreRecord: true)
                ->label('Kode Kriteria')
                ->placeholder('Contoh: K01'),

            Forms\Components\TextInput::make('nama_kriteria')
                ->required()
                ->maxLength(255)
                ->label('Nama Kriteria')
                ->placeholder('Contoh: Harga'),

            Forms\Components\Textarea::make('deskripsi')
                ->rows(3)
                ->label('Deskripsi')
                ->placeholder('Deskripsi kriteria...'),


        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_kriteria')
                    ->label('Kode Kriteria')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('nama_kriteria')
                    ->label('Nama Kriteria')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->deskripsi)
                    ->toggleable(isToggledHiddenByDefault: true),



                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('kode_kriteria')
            ->emptyStateHeading('Tidak ada kriteria')
            ->emptyStateDescription('Belum ada kriteria yang ditambahkan. Tambahkan kriteria untuk memulai analisis AHP.')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->filters([
                // Tidak ada filter karena semua kriteria selalu aktif
            ])
            ->actions([
                Tables\Actions\EditAction::make(),


            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),


                ]),
            ])
            ->groups([
                // Tidak ada grouping karena semua kriteria selalu aktif
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKriterias::route('/'),
            'create' => Pages\CreateKriteria::route('/create'),
            'edit' => Pages\EditKriteria::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Kriteria::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = Kriteria::count();
        if ($count < 2) {
            return 'danger'; // Red if less than 2 criteria
        }
        return 'success'; // Green if 2 or more criteria
    }
}
