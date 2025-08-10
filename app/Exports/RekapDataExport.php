<?php

namespace App\Exports;

use App\Models\PengajuanBahanAjar;
use App\Models\User;
use App\Models\AhpSession;
use App\Models\AhpResult;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Illuminate\Support\Facades\DB;

class RekapDataExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new RekapSummarySheet(),
            new RekapPerProdiSheet(),
            new RekapUrgensiSheet(),
            new RekapAHPResultsSheet(),
            new RekapPengajuanDetailSheet()
        ];
    }
}

class RekapSummarySheet implements WithTitle, WithHeadings, WithStyles
{
    public function title(): string
    {
        return 'Rekap Umum';
    }

    public function headings(): array
    {
        return [
            'REKAP DATA PENGADAAN BAHAN AJAR',
            '',
            'Tanggal Export: ' . now()->format('d/m/Y H:i:s'),
            '',
            'Ringkasan Data:'
        ];
    }

    public function collection()
    {
        return collect([]);
    }

    public function styles(Worksheet $sheet)
    {
        // Title styling
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Add summary data
        $this->addSummaryData($sheet);

        // Auto-size columns
        $sheet->getColumnDimension('A')->setWidth(50);
        $sheet->getColumnDimension('B')->setWidth(30);

        return $sheet;
    }

