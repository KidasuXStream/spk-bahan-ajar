<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $modelLabel = 'Pengguna';
    protected static ?string $pluralModelLabel = 'Pengguna';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dasar')
                    ->description('Data pribadi pengguna')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Masukkan nama lengkap'),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->required()
                                    ->email()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('contoh@email.com'),
                            ]),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->placeholder('Minimal 8 karakter')
                            ->confirmed(),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->dehydrated(false)
                            ->required(fn(string $context): bool => $context === 'create')
                            ->placeholder('Ulangi password')
                            ->same('password'),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Section::make('Informasi Kepegawaian')
                    ->description('Data kepegawaian dan akademik')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nidn')
                                    ->label('NIDN')
                                    ->maxLength(10)
                                    ->placeholder('10 digit NIDN')
                                    ->regex('/^\d{10}$/')
                                    ->helperText('NIDN harus 10 digit angka')
                                    ->nullable(),

                                Forms\Components\TextInput::make('nip')
                                    ->label('NIP')
                                    ->maxLength(18)
                                    ->placeholder('18 digit NIP')
                                    ->regex('/^\d{18}$/')
                                    ->helperText('NIP harus 18 digit angka')
                                    ->nullable(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Section::make('Role dan Program Studi')
                    ->description('Penentuan role dan program studi')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Role')
                            ->options([
                                'Kaprodi' => 'Kaprodi',
                                'Tim Pengadaan' => 'Tim Pengadaan',
                            ])
                            ->searchable()
                            ->placeholder('Pilih role pengguna')
                            ->multiple(false)
                            ->required()
                            ->live()
                            ->default(function ($record) {
                                if ($record) {
                                    $role = $record->roles->first();
                                    return $role ? $role->name : null;
                                }
                                return null;
                            }),

                        Forms\Components\Select::make('prodi')
                            ->label('Program Studi')
                            ->options([
                                'trpl' => 'Teknologi Rekayasa Perangkat Lunak',
                                'mesin' => 'Mesin',
                                'elektro' => 'Elektro',
                                'mekatronika' => 'Mekatronika',
                            ])
                            ->searchable()
                            ->placeholder('Pilih program studi')
                            ->helperText('Program studi hanya untuk Kaprodi')
                            ->visible(fn(Get $get): bool => $get('roles') === 'Kaprodi')
                            ->required(fn(Get $get): bool => $get('roles') === 'Kaprodi'),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email berhasil disalin!'),

                Tables\Columns\TextColumn::make('nidn')
                    ->label('NIDN')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('prodi')
                    ->label('Program Studi')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'trpl' => 'Teknologi Rekayasa Perangkat Lunak',
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
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->getStateUsing(function ($record) {
                        $role = $record->roles->first();
                        return $role ? $role->name : null;
                    })
                    ->badge()
                    ->colors([
                        'super_admin' => 'danger',
                        'Kaprodi' => 'warning',
                        'Tim Pengadaan' => 'success',
                    ])
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Filter by Role')
                    ->relationship('roles', 'name')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'Kaprodi' => 'Kaprodi',
                        'Tim Pengadaan' => 'Tim Pengadaan',
                    ])
                    ->searchable()
                    ->preload(),

                SelectFilter::make('prodi')
                    ->label('Filter by Program Studi')
                    ->options([
                        'trpl' => 'Teknologi Rekayasa Perangkat Lunak',
                        'mesin' => 'Mesin',
                        'elektro' => 'Elektro',
                        'mekatronika' => 'Mekatronika',
                    ])
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Tidak ada data ditemukan')
            ->emptyStateDescription('Belum ada data pengguna yang dimasukkan.')
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Tambah Pengguna')
                    ->url(route('filament.admin.resources.users.create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('roles');
    }

    public static function canEdit($record): bool
    {
        return true; // Allow all authenticated users to edit for now
    }
}
