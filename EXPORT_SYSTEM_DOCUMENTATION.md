# 📊 Sistem Ekspor Tim Pengadaan - SPK Bahan Ajar

## 🎯 Overview

Sistem ekspor tim pengadaan menyediakan fitur lengkap untuk mengekspor data pengadaan bahan ajar dalam berbagai format dan kategori. Semua data diekspor dalam format Excel (.xlsx) dengan struktur yang terorganisir dan mudah dibaca.

## 🚀 Fitur Ekspor yang Tersedia

### 1. 📋 Ekspor Data Pengajuan Barang Per Prodi
**Endpoint:** `/export/pengajuan-per-prodi`  
**File:** `PengajuanPerProdiExport.php`

**Deskripsi:** Mengekspor semua pengajuan barang dari semua prodi dalam 1 file Excel dengan sheet terpisah untuk setiap prodi.

**Fitur:**
- 1 file Excel dengan multiple sheet
- Sheet per prodi: TRPL, Mesin, Elektro, Mekatronika
- Data lengkap: nama barang, spesifikasi, vendor, harga, urgensi, status
- Format yang rapi dengan styling dan border

**Konten Sheet:**
- No, Nama Barang, Spesifikasi, Vendor
- Jumlah, Harga Satuan, Total Harga
- Masa Pakai, Stok, Urgensi Prodi & Institusi
- Status, Pengaju, NIDN, NIP
- Tahun Ajaran, Semester, Tanggal Pengajuan
- Catatan Pengaju & Tim Pengadaan

---

### 2. 📊 Ekspor Data Barang yang Sudah Dirangking dengan AHP Per Prodi
**Endpoint:** `/export/ranking-ahp-per-prodi`  
**File:** `RankingPerProdiAdvancedExport.php`

**Deskripsi:** Mengekspor data barang yang sudah diproses dengan algoritma AHP dan dirangking berdasarkan skor prioritas.

**Fitur:**
- Ranking berdasarkan skor AHP
- Highlight top 3 ranking dengan warna khusus
- Data per prodi dalam sheet terpisah
- Informasi lengkap hasil perhitungan AHP

**Konten Sheet:**
- Ranking, Nama Barang, Spesifikasi, Vendor
- Jumlah, Harga Satuan, Total Harga
- Stok, Masa Pakai, AHP Score
- Urgensi Prodi & Institusi
- Pengaju, NIDN, NIP
- Tahun Ajaran, Semester, Tanggal Pengajuan
- Catatan Pengaju & Tim Pengadaan

**Highlight Khusus:**
- 🥇 Top 1: Background Gold
- 🥈 Top 2: Background Silver  
- 🥉 Top 3: Background Bronze

---

### 3. 🧮 Ekspor Hasil AHP
**Endpoint:** `/export/ahp-results/{sessionId}`  
**File:** `AHPResultsExport.php`

**Deskripsi:** Mengekspor hasil lengkap perhitungan AHP termasuk matrix perbandingan, bobot kriteria, dan ranking final.

**Fitur:**
- Multiple sheet dalam 1 file Excel
- Informasi session AHP
- Matrix perbandingan kriteria
- Hasil perhitungan bobot
- Ranking final dengan prioritas

**Sheet yang Tersedia:**
1. **Info Session AHP**
   - ID Session, Tahun Ajaran, Semester
   - Tanggal Dibuat, Status, Keterangan

2. **Matrix Perbandingan**
   - Matrix perbandingan antar kriteria
   - Nilai konsistensi (CR)
   - Status konsistensi

3. **Hasil AHP**
   - Bobot setiap kriteria
   - Persentase prioritas
   - Analisis detail

4. **Ranking Final**
   - Ranking berdasarkan skor AHP
   - Kategori prioritas (A, B, C, D, E)
   - Rekomendasi pengadaan

---

### 4. 📈 Ekspor Rekap Data
**Endpoint:** `/export/rekap-data`  
**File:** `RekapDataExport.php`

**Deskripsi:** Mengekspor rekap data komprehensif pengadaan bahan ajar dengan analisis statistik dan ringkasan.

**Fitur:**
- Data summary dan statistik
- Analisis per prodi
- Distribusi urgensi dan status
- Detail pengajuan lengkap

**Sheet yang Tersedia:**
1. **Rekap Umum**
   - Total pengajuan, prodi, session AHP
   - Statistik umum sistem

2. **Rekap Per Prodi**
   - Data pengajuan per prodi
   - Statistik per program studi

3. **Rekap Urgensi**
   - Distribusi urgensi prodi
   - Distribusi urgensi tim pengadaan
   - Distribusi urgensi institusi

4. **Rekap Hasil AHP**
   - Statistik hasil AHP
   - Analisis konsistensi
   - Rekomendasi threshold

5. **Detail Pengajuan**
   - Data lengkap semua pengajuan
   - Filter dan sorting

---

### 5. 🛒 Ekspor Daftar Pengadaan (Shopping List)
**Endpoint:** `/export/procurement/{prodiId?}`  
**File:** `ProcurementListExport.php`

**Deskripsi:** Mengekspor daftar pengadaan dalam format shopping list yang mudah dibaca untuk tim pengadaan.

