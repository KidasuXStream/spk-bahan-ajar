{{-- resources/views/filament/forms/components/ahp-ranking-preview.blade.php --}}

<div class="space-y-4">
    @if (!empty($rankings) && count($rankings) > 0)
        <!-- DEBUG INFO -->
        @if (config('app.debug'))
            <div
                class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2">
                    🔍 Debug Info
                </h4>
                <p class="text-xs text-yellow-700 dark:text-yellow-300">
                    Rankings count: {{ count($rankings) }}<br>
                    First item: {{ $rankings[0]['nama_barang'] ?? 'N/A' }}<br>
                    Sample score: {{ $rankings[0]['score'] ?? 'N/A' }}
                </p>
            </div>
        @endif

        <!-- CHART SECTION -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div
                class="px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20">
                <h3 class="text-md font-semibold text-purple-900 dark:text-purple-100">
                    📊 Visualisasi Ranking
                </h3>
                <p class="text-sm text-purple-700 dark:text-purple-300 mt-1">
                    Chart untuk memudahkan analisis ranking pengajuan bahan ajar
                </p>
            </div>

            <div class="p-4">
                <!-- Chart Container -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Chart 1: Bar Chart - Score Comparison -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                            🏆 Perbandingan Score Ranking
                        </h4>
                        <div class="h-64">
                            <canvas id="scoreChart"></canvas>
                        </div>
                    </div>

                    <!-- Chart 2: Pie Chart - Score Distribution -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                            🥧 Distribusi Score
                        </h4>
                        <div class="h-64">
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Chart 3: Line Chart - Price vs Score -->
                <div class="mt-6 bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                        💰 Hubungan Harga vs Score
                    </h4>
                    <div class="h-48">
                        <canvas id="priceScoreChart"></canvas>
                    </div>
                </div>

                <!-- Chart 4: Bar Chart - Price Comparison -->
                <div class="mt-6 bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                        💵 Perbandingan Harga
                    </h4>
                    <div class="h-48">
                        <canvas id="priceChart"></canvas>
                    </div>
                </div>

                <!-- Chart 5: Radar Chart - Criteria Breakdown -->
                <div class="mt-6 bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                        🎯 Breakdown Kriteria (Top 3)
                    </h4>
                    <div class="h-64">
                        <canvas id="radarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- RANKING LIST SECTION -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div
                class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
                <h3 class="text-md font-semibold text-blue-900 dark:text-blue-100">
                    🏆 Preview Ranking Pengajuan (Top {{ count($rankings) }})
                </h3>
                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                    Ranking berdasarkan perhitungan AHP dengan bobot kriteria yang telah ditentukan
                </p>
            </div>

            <div class="p-4">
                <!-- Ranking Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Ranking
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
                                    Score AHP
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Harga
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Jumlah
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Stok
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
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
                                            {{ number_format($item['score'], 4) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ number_format($item['jumlah']) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ number_format($item['stok'] ?? 0) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $item['score'] > 0.5
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                : ($item['score'] > 0.3
                                                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                            {{ $item['score'] > 0.5 ? 'Tinggi' : ($item['score'] > 0.3 ? 'Sedang' : 'Rendah') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Total Row -->
                            <tr class="bg-gray-50 dark:bg-gray-800 font-semibold">
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    Total
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ count($rankings) }} item
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    -
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    -
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                    {{ number_format(collect($rankings)->sum('score'), 4) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    -
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format(collect($rankings)->sum('jumlah')) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format(collect($rankings)->sum('stok')) }}
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

            <div class="px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border-t border-blue-200 dark:border-blue-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            💡 Ranking berdasarkan perhitungan AHP
                        </p>
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ count($rankings) }} item
                        </span>
                    </div>
                    <div class="flex space-x-2">
                        <button type="button" onclick="window.open('/admin/pengajuan-bahan-ajars', '_blank')"
                            class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                            Lihat Detail →
                        </button>
                        <button type="button" onclick="exportRankings()"
                            class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                            Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart.js Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Debug: Check if data is available
            console.log('🔍 Debug: Checking ranking data...');
            let rankings = @json($rankings ?? []);
            console.log('📊 Rankings data:', rankings);

            // If no rankings data, use sample data for testing
            if (!rankings || rankings.length === 0) {
                console.log('⚠️ No ranking data, using sample data for testing...');
                rankings = [{
                        nama_barang: 'HDD 1 TB External',
                        pengaju: 'Admin N/A',
                        prodi: 'TRPL',
                        score: 4.5299,
                        harga: 800000,
                        jumlah: 15,
                        stok: 0
                    },
                    {
                        nama_barang: 'HDD 1 TB External',
                        pengaju: 'Test Kaprodi TRPL',
                        prodi: 'TRPL',
                        score: 3.0199,
                        harga: 830000,
                        jumlah: 10,
                        stok: 0
                    }
                ];
            }

            // Wait for Chart.js to load and DOM to be ready
            function initializeCharts() {
                console.log('🚀 Initializing charts...');

                if (typeof Chart === 'undefined') {
                    console.error('❌ Chart.js not loaded');
                    setTimeout(initializeCharts, 1000);
                    return;
                }

                if (!rankings || rankings.length === 0) {
                    console.log('⚠️ No ranking data available');
                    return;
                }

                console.log('✅ Chart.js loaded, creating charts...');

                // Chart 1: Bar Chart - Score Comparison
                const scoreCtx = document.getElementById('scoreChart');
                if (scoreCtx) {
                    console.log('📈 Creating score chart...');
                    new Chart(scoreCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: rankings.map(item => item.nama_barang.substring(0, 15) + '...'),
                            datasets: [{
                                label: 'Score AHP',
                                data: rankings.map(item => item.score),
                                backgroundColor: [
                                    'rgba(255, 206, 86, 0.8)', // Gold
                                    'rgba(192, 192, 192, 0.8)', // Silver
                                    'rgba(205, 127, 50, 0.8)', // Bronze
                                    ...Array(Math.max(0, rankings.length - 3)).fill(
                                        'rgba(54, 162, 235, 0.8)') // Blue
                                ],
                                borderColor: [
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(192, 192, 192, 1)',
                                    'rgba(205, 127, 50, 1)',
                                    ...Array(Math.max(0, rankings.length - 3)).fill('rgba(54, 162, 235, 1)')
                                ],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Score AHP'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Nama Barang'
                                    }
                                }
                            }
                        }
                    });
                } else {
                    console.error('❌ Score chart canvas not found');
                }

                // Chart 2: Pie Chart - Score Distribution
                const pieCtx = document.getElementById('pieChart');
                if (pieCtx) {
                    console.log('🥧 Creating pie chart...');
                    new Chart(pieCtx.getContext('2d'), {
                        type: 'pie',
                        data: {
                            labels: rankings.map(item => item.nama_barang.substring(0, 12) + '...'),
                            datasets: [{
                                data: rankings.map(item => item.score),
                                backgroundColor: [
                                    '#FFD700', // Gold
                                    '#C0C0C0', // Silver
                                    '#CD7F32', // Bronze
                                    '#36A2EB', // Blue
                                    '#FF6384', // Pink
                                    '#4BC0C0', // Teal
                                    '#9966FF', // Purple
                                    '#FF9F40' // Orange
                                ],
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 10,
                                        usePointStyle: true
                                    }
                                }
                            }
                        }
                    });
                } else {
                    console.error('❌ Pie chart canvas not found');
                }

                // Chart 3: Line Chart - Price vs Score
                const priceScoreCtx = document.getElementById('priceScoreChart');
                if (priceScoreCtx) {
                    console.log('💰 Creating price vs score chart...');
                    new Chart(priceScoreCtx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: rankings.map((item, index) => `Rank ${index + 1}`),
                            datasets: [{
                                label: 'Score AHP',
                                data: rankings.map(item => item.score),
                                borderColor: 'rgba(54, 162, 235, 1)',
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4
                            }, {
                                label: 'Harga (normalized)',
                                data: rankings.map(item => (1 / item.harga) * 1000000), // Normalize price
                                borderColor: 'rgba(255, 99, 132, 1)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderWidth: 3,
                                fill: false,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Nilai'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Ranking'
                                    }
                                }
                            }
                        }
                    });
                } else {
                    console.error('❌ Price vs score chart canvas not found');
                }

                // Chart 4: Bar Chart - Price Comparison
                const priceChartCtx = document.getElementById('priceChart');
                if (priceChartCtx) {
                    console.log('💵 Creating price chart...');
                    new Chart(priceChartCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: rankings.map(item => item.nama_barang.substring(0, 15) + '...'),
                            datasets: [{
                                label: 'Harga (Rp)',
                                data: rankings.map(item => item.harga),
                                backgroundColor: 'rgba(54, 162, 235, 0.8)', // Blue
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Harga (Rp)'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Nama Barang'
                                    }
                                }
                            }
                        }
                    });
                } else {
                    console.error('❌ Price chart canvas not found');
                }

                // Chart 5: Radar Chart - Criteria Breakdown (Top 3)
                const radarCtx = document.getElementById('radarChart');
                if (radarCtx) {
                    console.log('🎯 Creating radar chart...');
                    // Prepare data for radar chart (criteria breakdown)
                    const criteriaLabels = ['Harga', 'Jumlah', 'Stok', 'Urgensi'];
                    const top3Data = rankings.slice(0, 3);

                    const radarDatasets = top3Data.map((item, index) => {
                        const colors = ['#FF6384', '#36A2EB', '#FFCE56'];
                        return {
                            label: `${item.nama_barang.substring(0, 15)}...`,
                            data: [
                                (1 / item.harga) * 1000000, // Normalized price
                                item.jumlah,
                                item.stok || 0,
                                item.score // Overall score
                            ],
                            backgroundColor: colors[index] + '20',
                            borderColor: colors[index],
                            borderWidth: 2,
                            pointBackgroundColor: colors[index],
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: colors[index]
                        };
                    });

                    new Chart(radarCtx.getContext('2d'), {
                        type: 'radar',
                        data: {
                            labels: criteriaLabels,
                            datasets: radarDatasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 15
                                    }
                                }
                            },
                            scales: {
                                r: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                } else {
                    console.error('❌ Radar chart canvas not found');
                }

                console.log('✅ All charts initialized successfully!');
            }

            // Initialize charts when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeCharts);
            } else {
                // DOM is already ready
                setTimeout(initializeCharts, 100);
            }

            // Export function
            function exportRankings() {
                let csvContent = "data:text/csv;charset=utf-8,";

                // Header
                csvContent += "Ranking,Nama Barang,Pengaju,Prodi,Score,Harga,Jumlah,Stok\n";

                // Data
                rankings.forEach((item, index) => {
                    const row = [
                        index + 1,
                        `"${item.nama_barang}"`,
                        `"${item.pengaju}"`,
                        `"${item.prodi || 'N/A'}"`,
                        item.score,
                        item.harga,
                        item.jumlah,
                        item.stok || 0
                    ].join(',');
                    csvContent += row + '\n';
                });

                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "ranking_bahan_ajar.csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        </script>
    @else
        <div
            class="text-center py-8 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="text-gray-500 dark:text-gray-400 mb-2 text-4xl">📊</div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum Ada Pengajuan</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">
                Belum ada pengajuan bahan ajar untuk session ini atau belum ada perhitungan AHP
            </p>
            <div class="flex justify-center space-x-2">
                <button type="button" onclick="window.open('/admin/pengajuan-bahan-ajars/create', '_blank')"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                    Tambah Pengajuan
                </button>
                <button type="button" onclick="window.open('/admin/ahp-analysis', '_blank')"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    Hitung AHP
                </button>
            </div>
        </div>
    @endif
</div>
