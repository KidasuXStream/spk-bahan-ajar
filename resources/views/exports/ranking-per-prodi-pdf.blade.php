<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Ranking {{ $prodi ? strtoupper($prodi) : 'Semua Prodi' }} - {{ $session->tahun_ajaran }}
        {{ $session->semester }}</title>
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
    </style>
</head>

<body>
    <div class="header">
        <div class="title">RANKING BAHAN AJAR</div>
        <div class="subtitle">{{ $prodi ? strtoupper($prodi) : 'Semua Program Studi' }}</div>
        <div class="subtitle">{{ $session->tahun_ajaran }} - {{ $session->semester }}</div>
    </div>

    <div class="info">
        <div class="info-row">
            <strong>Tanggal Export:</strong> {{ $exported_at }}
        </div>
        <div class="info-row">
            <strong>Program Studi:</strong> {{ $prodi ? strtoupper($prodi) : 'Semua Prodi' }}
        </div>
        <div class="info-row">
            <strong>Total Pengajuan:</strong> {{ count($rankings) }} item
        </div>
        <div class="info-row">
            <strong>Total Budget:</strong> Rp {{ number_format(collect($rankings)->sum('total_harga'), 0, ',', '.') }}
        </div>
    </div>

    @if ($prodi)
        {{-- Single Prodi --}}
        <table class="table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Nama Barang</th>
                    <th>Pengaju</th>
                    <th>Harga Satuan</th>
                    <th>Jumlah</th>
                    <th>Total Harga</th>
                    <th>Score AHP</th>
                    <th>Urgensi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rankings as $index => $item)
                    <tr>
                        <td class="rank">{{ $index + 1 }}</td>
                        <td>{{ $item['nama_barang'] }}</td>
                        <td>{{ $item['pengaju'] }}</td>
                        <td>Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                        <td>{{ $item['jumlah'] }}</td>
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
    @else
        {{-- All Prodi Grouped --}}
        @php
            $groupedRankings = collect($rankings)->groupBy('prodi');
        @endphp

        @foreach ($groupedRankings as $prodiName => $items)
            <div class="prodi-header">
                PROGRAM STUDI {{ strtoupper($prodiName) }}
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Nama Barang</th>
                        <th>Pengaju</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                        <th>Total Harga</th>
                        <th>Score AHP</th>
                        <th>Urgensi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $item)
                        <tr>
                            <td class="rank">{{ $index + 1 }}</td>
                            <td>{{ $item['nama_barang'] }}</td>
                            <td>{{ $item['pengaju'] }}</td>
                            <td>Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                            <td>{{ $item['jumlah'] }}</td>
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
    @endif

    <div class="footer">
        <p>Dokumen ini di-generate secara otomatis oleh sistem SPK Bahan Ajar</p>
        <p>Export dilakukan pada: {{ $exported_at }}</p>
    </div>
</body>

</html>
