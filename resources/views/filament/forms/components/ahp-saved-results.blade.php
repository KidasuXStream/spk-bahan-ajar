{{-- resources/views/filament/forms/components/ahp-saved-results.blade.php --}}

<div class="space-y-4">
    @if (!empty($results) && $results->count() > 0)
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800">
                <h3 class="text-md font-semibold text-gray-900 dark:text-white">
                    📊 Bobot Kriteria Tersimpan ({{ $results->count() }} kriteria)
                </h3>
            </div>

            <div class="p-4 space-y-3">
                @foreach ($results->sortByDesc('bobot') as $result)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ $result->kriteria->kode_kriteria }} - {{ $result->kriteria->nama_kriteria }}
                            </span>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-blue-600">{{ number_format($result->bobot, 4) }}</div>
                            <div class="text-sm text-gray-500">{{ number_format($result->bobot * 100, 1) }}%</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-4 py-3 bg-green-50 dark:bg-green-900/20 border-t">
                <p class="text-sm text-green-700 dark:text-green-300">
                    ✅ Data bobot sudah tersimpan dan siap digunakan untuk ranking pengajuan
                </p>
            </div>
        </div>
    @else
        <div class="text-center py-8 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="text-gray-500 dark:text-gray-400 mb-2">📋</div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum Ada Data Tersimpan</h3>
            <p class="text-gray-500 dark:text-gray-400">
                Bobot kriteria akan tersimpan setelah perhitungan AHP selesai
            </p>
        </div>
    @endif
</div>
