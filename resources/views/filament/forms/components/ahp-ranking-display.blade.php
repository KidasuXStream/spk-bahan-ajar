{{-- resources/views/filament/forms/components/ahp-ranking-display.blade.php --}}

<div class="space-y-6">
    @if (!empty($rankings) && count($rankings) > 0)
        <!-- RANKING TABLE -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div
                class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-md font-semibold text-blue-900 dark:text-blue-100">
                            🏆 Ranking Pengajuan Bahan Ajar
                        </h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            Ranking berdasarkan nilai rata-rata (Average Score) dengan kategorisasi Grade
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('export.form') }}"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Export Data
                        </a>
                        @if (isset($rankings[0]['prodi']))
                            <a href="{{ route('export.ranking', ['prodiId' => $rankings[0]['prodi']]) }}"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Export {{ strtoupper($rankings[0]['prodi']) }}
                            </a>
                            <a href="{{ route('export.procurement', ['prodiId' => $rankings[0]['prodi']]) }}"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>
                                Shopping List
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Rank
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Nama Barang
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Pengaju
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Prodi
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Avg. Score
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Grade
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Priority Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Detail AHP
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($rankings as $index => $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if ($index === 0)
                                            <span
                                                class="flex items-center justify-center w-8 h-8 rounded-full bg-yellow-100 text-yellow-800 border-2 border-yellow-300 text-sm font-bold">
                                                🥇
                                            </span>
                                        @elseif ($index === 1)
                                            <span
                                                class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-800 border-2 border-gray-300 text-sm font-bold">
                                                🥈
                                            </span>
                                        @elseif ($index === 2)
                                            <span
                                                class="flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800 border-2 border-orange-300 text-sm font-bold">
                                                🥉
                                            </span>
                                        @else
                                            <span
                                                class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 border-2 border-blue-300 text-sm font-bold">
                                                {{ $index + 1 }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ Str::limit($item['nama_barang'], 30) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $item['pengaju'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ strtoupper($item['prodi'] ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                        {{ number_format($item['avg_score'], 3) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $gradeColors = [
                                            'A' =>
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border-green-300',
                                            'B' =>
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border-blue-300',
                                            'C' =>
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 border-yellow-300',
                                            'D' =>
                                                'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 border-orange-300',
                                            'E' =>
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border-red-300',
                                        ];
                                        $gradeColor =
                                            $gradeColors[$item['grade']] ??
                                            'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 border-gray-300';
                                    @endphp
                                    <span
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold border-2 {{ $gradeColor }}">
                                        {{ $item['grade'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $priorityColors = [
                                            'Diprioritaskan' =>
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border-green-300',
                                            'Sedang' =>
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 border-yellow-300',
                                            'Dapat Ditunda' =>
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border-red-300',
                                        ];
                                        $priorityColor =
                                            $priorityColors[$item['priority_status']] ??
                                            'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 border-gray-300';
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $priorityColor }}">
                                        {{ $item['priority_status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button type="button" onclick="toggleDetail(event, {{ $index }})"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 text-sm font-medium">
                                        📊 Lihat Detail
                                    </button>
                                </td>
                            </tr>

                            <!-- Detail AHP Row -->
                            <tr id="detail-{{ $index }}" class="hidden bg-gray-50 dark:bg-gray-800">
                                <td colspan="8" class="px-6 py-4">
                                    <div class="space-y-4">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                            📊 Detail Perhitungan AHP - {{ $item['nama_barang'] }}
                                        </h4>

                                        <!-- Score Summary -->
                                        <div class="grid grid-cols-3 gap-4">
                                            <div class="bg-white dark:bg-gray-700 p-3 rounded-lg shadow-sm">
                                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                    Total Score
                                                </div>
                                                <div class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                                    {{ number_format($item['score'], 4) }}
                                                </div>
                                            </div>
                                            <div class="bg-white dark:bg-gray-700 p-3 rounded-lg shadow-sm">
                                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                    Average Score
                                                </div>
                                                <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                                    {{ number_format($item['avg_score'], 4) }}
                                                </div>
                                            </div>
                                            <div class="bg-white dark:bg-gray-700 p-3 rounded-lg shadow-sm">
                                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                    Grade
                                                </div>
                                                <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                                    {{ $item['grade'] }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Normalized Values -->
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            @foreach ($item['normalized_values'] ?? [] as $criteria => $value)
                                                <div class="bg-white dark:bg-gray-700 p-3 rounded-lg shadow-sm">
                                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                        {{ $criteria }} (Normalized)
                                                    </div>
                                                    <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                                        {{ number_format($value, 4) }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Weighted Scores -->
                                        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                                                🎯 Weighted Scores per Kriteria
                                            </h5>
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                                @foreach ($item['weighted_scores'] ?? [] as $criteria => $scoreData)
                                                    <div class="text-center">
                                                        <div
                                                            class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                            {{ $criteria }}
                                                        </div>
                                                        <div class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                                            {{ number_format($scoreData['weighted_score'], 4) }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            ({{ number_format($scoreData['weight'] * 100, 1) }}% ×
                                                            {{ number_format($scoreData['normalized_value'], 4) }})
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Raw Data -->
                                        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                                                📋 Data Mentah
                                            </h5>
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Harga:</span>
                                                    <span class="font-medium">Rp
                                                        {{ number_format($item['harga'], 0, ',', '.') }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Jumlah:</span>
                                                    <span class="font-medium">{{ $item['jumlah'] }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Stok:</span>
                                                    <span class="font-medium">{{ $item['stok'] }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Urgensi:</span>
                                                    <span
                                                        class="font-medium">{{ ucfirst($item['urgensi_prodi'] ?? 'N/A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- JavaScript for toggle detail -->
        <script>
            function toggleDetail(event, index) {
                // Prevent default behavior and stop propagation
                event.preventDefault();
                event.stopPropagation();

                const detailRow = document.getElementById(`detail-${index}`);
                if (detailRow) {
                    detailRow.classList.toggle('hidden');
                }

                // Return false to prevent any further event handling
                return false;
            }
        </script>
    @else
        <div class="text-center py-12">
            <div class="text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p class="text-lg font-medium">Tidak ada data ranking</p>
                <p class="text-sm">Belum ada pengajuan bahan ajar yang dihitung rankingnya.</p>
            </div>
        </div>
    @endif
</div>
