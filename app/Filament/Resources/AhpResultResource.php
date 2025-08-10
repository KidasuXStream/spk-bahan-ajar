<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AhpResultResource\Pages;
use App\Models\AhpResult;
use App\Models\AhpSession;
use App\Models\PengajuanBahanAjar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class AhpResultResource extends Resource
{
    protected static ?string $model = AhpResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'AHP';

    protected static ?string $navigationLabel = 'Hasil AHP & Ranking';

    protected static ?int $navigationSort = 2;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Session AHP')
                    ->schema([
                        Placeholder::make('tahun_ajaran')
                            ->label('Tahun Ajaran')
                            ->content(fn($record) => $record->session?->tahun_ajaran ?? 'Tidak ada data'),

                        Placeholder::make('semester')
                            ->label('Semester')
                            ->content(fn($record) => $record->session?->semester ?? 'Tidak ada data'),

                        Placeholder::make('status_session')
                            ->label('Status Session')
                            ->content(fn($record) => $record->session?->status ?? 'Tidak ada data'),

                        Placeholder::make('is_active')
                            ->label('Session Aktif')
                            ->content(fn($record) => $record->session?->is_active ? 'Ya' : 'Tidak'),
                    ])
                    ->columns(2),

                Section::make('Informasi Kriteria')
                    ->schema([
                        Placeholder::make('kode_kriteria')
                            ->label('Kode Kriteria')
                            ->content(fn($record) => $record->kriteria?->kode_kriteria ?? 'Tidak ada data'),

                        Placeholder::make('nama_kriteria')
                            ->label('Nama Kriteria')
                            ->content(fn($record) => $record->kriteria?->nama_kriteria ?? 'Tidak ada data'),

                        Placeholder::make('jenis_kriteria')
                            ->label('Jenis Kriteria')
                            ->content(fn($record) => $record->kriteria?->jenis ?? 'Tidak ada data'),

                        Placeholder::make('satuan')
                            ->label('Satuan')
                            ->content(fn($record) => $record->kriteria?->satuan ?? 'Tidak ada data'),

                        Placeholder::make('deskripsi_kriteria')
                            ->label('Deskripsi Kriteria')
                            ->content(fn($record) => $record->kriteria?->deskripsi ?? 'Tidak ada data')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Hasil Perhitungan AHP')
                    ->schema([
                        Placeholder::make('bobot_awal')
                            ->label('Bobot Awal Kriteria')
                            ->content(fn($record) => $record->kriteria?->bobot_awal ? number_format($record->kriteria->bobot_awal, 4) : 'Tidak ada data'),

                        Placeholder::make('bobot_ahp')
                            ->label('Bobot AHP Final')
                            ->content(fn($record) => $record->bobot ? number_format($record->bobot, 6) : 'Tidak ada data'),

                        Placeholder::make('ci_value')
                            ->label('Consistency Index (CI)')
                            ->content(function ($record) {
                                if (!$record->ahp_session_id) return 'Tidak ada data';
                                $results = self::getResultsData($record->ahp_session_id);
                                return isset($results['ci']) ? number_format($results['ci'], 6) : 'Tidak ada data';
                            }),

                        Placeholder::make('cr_value')
                            ->label('Consistency Ratio (CR)')
                            ->content(function ($record) {
                                if (!$record->ahp_session_id) return 'Tidak ada data';
                                $results = self::getResultsData($record->ahp_session_id);
                                return isset($results['cr']) ? number_format($results['cr'], 6) : 'Tidak ada data';
                            }),

                        Placeholder::make('consistency_status')
                            ->label('Status Konsistensi')
                            ->content(function ($record) {
                                if (!$record->ahp_session_id) return 'Tidak ada data';
                                $results = self::getResultsData($record->ahp_session_id);
                                if (isset($results['consistent'])) {
                                    return $results['consistent'] ? '✅ Konsisten (CR < 0.1)' : '❌ Tidak Konsisten (CR ≥ 0.1)';
                                }
                                return 'Tidak ada data';
                            })
                            ->columnSpanFull(),

                        Placeholder::make('lambda_max')
                            ->label('Lambda Max (λmax)')
                            ->content(function ($record) {
                                if (!$record->ahp_session_id) return 'Tidak ada data';
                                $results = self::getResultsData($record->ahp_session_id);
                                return isset($results['lambda_max']) ? number_format($results['lambda_max'], 6) : 'Tidak ada data';
                            }),

                        Placeholder::make('total_kriteria')
                            ->label('Total Kriteria')
                            ->content(function ($record) {
                                if (!$record->ahp_session_id) return 'Tidak ada data';
                                return AhpResult::where('ahp_session_id', $record->ahp_session_id)->count();
                            }),
                    ])
                    ->columns(2),

                Section::make('Chart & Visualisasi')
                    ->schema([
                        ViewField::make('ranking_charts')
                            ->label('Chart Ranking & Score')
                            ->view('filament.forms.components.ahp-ranking-preview')
                            ->viewData(function ($record) {
                                if (!$record->ahp_session_id) return ['rankings' => []];

                                $rankings = self::getRankingData($record->ahp_session_id);
                                return [
                                    'rankings' => $rankings,
                                    'session_id' => $record->ahp_session_id,
                                    'kriteria_name' => $record->kriteria?->nama_kriteria
                                ];
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Ranking Pengajuan Bahan Ajar')
                    ->schema([
                        ViewField::make('ranking_table')
                            ->label('Tabel Ranking Lengkap')
                            ->view('filament.forms.components.ahp-ranking-table')
                            ->viewData(function ($record) {
                                if (!$record->ahp_session_id) return ['rankings' => []];

                                $rankings = self::getRankingData($record->ahp_session_id);
                                return [
                                    'rankings' => $rankings,
                                    'session_id' => $record->ahp_session_id,
                                    'export_routes' => [
                                        'ranking_per_prodi' => route('export.ranking'),
                                        'summary_per_prodi' => route('export.summary', $record->ahp_session_id),
                                        'procurement_list' => route('export.procurement'),
                                        'export_form' => route('export.form', $record->ahp_session_id),
                                        'ahp_results' => route('export.ahp-results', $record->ahp_session_id),
                                        'ranking_advanced' => route('export.ranking.advanced', $record->ahp_session_id)
                                    ]
                                ];
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Timestamps')
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('Dibuat Pada')
                            ->content(fn($record) => $record->created_at?->format('d/m/Y H:i:s') ?? 'Tidak ada data'),

                        Placeholder::make('updated_at')
                            ->label('Diupdate Pada')
                            ->content(fn($record) => $record->updated_at?->format('d/m/Y H:i:s') ?? 'Tidak ada data'),
                    ])
                    ->columns(2),
            ])
            ->disabled(); // Keep form fields disabled for read-only
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

                TextColumn::make('kriteria.nama_kriteria')
                    ->label('Kriteria')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bobot')
                    ->label('Bobot')
                    ->numeric(
                        decimalPlaces: 4,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('view_details')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn(AhpResult $record): string => route('filament.admin.resources.ahp-results.view', $record))
                    ->openUrlInNewTab(),

                Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(AhpResult $record): string => route('export.form', $record->ahp_session_id))
                    ->openUrlInNewTab()
                    ->color('success')
                    ->visible(fn(AhpResult $record): bool => $record->ahp_session_id !== null),
            ])
            ->bulkActions([
                // No bulk actions needed for view-only resource
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
            'index' => Pages\ListAhpResults::route('/'),
            'create' => Pages\CreateAhpResult::route('/create'),
            'edit' => Pages\EditAhpResult::route('/{record}/edit'),
            'view' => Pages\ViewAhpResult::route('/{record}'),
        ];
    }

    protected static function getResultsData($sessionId): array
    {
        try {
            $results = \App\Models\AhpComparison::calculateAHPWeights($sessionId);
            return $results;
        } catch (\Exception $e) {
            Log::error('Failed to get AHP results', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    protected static function getRankingData($sessionId): array
    {
        try {
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
                // Calculate normalized values for each criteria
                $normalizedValues = self::calculateNormalizedValues($item, $sessionId);

                // Calculate weighted scores for each criteria
                $weightedScores = [];
                $totalScore = 0;
                $criteriaCount = 0;

                foreach ($weights as $criteriaName => $weight) {
                    $normalizedValue = $normalizedValues[$criteriaName] ?? 0;
                    $weightedScore = $weight * $normalizedValue;
                    $weightedScores[$criteriaName] = [
                        'normalized_value' => $normalizedValue,
                        'weight' => $weight,
                        'weighted_score' => $weightedScore
                    ];
                    $totalScore += $weightedScore;
                    $criteriaCount++;
                }

                // Calculate average score
                $averageScore = $criteriaCount > 0 ? $totalScore / $criteriaCount : 0;

                // Calculate grade based on average score
                $grade = self::calculateGrade($averageScore);

                $rankings[] = [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'pengaju' => $item->user->name,
                    'prodi' => $item->user->prodi,
                    'score' => $totalScore,
                    'avg_score' => $averageScore,
                    'grade' => $grade,
                    'priority_status' => self::calculatePriorityStatus($averageScore),
                    'harga' => $item->harga_satuan,
                    'jumlah' => $item->jumlah,
                    'stok' => $item->stok ?? 0,
                    'urgensi_prodi' => $item->urgensi_prodi,
                    'urgensi_institusi' => $item->urgensi_institusi,
                    'urgensi_tim_pengadaan' => $item->urgensi_tim_pengadaan,
                    'normalized_values' => $normalizedValues,
                    'weighted_scores' => $weightedScores,
                    'weights' => $weights
                ];
            }

            // Sort by average score descending
            usort($rankings, fn($a, $b) => $b['avg_score'] <=> $a['avg_score']);

            return array_slice($rankings, 0, 10);
        } catch (\Exception $e) {
            Log::error('Failed to get ranking data', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    protected static function calculateGrade($averageScore): string
    {
        // AHP scores are typically low (0.1 - 0.5 range)
        // Adjusted thresholds for more realistic grading
        // For cases with few items, scores tend to be higher due to normalization

        // Special handling for cases with very few items (normalization issues)
        // When all normalized values are 1.0, the score becomes artificially high

        // REALISTIC THRESHOLDS FOR PRODI DATA (2-5 items per semester)
        // Based on analysis of typical AHP scores for teaching material selection

        // Method 1: Percentile-based (recommended for real data)
        // if ($averageScore >= 0.25) return 'A';  // Top 20%
        // if ($averageScore >= 0.20) return 'B';  // Top 40%
        // if ($averageScore >= 0.15) return 'C';  // Top 60%
        // if ($averageScore >= 0.10) return 'D';  // Top 80%
        // return 'E';                             // Bottom 20%

        // Method 2: Rule-based (current implementation)
        if ($averageScore >= 0.3) return 'A';      // Excellent (≥ 0.3) - Very good for prodi data
        if ($averageScore >= 0.2) return 'B';      // Good (≥ 0.2) - Good for prodi data
        if ($averageScore >= 0.15) return 'C';     // Average (≥ 0.15) - Average for prodi data
        if ($averageScore >= 0.1) return 'D';      // Poor (≥ 0.1) - Poor for prodi data
        return 'E';                                // Very Poor (< 0.1) - Very poor for prodi data
    }

    /**
     * Calculate priority status based on AHP score
     */
    protected static function calculatePriorityStatus($averageScore): string
    {
        // Priority status for procurement planning
        if ($averageScore >= 0.25) return 'Diprioritaskan';      // High priority (A grade)
        if ($averageScore >= 0.15) return 'Sedang';              // Medium priority (B-C grade)
        return 'Dapat Ditunda';                                   // Low priority (D-E grade)
    }

    /**
     * Analyze historical data to determine optimal thresholds
     */
    protected static function analyzeHistoricalData(): array
    {
        // Get all historical scores from all sessions
        $allScores = [];

        $sessions = \App\Models\AhpSession::with('pengajuanBahanAjar')->get();

        foreach ($sessions as $session) {
            $pengajuan = $session->pengajuanBahanAjar;
            if ($pengajuan->count() > 0) {
                // Calculate scores for this session
                $weights = AhpResult::where('ahp_session_id', $session->id)
                    ->with('kriteria')
                    ->get()
                    ->pluck('bobot', 'kriteria.nama_kriteria');

                if (!$weights->isEmpty()) {
                    foreach ($pengajuan as $item) {
                        $normalizedValues = self::calculateNormalizedValues($item, $session->id);
                        $totalScore = 0;
                        $criteriaCount = 0;

                        foreach ($weights as $criteriaName => $weight) {
                            $normalizedValue = $normalizedValues[$criteriaName] ?? 0;
                            $weightedScore = $weight * $normalizedValue;
                            $totalScore += $weightedScore;
                            $criteriaCount++;
                        }

                        $averageScore = $criteriaCount > 0 ? $totalScore / $criteriaCount : 0;
                        $allScores[] = $averageScore;
                    }
                }
            }
        }

        if (empty($allScores)) {
            return [
                'count' => 0,
                'min' => 0,
                'max' => 0,
                'avg' => 0,
                'std_dev' => 0,
                'percentiles' => [],
                'recommended_thresholds' => []
            ];
        }

        // Calculate statistics
        $count = count($allScores);
        $min = min($allScores);
        $max = max($allScores);
        $avg = array_sum($allScores) / $count;

        // Calculate standard deviation
        $variance = 0;
        foreach ($allScores as $score) {
            $variance += pow($score - $avg, 2);
        }
        $stdDev = sqrt($variance / $count);

        // Calculate percentiles
        sort($allScores);
        $percentiles = [
            'p20' => $allScores[floor($count * 0.2)] ?? 0,
            'p40' => $allScores[floor($count * 0.4)] ?? 0,
            'p60' => $allScores[floor($count * 0.6)] ?? 0,
            'p80' => $allScores[floor($count * 0.8)] ?? 0,
        ];

        // Recommended thresholds based on analysis
        $recommendedThresholds = [
            'percentile_based' => [
                'A' => $percentiles['p80'],
                'B' => $percentiles['p60'],
                'C' => $percentiles['p40'],
                'D' => $percentiles['p20'],
            ],
            'statistical_based' => [
                'A' => $avg + (1.5 * $stdDev),
                'B' => $avg + (0.5 * $stdDev),
                'C' => $avg,
                'D' => $avg - (0.5 * $stdDev),
            ]
        ];

        return [
            'count' => $count,
            'min' => $min,
            'max' => $max,
            'avg' => $avg,
            'std_dev' => $stdDev,
            'percentiles' => $percentiles,
            'recommended_thresholds' => $recommendedThresholds,
            'all_scores' => $allScores
        ];
    }

    protected static function calculateNormalizedValues($item, $sessionId): array
    {
        // Get all items in this session for normalization
        $allItems = PengajuanBahanAjar::where('ahp_session_id', $sessionId)->get();
        $itemCount = $allItems->count();

        $maxHarga = $allItems->max('harga_satuan');
        $minHarga = $allItems->min('harga_satuan');
        $maxJumlah = $allItems->max('jumlah');
        $minJumlah = $allItems->min('jumlah');
        $maxStok = $allItems->max('stok');
        $minStok = $allItems->min('stok');

        // Special handling for cases with very few items
        if ($itemCount <= 2) {
            // For very few items, use absolute scoring instead of relative normalization
            $normalizedHarga = $maxHarga > 0 ? (1 - ($item->harga_satuan / $maxHarga)) : 0.5;
            $normalizedJumlah = $maxJumlah > 0 ? ($item->jumlah / $maxJumlah) : 0.5;
            $normalizedStok = $maxStok > 0 ? (1 - ($item->stok / $maxStok)) : 0.5;
        } else {
            // Normal normalization for multiple items
            $normalizedHarga = ($maxHarga == $minHarga) ? 1.0 : ($maxHarga - $item->harga_satuan) / ($maxHarga - $minHarga);
            $normalizedJumlah = ($maxJumlah == $minJumlah) ? 1.0 : ($item->jumlah - $minJumlah) / ($maxJumlah - $minJumlah);
            $normalizedStok = ($maxStok == $minStok) ? 1.0 : ($maxStok - $item->stok) / ($maxStok - $minStok);
        }

        // Normalize Urgensi (benefit criteria - higher is better)
        $prodiValue = match ($item->urgensi_prodi) {
            'tinggi' => 3,
            'sedang' => 2,
            'rendah' => 1,
            default => 1
        };

        $institusiValue = match ($item->urgensi_institusi) {
            'tinggi' => 3,
            'sedang' => 2,
            'rendah' => 1,
            default => 1
        };

        $normalizedUrgensi = ($prodiValue + $institusiValue) / 6; // Max is 6

        return [
            'Harga' => $normalizedHarga,
            'Jumlah' => $normalizedJumlah,
            'Stok' => $normalizedStok,
            'Urgensi' => $normalizedUrgensi,
        ];
    }
}