<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Summary Per Prodi - {{ $session->tahun_ajaran }} {{ $session->semester }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
        }

        .info {
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .score {
            font-weight: bold;
            color: #2563eb;
        }

        .rank {
            font-weight: bold;
            color: #dc2626;
        }

        .prodi-header {
            background-color: #e5e7eb;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            margin: 20px 0 10px 0;
            border-radius: 5px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .summary-item {
            background-color: #f8fafc;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
        }

        .summary-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">SUMMARY PER PROGRAM STUDI</div>
        <div class="subtitle">Berdasarkan Hasil Perangkingan AHP</div>
        <div class="subtitle">{{ $session->tahun_ajaran }} - {{ $session->semester }}</div>
    </div>

    <div class="info">
        <div class="info-row">
            <strong>Tanggal Export:</strong> {{ $exported_at }}
        </div>
        <div class="info-row">
            <strong>Total Program Studi:</strong> {{ count($summary) }} prodi
        </div>
        <div class="info-row">
            <strong>Total Item:</strong> {{ collect($summary)->sum('total_items') }} item
        </div>
        <div class="info-row">
            <strong>Total Budget:</strong> Rp {{ number_format(collect($summary)->sum('total_budget'), 0, ',', '.') }}
        </div>
    </div>

    @foreach ($summary as $prodiSummary)
        <div class="prodi-header">
            PROGRAM STUDI {{ strtoupper($prodiSummary['prodi']) }}
        </div>

        <!-- Summary Stats -->
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $prodiSummary['total_items'] }}</div>
                <div class="summary-label">Total Item</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">Rp {{ number_format($prodiSummary['total_budget'] / 1000000, 1) }}M</div>
                <div class="summary-label">Total Budget</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($prodiSummary['avg_score'], 4) }}</div>
                <div class="summary-label">Rata-rata Score</div>
            </div>
        </div>

        <!-- Top Items Table -->
        <table class="table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Nama Barang</th>
                    <th>Pengaju</th>
                    <th>Total Harga</th>
                    <th>Score AHP</th>
                    <th>Urgensi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($prodiSummary['top_items'] as $index => $item)
                    <tr>
                        <td class="rank">{{ $index + 1 }}</td>
                        <td>{{ $item['nama_barang'] }}</td>
                        <td>{{ $item['pengaju'] }}</td>
                        <td>Rp {{ number_format($item['total_harga'], 0, ',', '.') }}</td>
                        <td class="score">{{ number_format($item['score'], 4) }}</td>
                        <td>
                            <strong>Prodi:</strong> {{ ucfirst($item['urgensi_prodi'] ?? '-') }}<br>
                            <strong>Institusi:</strong> {{ ucfirst($item['urgensi_institusi'] ?? '-') }}<br>
                            <strong>Final:</strong> {{ ucfirst($item['urgensi_tim_pengadaan'] ?? '-') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <!-- Overall Summary -->
    <div style="margin-top: 30px; padding: 15px; background-color: #f1f5f9; border-radius: 5px;">
        <h3 style="margin: 0 0 15px 0; color: #1e293b;">Ringkasan Keseluruhan</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <h4 style="margin: 0 0 10px 0; color: #475569;">Statistik Budget</h4>
                @php
                    $totalBudget = collect($summary)->sum('total_budget');
                    $avgBudget = collect($summary)->avg('total_budget');
                    $maxBudget = collect($summary)->max('total_budget');
                    $minBudget = collect($summary)->min('total_budget');
                @endphp
                <p><strong>Total Budget:</strong> Rp {{ number_format($totalBudget, 0, ',', '.') }}</p>
                <p><strong>Rata-rata per Prodi:</strong> Rp {{ number_format($avgBudget, 0, ',', '.') }}</p>
                <p><strong>Budget Tertinggi:</strong> Rp {{ number_format($maxBudget, 0, ',', '.') }}</p>
                <p><strong>Budget Terendah:</strong> Rp {{ number_format($minBudget, 0, ',', '.') }}</p>
            </div>

            <div>
                <h4 style="margin: 0 0 10px 0; color: #475569;">Statistik Item</h4>
                @php
                    $totalItems = collect($summary)->sum('total_items');
                    $avgItems = collect($summary)->avg('total_items');
                    $maxItems = collect($summary)->max('total_items');
                    $minItems = collect($summary)->min('total_items');
                @endphp
                <p><strong>Total Item:</strong> {{ $totalItems }} item</p>
                <p><strong>Rata-rata per Prodi:</strong> {{ number_format($avgItems, 1) }} item</p>
                <p><strong>Item Terbanyak:</strong> {{ $maxItems }} item</p>
                <p><strong>Item Tersedikit:</strong> {{ $minItems }} item</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Dokumen ini di-generate secara otomatis oleh sistem SPK Bahan Ajar</p>
        <p>Export dilakukan pada: {{ $exported_at }}</p>
        <p>Summary ini memberikan gambaran perbandingan antar program studi</p>
    </div>
</body>

</html>
