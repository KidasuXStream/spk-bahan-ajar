<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AhpComparisonResource\Pages;
use App\Models\AhpComparison;
use App\Models\AhpSession;
use App\Models\Kriteria;
use App\Models\AhpResult;
use App\Models\PengajuanBahanAjar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Actions;

class AhpComparisonResource extends Resource
{
    protected static ?string $model = AhpComparison::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'AHP Management';
    protected static ?string $navigationLabel = 'AHP - Analisis Kriteria';
    protected static ?string $pluralModelLabel = 'AHP - Analisis Kriteria';
    protected static ?string $slug = 'ahp-analysis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('AHP Analysis')
                    ->tabs([
                        // TAB 1: SESSION & MATRIX INPUT
                        Tabs\Tab::make('Perbandingan Kriteria')
                            ->icon('heroicon-o-table-cells')
                            ->schema([
                                // SESSION SELECTION
                                Section::make('Pilih Session AHP')
                                    ->description('Pilih tahun ajaran dan semester untuk perhitungan AHP')
                                    ->icon('heroicon-o-calendar')
                                    ->schema([
                                        Select::make('ahp_session_id')
                                            ->label('Tahun Ajaran - Semester')
                                            ->relationship('session', 'tahun_ajaran')
                                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->tahun_ajaran} - {$record->semester}")
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, $set, $get) {
                                                if ($state) {
                                                    Log::info('Session AHP selected', ['session_id' => $state]);
                                                    self::loadExistingMatrixData($state, $set);
                                                }
                                            })
                                            ->helperText('Pilih session AHP yang akan digunakan untuk perhitungan'),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),

                                // CRITERIA SELECTION & MATRIX
                                Section::make('Matriks Perbandingan Kriteria')
                                    ->description('Bandingkan setiap pasangan kriteria berdasarkan tingkat kepentingannya')
                                    ->icon('heroicon-o-table-cells')
                                    ->schema([
                                        ViewField::make('comparison_matrix')
                                            ->view('filament.forms.components.ahp-matrix')
                                            ->viewData(function ($get) {
                                                $sessionId = $get('ahp_session_id');

                                                if (!$sessionId) {
                                                    return [
                                                        'criteria' => [],
                                                        'session_id' => null,
                                                        'existing_data' => []
                                                    ];
                                                }

                                                $criteria = self::getCriteriaForMatrix($sessionId);
                                                $existingData = AhpComparison::getMatrixForSession($sessionId);

                                                return [
                                                    'criteria' => $criteria,
                                                    'session_id' => $sessionId,
                                                    'existing_data' => $existingData,
                                                ];
                                            })
                                            ->visible(fn($get) => filled($get('ahp_session_id')))
                                            ->live(),

                                        // Hidden fields to store matrix values
                                        Grid::make()
                                            ->schema(self::generateHiddenMatrixFields())
                                            ->visible(false),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),
                            ]),

                        // TAB 2: AHP RESULTS & CONSISTENCY
                        Tabs\Tab::make('Hasil & Konsistensi')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Section::make('Hasil Perhitungan AHP')
                                    ->description('Hasil bobot kriteria dan metrik konsistensi')
                                    ->icon('heroicon-o-chart-bar')
                                    ->schema([
                                        ViewField::make('ahp_results')
                                            ->view('filament.forms.components.ahp-results')
                                            ->viewData(function ($get) {
                                                $sessionId = $get('ahp_session_id');
                                                if (!$sessionId) {
                                                    return ['results' => null];
                                                }

                                                $results = AhpComparison::calculateAHPWeights($sessionId);
                                                return ['results' => $results];
                                            })
                                            ->visible(fn($get) => filled($get('ahp_session_id')))
                                            ->live(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),
                            ]),

                        // TAB 3: SAVED RESULTS & RANKING
                        Tabs\Tab::make('Data Tersimpan')
                            ->icon('heroicon-o-archive-box')
                            ->schema([
                                Section::make('Bobot Kriteria Tersimpan')
                                    ->description('Data bobot kriteria yang sudah disimpan dalam database')
                                    ->icon('heroicon-o-archive-box')
                                    ->schema([
                                        ViewField::make('saved_results')
                                            ->view('filament.forms.components.ahp-saved-results')
                                            ->viewData(function ($get) {
                                                $sessionId = $get('ahp_session_id');
                                                if (!$sessionId) return ['results' => []];

                                                $savedResults = AhpResult::where('ahp_session_id', $sessionId)
                                                    ->with('kriteria')
                                                    ->get();

                                                return ['results' => $savedResults];
                                            })
                                            ->visible(fn($get) => filled($get('ahp_session_id')))
                                            ->live(),

                                        // TOMBOL HITUNG AHP
                                        ViewField::make('calculate_ahp_button')
                                            ->view('filament.forms.components.ahp-calculate-button')
                                            ->viewData(function ($get) {
                                                $sessionId = $get('ahp_session_id');
                                                return ['session_id' => $sessionId];
                                            })
                                            ->visible(fn($get) => filled($get('ahp_session_id'))),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),

                                // RANKING PREVIEW
                                Section::make('Preview Ranking Pengajuan')
                                    ->description('Preview ranking berdasarkan bobot AHP yang sudah dihitung')
                                    ->icon('heroicon-o-list-bullet')
                                    ->schema([
                                        ViewField::make('ranking_preview')
                                            ->view('filament.forms.components.ahp-ranking-preview')
                                            ->viewData(function ($get) {
                                                $sessionId = $get('ahp_session_id');
                                                if (!$sessionId) {
                                                    Log::info('No session ID for ranking preview');
                                                    return ['rankings' => []];
                                                }

                                                $rankings = self::calculateRankingPreview($sessionId);
                                                Log::info('Ranking preview data', [
                                                    'session_id' => $sessionId,
                                                    'rankings_count' => count($rankings),
                                                    'rankings' => $rankings
                                                ]);
                                                return ['rankings' => $rankings];
                                            })
                                            ->visible(fn($get) => filled($get('ahp_session_id')))
                                            ->live(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),

                                // AHP RESULTS DETAIL
                                Section::make('Hasil Perhitungan AHP')
                                    ->description('Detail hasil perhitungan AHP dan bobot kriteria')
                                    ->icon('heroicon-o-calculator')
                                    ->schema([
                                        ViewField::make('ahp_results')
                                            ->view('filament.forms.components.ahp-results')
                                            ->viewData(function ($get) {
                                                $sessionId = $get('ahp_session_id');
                                                if (!$sessionId) {
                                                    return ['results' => null];
                                                }

                                                $results = AhpComparison::calculateAHPWeights($sessionId);
                                                return ['results' => $results];
                                            })
                                            ->visible(fn($get) => filled($get('ahp_session_id')))
                                            ->live(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('session.tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('session.semester')
                    ->label('Semester')
                    ->badge()
                    ->color(fn($state) => $state === 'Ganjil' ? 'success' : 'info')
                    ->sortable(),

                // SHOW MATRIX COMPLETION STATUS
                TextColumn::make('completion_status')
                    ->label('Status Matrix')
                    ->getStateUsing(function ($record) {
                        $stats = AhpComparison::getSessionStatistics($record->ahp_session_id);
                        return $stats['completion_percentage'] . '%';
                    })
                    ->badge()
                    ->color(fn($state) => match (true) {
                        floatval($state) >= 100 => 'success',
                        floatval($state) >= 50 => 'warning',
                        default => 'danger'
                    }),

                // SHOW CONSISTENCY STATUS
                TextColumn::make('consistency_status')
                    ->label('Konsistensi')
                    ->getStateUsing(function ($record) {
                        $results = AhpComparison::calculateAHPWeights($record->ahp_session_id);
                        $cr = $results['cr'] ?? 1;
                        return $cr < 0.1 ? 'Konsisten' : 'Tidak Konsisten';
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Konsisten' => 'success',
                        'Tidak Konsisten' => 'danger',
                        default => 'gray'
                    }),

                TextColumn::make('consistency_ratio')
                    ->label('CR Value')
                    ->getStateUsing(function ($record) {
                        $results = AhpComparison::calculateAHPWeights($record->ahp_session_id);
                        return number_format($results['cr'] ?? 0, 4);
                    })
                    ->sortable(),

                // COUNT PENGAJUAN FOR THIS SESSION
                TextColumn::make('pengajuan_count')
                    ->label('Jumlah Pengajuan')
                    ->getStateUsing(function ($record) {
                        return PengajuanBahanAjar::where('ahp_session_id', $record->ahp_session_id)->count();
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('calculate_save_ahp')
                    ->label('Hitung & Simpan')
                    ->icon('heroicon-o-calculator')
                    ->color('success')
                    ->action(function ($record) {
                        try {
                            $results = AhpComparison::calculateAHPWeights($record->ahp_session_id);

                            if (empty($results['weights'])) {
                                throw new \Exception('Tidak ada data perbandingan untuk dihitung');
                            }

                            // Save results to database
                            self::saveAHPResults($record->ahp_session_id, $results);

                            Notification::make()
                                ->title('AHP Berhasil Dihitung & Disimpan')
                                ->body("CR: " . number_format($results['cr'], 4) .
                                    " (" . ($results['consistent'] ? 'Konsisten' : 'Tidak Konsisten') . ")")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Perhitungan Gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Hitung & Simpan Hasil AHP')
                    ->modalDescription('Ini akan menghitung bobot kriteria dan menyimpannya ke database.')
                    ->visible(fn() => true), // Temporary fix - allow all users

                Tables\Actions\EditAction::make()
                    ->visible(fn() => true), // Temporary fix - allow all users
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAhpComparisons::route('/'),
            'create' => Pages\CreateAhpComparison::route('/create'),
            'edit' => Pages\EditAhpComparison::route('/{record}/edit'),
        ];
    }

    // CUSTOM METHODS
    protected static function generateHiddenMatrixFields(): array
    {
        $criteria = Kriteria::orderBy('id')->get();
        $fields = [];

        foreach ($criteria as $c1) {
            foreach ($criteria as $c2) {
                $fieldName = "matrix_{$c1->id}_{$c2->id}";
                $fields[] = Forms\Components\Hidden::make($fieldName)
                    ->default($c1->id === $c2->id ? 1 : 1)
                    ->live();
            }
        }

        return $fields;
    }

    protected static function loadExistingMatrixData($sessionId, $set): void
    {
        if (!$sessionId) return;

        try {
            $existingMatrix = AhpComparison::getMatrixForSession($sessionId);
            foreach ($existingMatrix as $key => $value) {
                $set($key, $value);
            }
        } catch (\Exception $e) {
            Log::error('Failed to load existing matrix data', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected static function getCriteriaForMatrix($sessionId): array
    {
        if (!$sessionId) return [];

        $criteria = Kriteria::orderBy('id')->get();

        return $criteria->map(function ($criterion) {
            return [
                'id' => $criterion->id,
                'kode' => $criterion->kode_kriteria,
                'nama' => $criterion->nama_kriteria,
            ];
        })->toArray();
    }

    protected static function saveAHPResults($sessionId, $results): void
    {
        try {
            if (empty($results['weights'])) {
                Log::warning('No weights to save', ['session_id' => $sessionId]);
                return;
            }

            DB::beginTransaction();

            // Delete existing results
            AhpResult::where('ahp_session_id', $sessionId)->delete();

            // Save new results
            foreach ($results['weights'] as $criteriaName => $weight) {
                $criteria = Kriteria::where('nama_kriteria', $criteriaName)->first();
                if ($criteria && $weight > 0) {
                    AhpResult::create([
                        'ahp_session_id' => $sessionId,
                        'kriteria_id' => $criteria->id,
                        'bobot' => $weight,
                    ]);
                }
            }

            // Update session status to completed
            AhpSession::where('id', $sessionId)->update(['status' => 'completed']);

            DB::commit();

            Log::info('AHP results saved successfully', [
                'session_id' => $sessionId,
                'weights_count' => count($results['weights']),
                'consistency_ratio' => $results['cr']
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save AHP results', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected static function calculateRankingPreview($sessionId): array
    {
        // Get AHP weights
        $weights = AhpResult::where('ahp_session_id', $sessionId)
            ->with('kriteria')
            ->get()
            ->pluck('bobot', 'kriteria.nama_kriteria');

        if ($weights->isEmpty()) {
            return [];
        }

        // Get pengajuan for this session
        $pengajuan = PengajuanBahanAjar::where('ahp_session_id', $sessionId)
            ->with('user')
            ->get();

        $rankings = [];

        foreach ($pengajuan as $item) {
            $score = 0;

            // Calculate weighted score based on criteria
            if (isset($weights['Harga'])) {
                $score += $weights['Harga'] * (1 / max($item->getHargaValue(), 1));
            }
            if (isset($weights['Jumlah'])) {
                $score += $weights['Jumlah'] * $item->getJumlahValue();
            }
            if (isset($weights['Stok'])) {
                $score += $weights['Stok'] * (1 / max($item->getStokValue(), 1));
            }
            if (isset($weights['Urgensi'])) {
                $urgency = $item->urgensi_institusi ?? $item->urgensi_prodi;
                $urgencyValue = match ($urgency) {
                    'tinggi' => 3,
                    'sedang' => 2,
                    'rendah' => 1,
                    default => 1
                };
                $score += $weights['Urgensi'] * $urgencyValue;
            }

            $rankings[] = [
                'id' => $item->id,
                'nama_barang' => $item->nama_barang,
                'pengaju' => $item->user->name,
                'prodi' => $item->user->prodi,
                'score' => $score,
                'harga' => $item->harga_satuan,
                'jumlah' => $item->jumlah,
                'stok' => $item->stok ?? 0,
            ];
        }

        // Sort by score descending
        usort($rankings, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($rankings, 0, 10);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $sessionId = $data['ahp_session_id'] ?? null;

        if (!$sessionId) {
            throw new \Exception('Session AHP harus dipilih');
        }

        try {
            // Extract matrix data
            $matrixData = [];
            foreach ($data as $key => $value) {
                if (str_starts_with($key, 'matrix_') && is_numeric($value)) {
                    $matrixData[$key] = (float) $value;
                }
            }

            Log::info('Extracted matrix data', [
                'session_id' => $sessionId,
                'matrix_data_count' => count($matrixData),
                'matrix_data_keys' => array_keys($matrixData),
                'all_data_keys' => array_keys($data)
            ]);

            // Save matrix
            if (!empty($matrixData)) {
                $saved = AhpComparison::saveMatrixForSession($sessionId, $matrixData);

                if ($saved) {
                    $results = AhpComparison::calculateAHPWeights($sessionId);

                    if ($results['consistent']) {
                        Notification::make()
                            ->title('AHP Berhasil - Konsisten!')
                            ->body("Matrix tersimpan. CR: " . number_format($results['cr'], 4))
                            ->success()
                            ->send();

                        self::saveAHPResults($sessionId, $results);
                    } else {
                        Notification::make()
                            ->title('AHP Tersimpan - Tidak Konsisten')
                            ->body("CR: " . number_format($results['cr'], 4) . ". Periksa kembali perbandingan.")
                            ->warning()
                            ->send();
                    }
                } else {
                    Notification::make()
                        ->title('Error')
                        ->body('Gagal menyimpan matrix data')
                        ->danger()
                        ->send();
                }
            } else {
                Log::warning('No matrix data found', [
                    'session_id' => $sessionId,
                    'data_keys' => array_keys($data)
                ]);

                Notification::make()
                    ->title('Warning')
                    ->body('Tidak ada data matrix yang ditemukan')
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            Log::error('Failed to process AHP', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error')
                ->body('Gagal memproses AHP: ' . $e->getMessage())
                ->danger()
                ->send();
        }

        return self::getMinimalFormData($sessionId);
    }

    public static function mutateFormDataBeforeUpdate(array $data, $record): array
    {
        $sessionId = $data['ahp_session_id'] ?? $record->ahp_session_id ?? null;

        if (!$sessionId) {
            throw new \Exception('Session AHP harus dipilih');
        }

        try {
            // Extract matrix data
            $matrixData = [];
            foreach ($data as $key => $value) {
                if (str_starts_with($key, 'matrix_') && is_numeric($value)) {
                    $matrixData[$key] = (float) $value;
                }
            }

            Log::info('Extracted matrix data for update', [
                'session_id' => $sessionId,
                'matrix_data_count' => count($matrixData),
                'matrix_data_keys' => array_keys($matrixData),
                'all_data_keys' => array_keys($data)
            ]);

            // Save matrix
            if (!empty($matrixData)) {
                $saved = AhpComparison::saveMatrixForSession($sessionId, $matrixData);

                if ($saved) {
                    $results = AhpComparison::calculateAHPWeights($sessionId);

                    if ($results['consistent']) {
                        Notification::make()
                            ->title('AHP Berhasil Diupdate - Konsisten!')
                            ->body("Matrix tersimpan. CR: " . number_format($results['cr'], 4))
                            ->success()
                            ->send();

                        self::saveAHPResults($sessionId, $results);
                    } else {
                        Notification::make()
                            ->title('AHP Diupdate - Tidak Konsisten')
                            ->body("CR: " . number_format($results['cr'], 4) . ". Periksa kembali perbandingan.")
                            ->warning()
                            ->send();
                    }
                } else {
                    Notification::make()
                        ->title('Error')
                        ->body('Gagal menyimpan matrix data')
                        ->danger()
                        ->send();
                }
            } else {
                Log::warning('No matrix data found for update', [
                    'session_id' => $sessionId,
                    'data_keys' => array_keys($data)
                ]);

                Notification::make()
                    ->title('Warning')
                    ->body('Tidak ada data matrix yang ditemukan')
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            Log::error('Failed to process AHP update', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error')
                ->body('Gagal memproses AHP: ' . $e->getMessage())
                ->danger()
                ->send();
        }

        return $data;
    }

    protected static function getMinimalFormData($sessionId = null): array
    {
        if (!$sessionId) {
            $session = AhpSession::first();
            $sessionId = $session ? $session->id : null;
        }

        $firstKriteria = Kriteria::orderBy('id')->first();
        $secondKriteria = Kriteria::orderBy('id')->skip(1)->first();

        if (!$sessionId || !$firstKriteria || !$secondKriteria) {
            throw new \Exception('Data minimal tidak tersedia');
        }

        return [
            'ahp_session_id' => $sessionId,
            'kriteria_1_id' => $firstKriteria->id,
            'kriteria_2_id' => $secondKriteria->id,
            'nilai' => 1.0,
        ];
    }

    // Authorization
    public static function canCreate(): bool
    {
        return true; // Temporary fix - allow all users to create
    }

    public static function canEdit($record): bool
    {
        return true; // Temporary fix - allow all users to edit
    }

    public static function canDelete($record): bool
    {
        return true; // Temporary fix - allow all users to delete
    }

    public static function getNavigationBadge(): ?string
    {
        $total = AhpSession::count();
        return $total > 0 ? (string) $total : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}