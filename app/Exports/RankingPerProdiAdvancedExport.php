<?php

namespace App\Exports;

use App\Models\PengajuanBahanAjar;
use App\Models\User;
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

class RankingPerProdiAdvancedExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $prodis = ['trpl', 'mesin', 'elektro', 'mekatronika'];
        $sheets = [];

        foreach ($prodis as $prodi) {
            $sheets[] = new RankingProdiSheet($prodi);
        }

        return $sheets;
    }
}

class RankingProdiSheet implements WithTitle, WithHeadings, WithMapping, WithStyles
{
    protected $prodi;
    protected $data;

    public function __construct($prodi)
    {
        $this->prodi = $prodi;
        $this->loadData();
    }

    protected function loadData()
    {
        $this->data = PengajuanBahanAjar::whereHas('user', function ($query) {
            $query->where('prodi', $this->prodi);
        })
            ->whereNotNull('ahp_score')
            ->whereNotNull('ranking_position')
            ->with(['user', 'ahpSession'])
            ->orderBy('ranking_position', 'asc')
            ->get();
    }

    public function title(): string
    {
        return match ($this->prodi) {
            'trpl' => 'TRPL',
            'mesin' => 'Mesin',
            'elektro' => 'Elektro',
            'mekatronika' => 'Mekatronika',
            default => strtoupper($this->prodi)
        };
    }

    public function headings(): array
    {
        return [
            'Ranking',
            'Nama Barang',
            'Spesifikasi',
            'Vendor',
            'Jumlah',
            'Harga Satuan',
            'Total Harga',
            'Stok',
            'Masa Pakai',
            'AHP Score',
            'Urgensi Prodi',
            'Urgensi Institusi',
            'Pengaju',
            'NIDN',
            'NIP',
            'Tahun Ajaran',
            'Semester',
            'Tanggal Pengajuan',
            'Catatan Pengaju',
            'Catatan Tim Pengadaan'
        ];
    }

    public function map($pengajuan): array
    {
        return [
            $pengajuan->ranking_position ?? '-',
            $pengajuan->nama_barang,
            $pengajuan->spesifikasi,
            $pengajuan->vendor,
            $pengajuan->jumlah,
            'Rp ' . number_format($pengajuan->harga_satuan, 0, ',', '.'),
            'Rp ' . number_format($pengajuan->harga_satuan * $pengajuan->jumlah, 0, ',', '.'),
            $pengajuan->stok,
            $pengajuan->masa_pakai,
            number_format($pengajuan->ahp_score, 4),
            ucfirst($pengajuan->urgensi_prodi ?? '-'),
            ucfirst($pengajuan->urgensi_institusi ?? '-'),
            $pengajuan->user->name ?? '-',
            $pengajuan->user->nidn ?? '-',
            $pengajuan->user->nip ?? '-',
            $pengajuan->ahpSession->tahun_ajaran ?? '-',
            $pengajuan->ahpSession->semester ?? '-',
            $pengajuan->created_at->format('d/m/Y H:i'),
            $pengajuan->alasan_penolakan ?? '-',
            $pengajuan->catatan_pengadaan ?? '-'
        ];
    }

    public function collection()
    {
        return $this->data;
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:T1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E75B6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Auto-size columns
        foreach (range('A', 'T') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders
        $sheet->getStyle('A1:T' . ($sheet->getHighestRow()))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Wrap text for long content
        $sheet->getStyle('B:T')->getAlignment()->setWrapText(true);

        // Highlight top 3 rankings
        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 1) {
            // Top 1 - Gold
            if ($highestRow >= 1) {
                $sheet->getStyle('A2:T2')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFD700'],
                    ],
                ]);
            }

            // Top 2 - Silver
            if ($highestRow >= 2) {
                $sheet->getStyle('A3:T3')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C0C0C0'],
                    ],
                ]);
            }

            // Top 3 - Bronze
            if ($highestRow >= 3) {
                $sheet->getStyle('A4:T4')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'CD7F32'],
                    ],
                ]);
            }
        }

        return $sheet;
    }
}
