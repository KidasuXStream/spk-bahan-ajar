# Sistem SPK Pemilihan Bahan Ajar Praktikum dengan Metode AHP

## Overview

Sistem ini merupakan implementasi Sistem Pendukung Keputusan (SPK) untuk pemilihan bahan ajar praktikum menggunakan metode Analytical Hierarchy Process (AHP) dengan framework Laravel dan Filament.

## Fitur Utama

### 1. Manajemen Kriteria
- ✅ Tambah, edit, dan hapus kriteria
- ✅ Aktivasi/deaktivasi kriteria
- ✅ Validasi kriteria minimal (2 kriteria)

### 2. Perbandingan Kriteria (AHP Matrix)
- ✅ Input matriks perbandingan berpasangan
- ✅ Validasi konsistensi (CI, CR)
- ✅ Perhitungan geometric mean
- ✅ Priority vector calculation
- ✅ Real-time calculation preview

### 3. Perhitungan AHP
- ✅ Perhitungan bobot kriteria
- ✅ Consistency Index (CI)
- ✅ Consistency Ratio (CR)
- ✅ Lambda Max calculation
- ✅ Validasi konsistensi (CR < 0.1)

### 4. Ranking Bahan Ajar
- ✅ Normalisasi nilai kriteria
- ✅ Perhitungan skor tertimbang
- ✅ Ranking berdasarkan skor AHP
- ✅ Export hasil ranking

## Struktur Database

### Tabel Utama

1. **kriterias** - Kriteria penilaian
   - `id` - Primary key
   - `kode_kriteria` - Kode kriteria
   - `nama_kriteria` - Nama kriteria
   - `deskripsi` - Deskripsi kriteria
   - `is_active` - Status aktif

2. **ahp_sessions** - Session AHP
   - `id` - Primary key
   - `tahun_ajaran` - Tahun ajaran
   - `semester` - Semester

3. **ahp_comparisons** - Perbandingan kriteria
   - `id` - Primary key
   - `ahp_session_id` - Foreign key ke ahp_sessions
   - `kriteria_1_id` - Kriteria pertama
   - `kriteria_2_id` - Kriteria kedua
   - `nilai` - Nilai perbandingan

4. **ahp_results** - Hasil perhitungan AHP
   - `id` - Primary key
   - `ahp_session_id` - Foreign key ke ahp_sessions
   - `kriteria_id` - Foreign key ke kriterias
   - `bobot` - Bobot kriteria

5. **pengajuan_bahan_ajars** - Pengajuan bahan ajar
   - `id` - Primary key
   - `user_id` - Foreign key ke users
   - `nama_barang` - Nama bahan ajar
   - `harga_satuan` - Harga satuan
   - `jumlah` - Jumlah
   - `stok` - Stok tersedia
   - `urgensi_prodi` - Urgensi prodi
   - `urgensi_institusi` - Urgensi institusi
   - `ahp_session_id` - Foreign key ke ahp_sessions
   - `ahp_score` - Skor AHP
   - `ranking_position` - Posisi ranking

## Implementasi AHP

### 1. Perhitungan Geometric Mean

```php
private static function calculateGeometricMeanWeights(array $matrix, $criteria): array
{
    $n = count($matrix);
    $weights = [];

    // Hitung geometric mean untuk setiap baris
    for ($i = 0; $i < $n; $i++) {
        $product = 1.0;
        $validValues = 0;
        
        for ($j = 0; $j < $n; $j++) {
            if ($matrix[$i][$j] > 0) {
                $product *= $matrix[$i][$j];
                $validValues++;
            }
        }
        
        if ($validValues > 0) {
            $geometricMean = pow($product, 1.0 / $validValues);
            $weights[$i] = $geometricMean;
        } else {
            $weights[$i] = 1.0;
        }
    }

    // Normalisasi bobot
    $totalWeight = array_sum($weights);
    $normalizedWeights = [];

    if ($totalWeight > 0) {
        for ($i = 0; $i < $n; $i++) {
            $weight = $weights[$i] / $totalWeight;
            $normalizedWeights[$criteria[$i]->nama_kriteria] = $weight;
        }
    } else {
        // Equal weights jika total 0
        $equalWeight = 1.0 / $n;
        for ($i = 0; $i < $n; $i++) {
            $normalizedWeights[$criteria[$i]->nama_kriteria] = $equalWeight;
        }
    }

    return $normalizedWeights;
}
```

### 2. Perhitungan Consistency Metrics

```php
private static function calculateConsistencyMetrics(array $matrix, array $weights, int $n): array
{
    if ($n < 2) {
        return [
            'lambda_max' => $n,
            'ci' => 0,
            'cr' => 0
        ];
    }

    // Hitung lambda max
    $lambdaMax = 0;
    $validRows = 0;
    
    for ($i = 0; $i < $n; $i++) {
        $sum = 0;
        $rowValid = false;
        
        for ($j = 0; $j < $n; $j++) {
            if ($matrix[$i][$j] > 0 && isset($weights[$j]) && $weights[$j] > 0) {
                $sum += $matrix[$i][$j] * $weights[$j];
                $rowValid = true;
            }
        }
        
        if ($rowValid && isset($weights[$i]) && $weights[$i] > 0) {
            $lambdaMax += $sum / $weights[$i];
            $validRows++;
        }
    }
    
    $lambdaMax = $validRows > 0 ? $lambdaMax / $validRows : $n;

    // Hitung CI
    $ci = ($lambdaMax - $n) / ($n - 1);

    // Random Index (Saaty's Random Index)
    $randomIndex = [
        0 => 0, 1 => 0, 2 => 0, 3 => 0.52, 4 => 0.89, 5 => 1.11,
        6 => 1.25, 7 => 1.35, 8 => 1.40, 9 => 1.45, 10 => 1.49,
        11 => 1.52, 12 => 1.54, 13 => 1.56, 14 => 1.58, 15 => 1.59
    ];
    
    $ri = $randomIndex[$n] ?? 1.59;

    // Hitung CR
    $cr = $ri > 0 ? $ci / $ri : 0;

    return [
        'lambda_max' => $lambdaMax,
        'ci' => $ci,
        'cr' => $cr
    ];
}
```