    private function addSummaryData(Worksheet $sheet)
    {
        // Get summary data
        $totalPengajuan = PengajuanBahanAjar::count();
        $totalProdi = User::whereNotNull('prodi')->distinct('prodi')->count();
        $totalSessions = AhpSession::count();
        $totalUsers = User::count();

        $prodiStats = DB::table('users')
            ->select('prodi', DB::raw('count(*) as total'))
            ->whereNotNull('prodi')
            ->groupBy('prodi')
            ->get();

        $urgensiStats = PengajuanBahanAjar::select('urgensi_institusi', DB::raw('count(*) as total'))
            ->whereNotNull('urgensi_institusi')
            ->groupBy('urgensi_institusi')
            ->get();

        // Add data to sheet
        $row = 6;
        $sheet->setCellValue("A{$row}", 'Total Pengajuan:');
        $sheet->setCellValue("B{$row}", $totalPengajuan);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Program Studi:');
        $sheet->setCellValue("B{$row}", $totalProdi);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total AHP Sessions:');
        $sheet->setCellValue("B{$row}", $totalSessions);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Users:');
        $sheet->setCellValue("B{$row}", $totalUsers);
        $row += 2;

        // Prodi breakdown
        $sheet->setCellValue("A{$row}", 'Breakdown per Prodi:');
        $row++;
        foreach ($prodiStats as $stat) {
            $prodiName = match ($stat->prodi) {
                'trpl' => 'TRPL',
                'mesin' => 'Mesin',
                'elektro' => 'Elektro',
                'mekatronika' => 'Mekatronika',
                default => strtoupper($stat->prodi)
            };
            $sheet->setCellValue("A{$row}", $prodiName);
            $sheet->setCellValue("B{$row}", $stat->total);
            $row++;
        }
        $row += 2;

        // Urgensi breakdown
        $sheet->setCellValue("A{$row}", 'Breakdown Urgensi:');
        $row++;
        foreach ($urgensiStats as $stat) {
            $sheet->setCellValue("A{$row}", ucfirst($stat->urgensi_institusi));
            $sheet->setCellValue("B{$row}", $stat->total);
            $row++;
        }

        // Style the data
        $sheet->getStyle("A6:B{$row}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }
}

class RekapPerProdiSheet implements WithTitle, WithHeadings, WithMapping, WithStyles
{
    use CommonStyles;
    public function title(): string
    {
        return 'Rekap Per Prodi';
    }

    public function headings(): array
    {
        return [
            'Program Studi',
            'Total Pengajuan',
            'Total Nilai',
            'Rata-rata Harga',
            'Pengajuan Tinggi',
            'Pengajuan Sedang',
            'Pengajuan Rendah',
            'Status Diajukan',
            'Status Diproses'
        ];
    }

    public function collection()
    {
        return DB::table('pengajuan_bahan_ajars')
            ->join('users', 'pengajuan_bahan_ajars.user_id', '=', 'users.id')
            ->select(
                'users.prodi',
                DB::raw('count(*) as total_pengajuan'),
                DB::raw('sum(pengajuan_bahan_ajars.harga_satuan * pengajuan_bahan_ajars.jumlah) as total_nilai'),
                DB::raw('avg(pengajuan_bahan_ajars.harga_satuan) as rata_harga'),
                DB::raw('sum(case when pengajuan_bahan_ajars.urgensi_institusi = "tinggi" then 1 else 0 end) as urgensi_tinggi'),
                DB::raw('sum(case when pengajuan_bahan_ajars.urgensi_institusi = "sedang" then 1 else 0 end) as urgensi_sedang'),
                DB::raw('sum(case when pengajuan_bahan_ajars.urgensi_institusi = "rendah" then 1 else 0 end) as urgensi_rendah'),
                DB::raw('sum(case when pengajuan_bahan_ajars.status_pengajuan = "diajukan" then 1 else 0 end) as status_diajukan'),
                DB::raw('sum(case when pengajuan_bahan_ajars.status_pengajuan = "diproses" then 1 else 0 end) as status_diproses')
            )
            ->whereNotNull('users.prodi')
            ->groupBy('users.prodi')
            ->get();
    }

    public function map($item): array
    {
        return [
            match ($item->prodi) {
                'trpl' => 'TRPL',
                'mesin' => 'Mesin',
                'elektro' => 'Elektro',
                'mekatronika' => 'Mekatronika',
                default => strtoupper($item->prodi)
            },
            $item->total_pengajuan,
            'Rp ' . number_format($item->total_nilai, 0, ',', '.'),
            'Rp ' . number_format($item->rata_harga, 0, ',', '.'),
            $item->urgensi_tinggi,
            $item->urgensi_sedang,
            $item->urgensi_rendah,
            $item->status_diajukan,
            $item->status_diproses
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $this->applyCommonStyles($sheet);
        return $sheet;
    }
}

class RekapUrgensiSheet implements WithTitle, WithHeadings, WithMapping, WithStyles
{
    use CommonStyles;
    public function title(): string
    {
        return 'Rekap Urgensi';
    }

    public function headings(): array
    {
        return [
            'Level Urgensi',
            'Total Pengajuan',
            'Total Nilai',
            'Rata-rata Harga',
            'Breakdown per Prodi (TRPL)',
            'Breakdown per Prodi (Mesin)',
            'Breakdown per Prodi (Elektro)',
            'Breakdown per Prodi (Mekatronika)'
        ];
    }

    public function collection()
    {
        return DB::table('pengajuan_bahan_ajars')
            ->join('users', 'pengajuan_bahan_ajars.user_id', '=', 'users.id')
            ->select(
                'pengajuan_bahan_ajars.urgensi_institusi',
                DB::raw('count(*) as total_pengajuan'),
                DB::raw('sum(pengajuan_bahan_ajars.harga_satuan * pengajuan_bahan_ajars.jumlah) as total_nilai'),
                DB::raw('avg(pengajuan_bahan_ajars.harga_satuan) as rata_harga'),
                DB::raw('sum(case when users.prodi = "trpl" then 1 else 0 end) as trpl'),
                DB::raw('sum(case when users.prodi = "mesin" then 1 else 0 end) as mesin'),
                DB::raw('sum(case when users.prodi = "elektro" then 1 else 0 end) as elektro'),
                DB::raw('sum(case when users.prodi = "mekatronika" then 1 else 0 end) as mekatronika')
            )
            ->whereNotNull('pengajuan_bahan_ajars.urgensi_institusi')
            ->groupBy('pengajuan_bahan_ajars.urgensi_institusi')
            ->get();
    }

    public function map($item): array
    {
        return [
            ucfirst($item->urgensi_institusi),
            $item->total_pengajuan,
            'Rp ' . number_format($item->total_nilai, 0, ',', '.'),
            'Rp ' . number_format($item->rata_harga, 0, ',', '.'),
            $item->trpl,
            $item->mesin,
            $item->elektro,
            $item->mekatronika
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $this->applyCommonStyles($sheet);
        return $sheet;
    }
}

class RekapAHPResultsSheet implements WithTitle, WithHeadings, WithMapping, WithStyles
{
    use CommonStyles;
    public function title(): string
    {
        return 'Rekap AHP';
    }

    public function headings(): array
    {
        return [
            'Session',
            'Tahun Ajaran',
            'Semester',
            'Total Kriteria',
            'Total Pengajuan',
            'Consistency Ratio',
            'Status Konsistensi',
            'Tanggal Dibuat'
        ];
    }

    public function collection()
    {
        return AhpSession::withCount(['ahpResults', 'pengajuanBahanAjar'])
            ->get();
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->tahun_ajaran,
            $item->semester,
            $item->ahp_results_count,
            $item->pengajuan_bahan_ajar_count,
            number_format($item->consistency_ratio ?? 0, 4),
            ($item->consistency_ratio ?? 0) <= 0.1 ? 'Konsisten' : 'Tidak Konsisten',
            $item->created_at->format('d/m/Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $this->applyCommonStyles($sheet);
        return $sheet;
    }
}

class RekapPengajuanDetailSheet implements WithTitle, WithHeadings, WithMapping, WithStyles
{
    use CommonStyles;
    public function title(): string
    {
        return 'Detail Pengajuan';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Barang',
            'Prodi',
            'Pengaju',
            'Harga Satuan',
            'Jumlah',
            'Total Harga',
            'Urgensi Prodi',
            'Urgensi Institusi',
            'Status',
            'AHP Score',
            'Ranking',
            'Tahun Ajaran',
            'Semester',
            'Tanggal Pengajuan'
        ];
    }

    public function collection()
    {
        return PengajuanBahanAjar::with(['user', 'ahpSession'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->nama_barang,
            match ($item->user->prodi ?? '') {
                'trpl' => 'TRPL',
                'mesin' => 'Mesin',
                'elektro' => 'Elektro',
                'mekatronika' => 'Mekatronika',
                default => '-'
            },
            $item->user->name ?? '-',
            'Rp ' . number_format($item->harga_satuan, 0, ',', '.'),
            $item->jumlah,
            'Rp ' . number_format($item->harga_satuan * $item->jumlah, 0, ',', '.'),
            ucfirst($item->urgensi_prodi ?? '-'),
            ucfirst($item->urgensi_institusi ?? '-'),
            ucfirst($item->status_pengajuan),
            $item->ahp_score ? number_format($item->ahp_score, 4) : '-',
            $item->ranking_position ?? '-',
            $item->ahpSession->tahun_ajaran ?? '-',
            $item->ahpSession->semester ?? '-',
            $item->created_at->format('d/m/Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $this->applyCommonStyles($sheet);
        return $sheet;
    }
}

trait CommonStyles
{
    protected function applyCommonStyles(Worksheet $sheet)
    {
        // Header styling
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Auto-size columns
        foreach (range('A', $highestColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Wrap text for long content
        $sheet->getStyle("A2:{$highestColumn}{$highestRow}")->getAlignment()->setWrapText(true);
    }
}
