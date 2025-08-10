<?php

namespace App\Filament\Resources\AhpComparisonResource\Pages;

use App\Filament\Resources\AhpComparisonResource;
use App\Models\AhpComparison;
use App\Models\AhpSession;
use App\Models\Kriteria;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CreateAhpComparison extends CreateRecord
{
    protected static string $resource = AhpComparisonResource::class;

    protected static ?string $title = 'Analisis AHP - Perbandingan Kriteria';

    /**
     * OVERRIDE: Handle form submission tanpa create record
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $sessionId = $data['ahp_session_id'] ?? null;

        if (!$sessionId) {
            Notification::make()
                ->title('Error')
                ->body('Session AHP harus dipilih')
                ->danger()
                ->send();

            $this->halt();
        }

        try {
            Log::info('🚀 Processing AHP from CreateRecord', ['session_id' => $sessionId]);

            // Extract matrix data
            $matrixData = [];
            foreach ($data as $key => $value) {
                if (str_starts_with($key, 'matrix_') && is_numeric($value)) {
                    $matrixData[$key] = (float) $value;
                }
            }

            Log::info('Matrix data extracted in CreateRecord', [
                'session_id' => $sessionId,
                'matrix_entries' => count($matrixData)
            ]);

            DB::beginTransaction();

            // Save matrix
            $saved = AhpComparison::saveMatrixForSession($sessionId, $matrixData);

            if ($saved) {
                // Calculate AHP
                $results = AhpComparison::calculateAHPWeights($sessionId);

                // Save results
                $this->saveAHPResults($sessionId, $results);

                DB::commit();

                $statusMessage = $results['consistent']
                    ? "✅ Konsisten (CR: " . number_format($results['cr'], 4) . ")"
                    : "⚠️ Tidak Konsisten (CR: " . number_format($results['cr'], 4) . ")";

                Notification::make()
                    ->title('AHP Berhasil Diproses!')
                    ->body($statusMessage)
                    ->success()
                    ->send();

                // Redirect to index
                $this->redirect(static::getResource()::getUrl('index'));
            } else {
                DB::rollBack();
                throw new \Exception('Gagal menyimpan matriks AHP');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('AHP processing failed in CreateRecord', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            Notification::make()
                ->title('Error Proses AHP')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }

        // Return dummy record - tidak akan disimpan ke database
        // Karena kita sudah redirect di atas
        return new AhpComparison([
            'ahp_session_id' => $sessionId,
            'kriteria_1_id' => 1,
            'kriteria_2_id' => 2,
            'nilai' => 1.0
        ]);
    }

    /**
     * Save AHP results
     */
    protected function saveAHPResults($sessionId, $results): void
    {
        try {
            if (empty($results['weights'])) {
                Log::warning('No weights to save', ['session_id' => $sessionId]);
                return;
            }

            // Delete existing results
            \App\Models\AhpResult::where('ahp_session_id', $sessionId)->delete();

            // Save new results
            foreach ($results['weights'] as $criteriaName => $weight) {
                $criteria = Kriteria::where('nama_kriteria', $criteriaName)->first();
                if ($criteria && $weight > 0) {
                    \App\Models\AhpResult::create([
                        'ahp_session_id' => $sessionId,
                        'kriteria_id' => $criteria->id,
                        'bobot' => $weight,
                    ]);
                }
            }

            // Update session status to completed
            \App\Models\AhpSession::where('id', $sessionId)->update(['status' => 'completed']);

            Log::info('AHP results saved successfully', [
                'session_id' => $sessionId,
                'weights_count' => count($results['weights'])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save AHP results', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * OVERRIDE: After creation redirect
     */
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    /**
     * Custom success notification
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'AHP Analysis Completed Successfully';
    }
}