**Fitur:**
- Format shopping list yang praktis
- Filter opsional per prodi
- Informasi vendor dan spesifikasi
- Total anggaran per prodi

---

### 6. 🔍 Ekspor Ranking Lanjutan
**Endpoint:** `/export/ranking-advanced`  
**File:** `RankingAdvancedExport.php`

**Deskripsi:** Mengekspor ranking dengan filter lanjutan untuk analisis mendalam.

**Filter yang Tersedia:**
- Prodi (TRPL, Mesin, Elektro, Mekatronika)
- Session AHP
- Status pengajuan
- Level urgensi

---

## 🎨 Fitur Tambahan

### Widget Dashboard Tim Pengadaan
**File:** `TimPengadaanExportWidget.php`

Widget khusus yang menampilkan semua opsi ekspor dalam interface yang user-friendly dengan:
- Card berwarna untuk setiap jenis ekspor
- Icon yang informatif
- Deskripsi singkat setiap fitur
- Tombol ekspor langsung
- Informasi tambahan dan tips

---

## 🔧 Cara Penggunaan

### 1. Melalui Dashboard Filament
1. Login sebagai Tim Pengadaan
2. Akses dashboard admin
3. Widget ekspor akan muncul otomatis
4. Klik tombol ekspor sesuai kebutuhan

### 2. Melalui URL Langsung
- Copy URL endpoint yang diinginkan
- Paste di browser
- File akan otomatis terdownload

### 3. Melalui Form Ekspor
- Akses `/export/form` untuk ekspor hasil AHP
- Pilih session yang sesuai
- Klik ekspor

---

## 📁 Struktur File Excel

### Format Umum
- **File Extension:** .xlsx
- **Encoding:** UTF-8
- **Compatibility:** Microsoft Excel, Google Sheets, LibreOffice Calc

### Styling
- **Header:** Background biru dengan teks putih
- **Border:** Garis tipis hitam di semua sel
- **Auto-size:** Kolom otomatis menyesuaikan konten
- **Wrap Text:** Teks panjang akan wrap otomatis

### Naming Convention
```
[jenis-ekspor]-[filter]-[timestamp].xlsx

Contoh:
- pengajuan-per-prodi-2025-08-10-17-45-30.xlsx
- ranking-ahp-per-prodi-2025-08-10-17-45-30.xlsx
- hasil-ahp-session-1-2025-08-10-17-45-30.xlsx
```

---

## 🔐 Keamanan dan Akses

### Role yang Diizinkan
- **Tim Pengadaan** - Akses penuh ke semua fitur ekspor
- **Super Admin** - Akses penuh ke semua fitur ekspor
- **Kaprodi** - Akses terbatas sesuai prodi

### Validasi Akses
- Middleware authentication
- Role-based access control
- Prodi-specific access untuk Kaprodi
- Session validation untuk AHP

---

## 📊 Contoh Output

### Sheet Pengajuan Per Prodi
```
| No | Nama Barang | Spesifikasi | Vendor | Jumlah | Harga Satuan | Total Harga | ... |
|----|-------------|-------------|---------|---------|---------------|-------------|-----|
| 1  | Laptop      | Core i5     | Vendor A| 5       | Rp 8.000.000 | Rp 40.000.000| ... |
| 2  | Printer     | Laser       | Vendor B| 3       | Rp 2.500.000 | Rp 7.500.000 | ... |
```

### Sheet Ranking AHP
```
| Ranking | Nama Barang | AHP Score | Urgensi | ... |
|---------|-------------|-----------|---------|-----|
| 1       | Laptop      | 0.3245    | High    | ... |
| 2       | Printer     | 0.2876    | Medium  | ... |
| 3       | Scanner     | 0.1987    | Low     | ... |
```

---

## 🚨 Troubleshooting

### Masalah Umum
1. **File tidak terdownload**
   - Cek koneksi internet
   - Pastikan popup blocker dimatikan
   - Cek permission folder storage

2. **Data tidak lengkap**
   - Pastikan ada data di database
   - Cek filter yang digunakan
   - Verifikasi session AHP aktif

3. **Error permission**
   - Pastikan role user sesuai
   - Cek middleware authentication
   - Verifikasi akses prodi

### Log dan Debug
- Cek log Laravel di `storage/logs/laravel.log`
- Monitor response API di browser developer tools
- Verifikasi route dengan `php artisan route:list`

---

## 🔄 Update dan Maintenance

### Versi Terbaru
- **v1.0.0** - Fitur dasar ekspor
- **v1.1.0** - Penambahan widget dashboard
- **v1.2.0** - Optimasi performa dan styling

### Roadmap
- [ ] Export ke PDF
- [ ] Export ke CSV
- [ ] Scheduled export otomatis
- [ ] Email notification hasil export
- [ ] Template export kustom

---

## 📞 Support

Untuk bantuan teknis atau pertanyaan:
- **Email:** support@spk-bahan-ajar.com
- **Documentation:** Lihat file README.md
- **Issue Tracker:** GitHub Issues

---

*Dokumentasi ini dibuat untuk Tim Pengadaan SPK Bahan Ajar - Versi 1.2.0*
