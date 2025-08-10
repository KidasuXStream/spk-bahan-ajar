<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanBahanAjarResource\Pages;
use App\Filament\Resources\PengajuanBahanAjarResource\RelationManagers;
use App\Models\PengajuanBahanAjar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model; // ADD THIS IMPORT
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class PengajuanBahanAjarResource extends Resource
{
    protected static ?string $model = PengajuanBahanAjar::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Pengajuan Bahan Ajar';
    protected static ?string $pluralModelLabel = 'Pengajuan Bahan Ajar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // HIDDEN FIELDS FOR AUTO-POPULATION
                Hidden::make('user_id')
                    ->default(Auth::id()),

                Hidden::make('status_pengajuan')
                    ->default('diajukan'),

                Grid::make(2)
                    ->schema([
                        // BASIC ITEM INFO - READ-ONLY FOR TIM PENGADAAN
                        TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->required()
                            ->disabled(fn() => Auth::user()->hasRole('Tim Pengadaan')),

                        TextInput::make('spesifikasi')
                            ->label('Spesifikasi')
                            ->required()
                            ->disabled(fn() => Auth::user()->hasRole('Tim Pengadaan')),

                        TextInput::make('vendor')
                            ->label('Vendor')
                            ->required()
                            ->disabled(fn() => Auth::user()->hasRole('Tim Pengadaan')),

                        // FINANCIAL INFO - READ-ONLY FOR TIM PENGADAAN
                        TextInput::make('harga_satuan')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->disabled(fn() => Auth::user()->hasRole('Tim Pengadaan')),

                        TextInput::make('jumlah')
                            ->label('Jumlah yang Dibutuhkan')
                            ->numeric()
                            ->required()
                            ->disabled(fn() => Auth::user()->hasRole('Tim Pengadaan')),

                        // AHP CRITERIA FIELDS - READ-ONLY FOR TIM PENGADAAN
                        TextInput::make('stok')
                            ->label('Stok yang Ada')
                            ->helperText('Jumlah barang yang masih tersedia')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->disabled(fn() => Auth::user()->hasRole('Tim Pengadaan')),

                        TextInput::make('masa_pakai')
                            ->label('Masa Pakai')
                            ->helperText('Contoh: 12 bulan, 2 tahun')
                            ->required()
                            ->disabled(fn() => Auth::user()->hasRole('Tim Pengadaan')),

                        // URGENSI - ROLE BASED WITH PROPER RESTRICTIONS
                        Select::make('urgensi_prodi')
                            ->label('Urgensi (Penilaian Prodi)')
                            ->helperText('Seberapa mendesak menurut prodi')
                            ->options([
                                'tinggi' => 'Tinggi - Sangat dibutuhkan',
                                'sedang' => 'Sedang - Cukup dibutuhkan',
                                'rendah' => 'Rendah - Bisa ditunda',
                            ])
                            ->required(fn() => Auth::user()->hasRole('Kaprodi'))
                            ->disabled(fn() => Auth::user()->hasRole('Tim Pengadaan'))
                            ->visible(fn() => Auth::user()->hasRole(['Kaprodi', 'Tim Pengadaan', 'super_admin'])),

                        Select::make('urgensi_institusi')
                            ->label('Urgensi (Penilaian Tim Pengadaan)')
                            ->helperText('Penilaian urgensi dari perspektif institusi - WAJIB diisi')
                            ->options([
                                'tinggi' => 'Tinggi - Prioritas utama',
                                'sedang' => 'Sedang - Prioritas normal',
                                'rendah' => 'Rendah - Bisa ditunda',
                            ])
                            ->required(fn() => Auth::user()->hasRole('Tim Pengadaan'))
                            ->disabled(fn() => Auth::user()->hasRole('Kaprodi'))
                            ->visible(fn() => Auth::user()->hasRole(['Tim Pengadaan', 'super_admin'])),



                        // SESSION SELECTION - READ-ONLY FOR TIM PENGADAAN
                        Forms\Components\Select::make('ahp_session_id')
                            ->label('Tahun Ajaran - Semester')
                            ->relationship(
                                name: 'ahpSession',
                                titleAttribute: 'tahun_ajaran',
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->tahun_ajaran} - {$record->semester}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn() => Auth::user()->hasRole('Tim Pengadaan')),

                        // CATATAN FIELDS - ROLE SEPARATED
                        Textarea::make('alasan_penolakan')
                            ->label('Catatan Pengaju')
                            ->helperText(fn() => Auth::user()->hasRole('Tim Pengadaan')
                                ? 'Catatan dari pengaju (tidak dapat diubah)'
                                : 'Catatan tambahan atau alasan khusus')
                            ->rows(3)
                            ->disabled(fn() => Auth::user()->hasRole('Tim Pengadaan'))
                            ->columnSpanFull(),

                        Textarea::make('catatan_pengadaan')
                            ->label('Catatan Tim Pengadaan')
                            ->helperText(fn() => Auth::user()->hasRole('Tim Pengadaan')
                                ? 'Catatan internal tim pengadaan untuk pengajuan ini'
                                : 'Catatan dari tim pengadaan (tidak dapat diubah)')
                            ->rows(3)
                            ->disabled(fn() => !Auth::user()->hasRole(['Tim Pengadaan', 'super_admin']))
                            ->visible(fn() => Auth::user()->hasRole(['Tim Pengadaan', 'super_admin']) ||
                                request()->routeIs('filament.admin.resources.pengajuan-bahan-ajars.edit'))
                            ->columnSpanFull(),


                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // USER & PRODI INFO
                TextColumn::make('user.name')
                    ->label('Pengaju')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.prodi')
                    ->label('Prodi')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'trpl' => 'TRPL',
                        'mesin' => 'Mesin',
                        'elektro' => 'Elektro',
                        'mekatronika' => 'Mekatronika',
                        default => strtoupper($state),
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'trpl' => 'primary',
                        'mesin' => 'success',
                        'elektro' => 'warning',
                        'mekatronika' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                // SESSION INFO
                TextColumn::make('ahpSession.tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->sortable(),

                TextColumn::make('ahpSession.semester')
                    ->label('Semester')
                    ->badge()
                    ->color(fn($state) => $state === 'Ganjil' ? 'success' : 'info')
                    ->sortable(),

                // ITEM DETAILS
                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('harga_satuan')
                    ->label('Harga')
                    ->money('IDR', true)
                    ->sortable(),

                TextColumn::make('jumlah')
                    ->label('Qty')
                    ->sortable(),

                TextColumn::make('stok')
                    ->label('Stok')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state == 0 => 'danger',
                        $state < 5 => 'warning',
                        default => 'success',
                    })
                    ->sortable(),

                // URGENSI COLUMNS - CONDITIONAL DISPLAY
                TextColumn::make('urgensi_prodi')
                    ->label('Urgensi Prodi')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'tinggi' => 'danger',
                        'sedang' => 'warning',
                        'rendah' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->visible(fn() => Auth::user()->hasRole(['Tim Pengadaan', 'super_admin'])),

                TextColumn::make('urgensi_institusi')
                    ->label('Urgensi Institusi')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'tinggi' => 'danger',
                        'sedang' => 'warning',
                        'rendah' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => $state ? ucfirst($state) : '-')
                    ->visible(fn() => Auth::user()->hasRole(['Tim Pengadaan', 'super_admin'])),

                TextColumn::make('urgensi_tim_pengadaan')
                    ->label('Urgensi Final')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'tinggi' => 'danger',
                        'sedang' => 'warning',
                        'rendah' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => $state ? ucfirst($state) : '-')
                    ->visible(fn() => Auth::user()->hasRole(['Tim Pengadaan', 'super_admin'])),

                // SIMPLIFIED STATUS - NO COMPLEX APPROVAL
                Tables\Columns\BadgeColumn::make('status_pengajuan')
                    ->label('Status')
                    ->colors([
                        'gray' => 'diajukan',
                        'success' => 'diproses',
                    ])
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'diajukan' => 'Diajukan',
                        'diproses' => 'Dalam Proses',
                        default => ucfirst($state),
                    }),

                // TIMESTAMPS
                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Tidak ada data ditemukan')
            ->emptyStateDescription('Belum ada pengajuan bahan ajar yang dimasukkan.')

            // DISABLE ROW CLICK FOR KAPRODI
            ->recordTitleAttribute(null)
            ->recordUrl(
                fn($record) =>
                Auth::user()->hasRole(['super_admin', 'Tim Pengadaan'])
                    ? static::getUrl('edit', ['record' => $record])
                    : null
            )

            // SIMPLIFIED FILTERS
            ->filters([
                Tables\Filters\SelectFilter::make('ahp_session_id')
                    ->label('Tahun Ajaran')
                    ->relationship('ahpSession', 'tahun_ajaran')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->tahun_ajaran} - {$record->semester}"),

                Tables\Filters\SelectFilter::make('urgensi_prodi')
                    ->label('Urgensi Prodi')
                    ->options([
                        'tinggi' => 'Tinggi',
                        'sedang' => 'Sedang',
                        'rendah' => 'Rendah',
                    ])
                    ->visible(fn() => Auth::user()->hasRole(['Tim Pengadaan', 'super_admin'])),
            ])

            // CLEAN ACTIONS - ROLE-BASED RESTRICTIONS
            ->actions([
                // EDIT: Only Tim Pengadaan & Super Admin can edit existing data
                Tables\Actions\EditAction::make()
                    ->visible(
                        fn($record) =>
                        Auth::user()->hasRole(['super_admin', 'Tim Pengadaan'])
                    ),

                Tables\Actions\ViewAction::make(),

                // EXPORT ACTIONS
                Tables\Actions\Action::make('export_ranking')
                    ->label('Export Ranking')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn($record): string => route('export.ranking', ['prodiId' => $record->user->prodi ?? null]))
                    ->openUrlInNewTab()
                    ->visible(fn() => Auth::user()->hasRole(['Tim Pengadaan', 'super_admin'])),

                Tables\Actions\Action::make('export_procurement')
                    ->label('Export Pengadaan')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('warning')
                    ->url(fn($record): string => route('export.procurement', ['prodiId' => $record->user->prodi ?? null]))
                    ->openUrlInNewTab()
                    ->visible(fn() => Auth::user()->hasRole(['Tim Pengadaan', 'super_admin'])),

                // OPTIONAL: Quick link to ranking (will be built later)
                Tables\Actions\Action::make('lihat_ranking')
                    ->label('Lihat Ranking')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->url('/admin/rankings') // Will point to RankingResource later
                    ->visible(fn() => Auth::user()->hasRole('Tim Pengadaan')),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasRole(['super_admin', 'Tim Pengadaan'])),
                ]),
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
            'index' => Pages\ListPengajuanBahanAjars::route('/'),
            'create' => Pages\CreatePengajuanBahanAjar::route('/create'),
            'edit' => Pages\EditPengajuanBahanAjar::route('/{record}/edit'),
        ];
    }

    // BLOCK KAPRODI FROM ACCESSING EDIT PAGE DIRECTLY
    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        // Only Tim Pengadaan & Super Admin can edit
        return $user->hasRole(['super_admin', 'Tim Pengadaan']);
    }

    // ALSO DISABLE ROW CLICK TO EDIT
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    // UPDATED ACCESS CONTROL - REMOVED LABORAN
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // KAPRODI: Only see their own prodi's submissions
        if ($user->hasRole('Kaprodi')) {
            return $query->whereHas('user', function (Builder $q) use ($user) {
                $q->where('prodi', $user->prodi);
            });
        }

        // TIM PENGADAAN & SUPER ADMIN: See all submissions
        return $query;
    }

    // FIX: AUTO-POPULATE user_id AND status_pengajuan
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-populate user_id with current logged in user
        $data['user_id'] = Auth::id();

        // Ensure status is set to default
        $data['status_pengajuan'] = 'diajukan';

        return $data;
    }
}