### 3. Normalisasi Nilai Kriteria

```php
// Normalisasi harga (cost criteria - lower is better)
private function normalizeHarga($submission): float
{
    $maxHarga = PengajuanBahanAjar::where('ahp_session_id', $submission->ahp_session_id)->max('harga_satuan');
    $minHarga = PengajuanBahanAjar::where('ahp_session_id', $submission->ahp_session_id)->min('harga_satuan');

    if ($maxHarga == $minHarga) return 1.0;

    return ($maxHarga - $submission->harga_satuan) / ($maxHarga - $minHarga);
}

// Normalisasi jumlah (benefit criteria - higher is better)
private function normalizeJumlah($submission): float
{
    $maxJumlah = PengajuanBahanAjar::where('ahp_session_id', $submission->ahp_session_id)->max('jumlah');
    $minJumlah = PengajuanBahanAjar::where('ahp_session_id', $submission->ahp_session_id)->min('jumlah');

    if ($maxJumlah == $minJumlah) return 1.0;

    return ($submission->jumlah - $minJumlah) / ($maxJumlah - $minJumlah);
}
```

## API Endpoints

### AHP Management

1. **POST** `/api/ahp/calculate-weights`
   - Menghitung bobot AHP untuk session tertentu
   - Body: `{"session_id": 1}`

2. **POST** `/api/ahp/generate-results`
   - Generate hasil AHP lengkap termasuk ranking
   - Body: `{"session_id": 1}`

3. **GET** `/api/ahp/results/{session_id}`
   - Mendapatkan hasil AHP untuk session tertentu

4. **GET** `/api/ahp/rankings/{session_id}`
   - Mendapatkan ranking bahan ajar untuk session tertentu

5. **GET** `/api/ahp/validate-matrix/{session_id}`
   - Validasi kelengkapan matriks perbandingan

6. **POST** `/api/ahp/save-matrix`
   - Menyimpan matriks perbandingan
   - Body: `{"session_id": 1, "matrix_data": {...}}`

7. **GET** `/api/ahp/matrix/{session_id}`
   - Mendapatkan matriks perbandingan untuk session tertentu

8. **GET** `/api/ahp/statistics/{session_id}`
   - Mendapatkan statistik session AHP

## Cara Penggunaan

### 1. Setup Kriteria
1. Buka menu "Kriteria" di admin panel
2. Tambah kriteria yang diperlukan (minimal 2)
3. Pastikan kriteria aktif

### 2. Input Perbandingan Kriteria
1. Buka menu "AHP - Analisis Kriteria"
2. Pilih session AHP
3. Isi matriks perbandingan berpasangan
4. Sistem akan menghitung CI, CR, dan priority vector secara real-time

### 3. Generate Hasil AHP
1. Setelah matriks lengkap, klik "Generate Results"
2. Sistem akan menghitung bobot dan ranking
3. Hasil dapat dilihat di preview ranking

### 4. Lihat Ranking
1. Buka menu "Pengajuan Bahan Ajar"
2. Ranking akan ditampilkan berdasarkan skor AHP
3. Dapat di-export ke CSV

## Validasi dan Error Handling

### 1. Validasi Matriks
- Minimal 2 kriteria aktif
- Semua perbandingan harus diisi
- Nilai perbandingan > 0
- Consistency Ratio < 0.1

### 2. Error Handling
- Matrix incomplete
- Invalid comparison values
- Calculation errors
- Database errors

## Perbaikan yang Telah Dilakukan

### 1. Perhitungan AHP
- ✅ Perbaikan perhitungan geometric mean
- ✅ Validasi nilai matrix yang lebih robust
- ✅ Penanganan error yang lebih baik
- ✅ Random Index yang lebih lengkap

### 2. Normalisasi Kriteria
- ✅ Normalisasi harga (cost criteria)
- ✅ Normalisasi jumlah (benefit criteria)
- ✅ Normalisasi stok (cost criteria)
- ✅ Normalisasi urgensi (benefit criteria)

### 3. UI/UX Improvements
- ✅ Tampilan ranking yang lebih informatif
- ✅ Export functionality
- ✅ Real-time calculation preview
- ✅ Better error messages

### 4. Code Quality
- ✅ Service layer untuk business logic
- ✅ Controller untuk API endpoints
- ✅ Proper error handling
- ✅ Comprehensive logging

## Troubleshooting

### 1. Matrix Tidak Lengkap
- Pastikan semua perbandingan berpasangan diisi
- Minimal 2 kriteria aktif
- Periksa nilai perbandingan (tidak boleh 0 atau negatif)

### 2. Consistency Ratio > 0.1
- Review perbandingan yang ekstrem
- Pertimbangkan untuk mengubah nilai perbandingan
- Pastikan konsistensi logis antar perbandingan

### 3. Ranking Tidak Muncul
- Pastikan sudah ada pengajuan bahan ajar
- Periksa apakah AHP sudah dihitung
- Pastikan session AHP sudah dipilih

## Dependencies

- Laravel 10+
- Filament 3+
- PHP 8.1+
- MySQL/PostgreSQL

## License

This project is licensed under the MIT License.
