<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Daftar Pengadaan - {{ $session->tahun_ajaran }} {{ $session->semester }}</title>
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

        .urgency-high {
            color: #dc2626;
            font-weight: bold;
        }

        .urgency-medium {
            color: #f59e0b;
            font-weight: bold;
        }

        .urgency-low {
            color: #059669;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">DAFTAR PENGADAAN BAHAN AJAR</div>
        <div class="subtitle">Berdasarkan Hasil Perangkingan AHP</div>
        <div class="subtitle">{{ $session->tahun_ajaran }} - {{ $session->semester }}</div>
    </div>

    <div class="info">
        <div class="info-row">
            <strong>Tanggal Export:</strong> {{ $exported_at }}
        </div>
        <div class="info-row">
            <strong>Total Item:</strong> {{ count($items) }} item
        </div>
        <div class="info-row">
            <strong>Total Budget:</strong> Rp {{ number_format(collect($items)->sum('total_harga'), 0, ',', '.') }}
        </div>
        <div class="info-row">
            <strong>Rata-rata Score:</strong> {{ number_format(collect($items)->avg('score'), 4) }}
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Nama Barang</th>
                <th>Prodi</th>
                <th>Pengaju</th>
                <th>Harga Satuan</th>
                <th>Jumlah</th>
                <th>Total Harga</th>
                <th>Score AHP</th>
                <th>Urgensi Final</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $index => $item)
                <tr>
                    <td class="rank">{{ $index + 1 }}</td>
                    <td>{{ $item['nama_barang'] }}</td>
                    <td>{{ strtoupper($item['prodi']) }}</td>
                    <td>{{ $item['pengaju'] }}</td>
                    <td>Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                    <td>{{ $item['jumlah'] }}</td>
                    <td>Rp {{ number_format($item['total_harga'], 0, ',', '.') }}</td>
                    <td class="score">{{ number_format($item['score'], 4) }}</td>
                    <td>
                        @php
                            $urgencyClass = match ($item['urgensi_tim_pengadaan'] ?? '') {
                                'tinggi' => 'urgency-high',
                                'sedang' => 'urgency-medium',
                                'rendah' => 'urgency-low',
                                default => '',
                            };
                        @endphp
                        <span class="{{ $urgencyClass }}">
                            {{ ucfirst($item['urgensi_tim_pengadaan'] ?? '-') }}
                        </span>
                    </td>
                    <td>
                        @if (!empty($item['catatan_tim_pengadaan']))
                            {{ Str::limit($item['catatan_tim_pengadaan'], 50) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Section -->
    <div style="margin-top: 30px; padding: 15px; background-color: #f8fafc; border-radius: 5px;">
        <h3 style="margin: 0 0 15px 0; color: #1e293b;">Ringkasan Pengadaan</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <h4 style="margin: 0 0 10px 0; color: #475569;">Distribusi Urgensi</h4>
                @php
                    $urgencyCounts = collect($items)->countBy('urgensi_tim_pengadaan');
                @endphp
                <p><strong>Tinggi:</strong> {{ $urgencyCounts['tinggi'] ?? 0 }} item</p>
                <p><strong>Sedang:</strong> {{ $urgencyCounts['sedang'] ?? 0 }} item</p>
                <p><strong>Rendah:</strong> {{ $urgencyCounts['rendah'] ?? 0 }} item</p>
            </div>

            <div>
                <h4 style="margin: 0 0 10px 0; color: #475569;">Distribusi Prodi</h4>
                @php
                    $prodiCounts = collect($items)->countBy('prodi');
                @endphp
                @foreach ($prodiCounts->take(5) as $prodi => $count)
                    <p><strong>{{ strtoupper($prodi) }}:</strong> {{ $count }} item</p>
                @endforeach
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Dokumen ini di-generate secara otomatis oleh sistem SPK Bahan Ajar</p>
        <p>Export dilakukan pada: {{ $exported_at }}</p>
        <p>Daftar ini berisi item yang telah diproses dan siap untuk pengadaan</p>
    </div>
</body>

</html>
