{{-- resources/views/filament/forms/components/ahp-results.blade.php --}}

<div class="space-y-6">
    @if ($results && !empty($results['weights']))
        <!-- AHP RESULTS TABLE -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div
                class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
                <h3 class="text-md font-semibold text-blue-900 dark:text-blue-100">
                    📊 Hasil Perhitungan AHP
                </h3>
                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                    Bobot kriteria dan nilai konsistensi matrix
                </p>
            </div>

            <div class="p-4">
                <!-- Results Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Kriteria
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Priority Vector (Bobot)
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Persentase
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @php
                                $sortedWeights = collect($results['weights'])->sortByDesc(function ($weight) {
                                    return $weight;
                                });
                                $totalWeight = $sortedWeights->sum();
                            @endphp

                            @foreach ($sortedWeights as $criteriaName => $weight)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div
                                                    class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                                        {{ $loop->iteration }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $criteriaName }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ number_format($weight, 4) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full"
                                                    style="width: {{ ($weight / $totalWeight) * 100 }}%"></div>
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ number_format(($weight / $totalWeight) * 100, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($loop->iteration === 1)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                🥇 Terpenting
                                            </span>
                                        @elseif ($loop->iteration === 2)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                                🥈 Kedua
                                            </span>
                                        @elseif ($loop->iteration === 3)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                🥉 Ketiga
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ $loop->iteration }}th
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Total Row -->
                            <tr class="bg-gray-50 dark:bg-gray-800 font-semibold">
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    Total
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                    {{ number_format($totalWeight, 4) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    100.0%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        ✅ Valid
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- CONSISTENCY METRICS -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div
                class="px-4 py-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20">
                <h3 class="text-md font-semibold text-green-900 dark:text-green-100">
                    🎯 Metrik Konsistensi
                </h3>
                <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                    Indikator konsistensi matrix perbandingan
                </p>
            </div>

            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- CI -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">CI</span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    Consistency Index
                                </p>
                                <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                    {{ number_format($results['ci'] ?? 0, 4) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- CR -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="h-8 w-8 rounded-full {{ ($results['cr'] ?? 1) < 0.1 ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} flex items-center justify-center">
                                    <span
                                        class="text-sm font-medium {{ ($results['cr'] ?? 1) < 0.1 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">CR</span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    Consistency Ratio
                                </p>
                                <p
                                    class="text-lg font-bold {{ ($results['cr'] ?? 1) < 0.1 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ number_format($results['cr'] ?? 0, 4) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Lambda Max -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="h-8 w-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                    <span class="text-sm font-medium text-purple-600 dark:text-purple-400">λ</span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    Lambda Max
                                </p>
                                <p class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                    {{ number_format($results['lambda_max'] ?? 0, 4) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Consistency Status -->
                <div
                    class="mt-4 p-4 {{ $results['consistent'] ?? false ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' }} rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if ($results['consistent'] ?? false)
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="ml-3">
                            <h3
                                class="text-sm font-medium {{ $results['consistent'] ?? false ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }}">
                                {{ $results['consistent'] ?? false ? 'Matrix Konsisten' : 'Matrix Tidak Konsisten' }}
                            </h3>
                            <div
                                class="mt-2 text-sm {{ $results['consistent'] ?? false ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                <p>
                                    {{ $results['consistent'] ?? false
                                        ? 'Matrix perbandingan konsisten (CR < 0.1). Hasil perhitungan AHP dapat dipercaya.'
                                        : 'Matrix perbandingan tidak konsisten (CR ≥ 0.1). Silakan periksa kembali nilai perbandingan.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="text-gray-500 dark:text-gray-400 mb-2 text-4xl">📊</div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum Ada Hasil AHP</h3>
            <p class="text-gray-500 dark:text-gray-400">
                Hasil perhitungan AHP akan muncul setelah matrix perbandingan diisi dan dihitung
            </p>
        </div>
    @endif
</div>
