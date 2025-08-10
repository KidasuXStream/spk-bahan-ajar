<?php

namespace App\Exports;

use App\Models\PengajuanBahanAjar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProcurementListExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $prodiId;
    protected $prodiName;

    public function __construct($prodiId = null)
    {
        $this->prodiId = $prodiId;
        if ($prodiId) {
            $this->prodiName = $this->getProdiName($prodiId);
        } else {
            $this->prodiName = 'Semua Prodi';
        }
    }

    public function collection()
    {
        $query = PengajuanBahanAjar::with([
            'user',
            'ahpSession'
        ]);

        // Prioritize active sessions
        $query->whereHas('ahpSession', function ($q) {
            $q->where('is_active', true);
        });

        // If we have rankings, filter by top 6
        if (PengajuanBahanAjar::whereNotNull('ranking_position')->exists()) {
            $query->whereNotNull('ranking_position')
                ->where('ranking_position', '<=', 6);
        } else {
            // If no rankings, show all items with a note
            $query->limit(6);
        }

        if ($this->prodiId) {
            $query->whereHas('user', function ($q) {
                $q->where('prodi', $this->prodiId);
            });
        }

        return $query->orderBy('ranking_position', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'DAFTAR PENGADAAN BAHAN AJAR',
            '',
            'Prodi: ' . $this->prodiName,
            'Tanggal Export: ' . date('d/m/Y H:i'),
            '',
            'No',
            'Nama Barang',
            'Spesifikasi',
            'Jumlah Dibutuhkan',
            'Stok Tersedia',
            'Jumlah Harus Dibeli',
            'Harga Satuan (Rp)',
            'Harga Total (Rp)',
            'Prioritas',
            'Keterangan'
        ];
    }

    public function map($pengajuan): array
    {
        $jumlahHarusBeli = max(0, $pengajuan->jumlah - ($pengajuan->stok ?? 0));
        $hargaTotal = $jumlahHarusBeli * $pengajuan->harga_satuan;
        $prioritas = $this->getPriorityLabel($pengajuan->ranking_position ?? $pengajuan->ranking ?? null);

        return [
            '', // Empty for title row
            '', // Empty for title row
            '', // Empty for title row
            '', // Empty for title row
            '', // Empty for title row
            $pengajuan->ranking_position ?? $pengajuan->ranking ?? '-',
            $pengajuan->nama_barang,
            $pengajuan->spesifikasi ?? '-',
            $pengajuan->jumlah,
            $pengajuan->stok ?? 0,
            $jumlahHarusBeli,
            number_format($pengajuan->harga_satuan, 0, ',', '.'),
            number_format($hargaTotal, 0, ',', '.'),
            $prioritas,
            $this->getKeterangan($pengajuan)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Title styling
        $sheet->getStyle('A1:A4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->mergeCells('A1:O1');
        $sheet->mergeCells('A2:O2');
        $sheet->mergeCells('A3:O3');
        $sheet->mergeCells('A4:O4');

        // Header styling
        $sheet->getStyle('A6:O6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F2937'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Priority coloring
        $this->applyPriorityColors($sheet);

        // Border styling
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A6:O' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Number formatting
        $sheet->getStyle('I:K')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('L:M')->getNumberFormat()->setFormatCode('#,##0');

        // Auto-filter
        $sheet->setAutoFilter('A6:O6');

        // Add summary section
        $this->addSummarySection($sheet);

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // No
            'B' => 35,  // Nama Barang
            'C' => 30,  // Spesifikasi
            'D' => 20,  // Jumlah Dibutuhkan
            'E' => 18,  // Stok Tersedia
            'F' => 22,  // Jumlah Harus Dibeli
            'G' => 20,  // Harga Satuan
            'H' => 20,  // Harga Total
            'I' => 15,  // Prioritas
            'J' => 40,  // Keterangan
        ];
    }

    public function title(): string
    {
        return 'Daftar Pengadaan ' . $this->prodiName;
    }

    protected function getProdiName($prodiId)
    {
        $prodiNames = [
            'trpl' => 'TRPL',
            'mesin' => 'Mesin',
            'elektro' => 'Elektro',
            'sipil' => 'Sipil',
            'kimia' => 'Kimia'
        ];

        return $prodiNames[$prodiId] ?? strtoupper($prodiId);
    }

    protected function getPriorityLabel($ranking)
    {
        // Handle null or invalid rankings
        if (empty($ranking) || $ranking === '-' || $ranking === 0) {
            return 'Belum Dihitung';
        }

        if ($ranking <= 3) {
            return 'Tinggi';
        } elseif ($ranking <= 6) {
            return 'Sedang';
        } else {
            return 'Rendah';
        }
    }

    protected function getKeterangan($pengajuan)
    {
        $keterangan = [];

        if ($pengajuan->stok >= $pengajuan->jumlah) {
            $keterangan[] = 'Stok mencukupi';
        } else {
            $keterangan[] = 'Perlu pembelian';
        }

        if ($pengajuan->urgensi_prodi === 'tinggi' || $pengajuan->urgensi_prodi === 'sangat_tinggi') {
            $keterangan[] = 'Urgensi prodi tinggi';
        }

        if ($pengajuan->urgensi_tim_pengadaan === 'tinggi' || $pengajuan->urgensi_tim_pengadaan === 'sangat_tinggi') {
            $keterangan[] = 'Urgensi tim pengadaan tinggi';
        }

        if ($pengajuan->urgensi_institusi === 'tinggi' || $pengajuan->urgensi_institusi === 'sangat_tinggi') {
            $keterangan[] = 'Urgensi institusi tinggi';
        }

        return implode(', ', $keterangan);
    }

    private function applyPriorityColors(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        for ($row = 7; $row <= $highestRow; $row++) {
            $priorityCell = $sheet->getCell('I' . $row)->getValue();

            switch ($priorityCell) {
                case 'Tinggi':
                    $sheet->getStyle('I' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'DCFCE7'],
                        ],
                        'font' => [
                            'color' => ['rgb' => '166534'],
                            'bold' => true,
                        ],
                    ]);
                    break;

                case 'Sedang':
                    $sheet->getStyle('I' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEF3C7'],
                        ],
                        'font' => [
                            'color' => ['rgb' => '92400E'],
                            'bold' => true,
                        ],
                    ]);
                    break;

                case 'Rendah':
                    $sheet->getStyle('I' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEE2E2'],
                        ],
                        'font' => [
                            'color' => ['rgb' => '991B1B'],
                            'bold' => true,
                        ],
                    ]);
                    break;

                case 'Belum Dihitung':
                    $sheet->getStyle('I' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F3F4F6'],
                        ],
                        'font' => [
                            'color' => ['rgb' => '6B7280'],
                            'bold' => true,
                        ],
                    ]);
                    break;
            }
        }
    }

    private function addSummarySection(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $startRow = $highestRow + 2;

        // Summary headers
        $sheet->setCellValue('A' . $startRow, 'RINGKASAN PENGADAAN');
        $sheet->getStyle('A' . $startRow)->getFont()->setBold(true)->setSize(12);
        $sheet->mergeCells('A' . $startRow . ':O' . $startRow);

        $startRow += 2;

        // Priority counts
        $high = $this->getPriorityCount('Tinggi');
        $medium = $this->getPriorityCount('Sedang');
        $low = $this->getPriorityCount('Rendah');
        $notCalculated = $this->getPriorityCount('Belum Dihitung');

        $sheet->setCellValue('A' . $startRow, 'Jumlah Barang Prioritas Tinggi:');
        $sheet->setCellValue('B' . $startRow, $high);
        $sheet->getStyle('A' . $startRow)->getFont()->setBold(true);

        $startRow++;
        $sheet->setCellValue('A' . $startRow, 'Jumlah Barang Prioritas Sedang:');
        $sheet->setCellValue('B' . $startRow, $medium);
        $sheet->getStyle('A' . $startRow)->getFont()->setBold(true);

        $startRow++;
        $sheet->setCellValue('A' . $startRow, 'Jumlah Barang Prioritas Rendah:');
        $sheet->setCellValue('B' . $startRow, $low);
        $sheet->getStyle('A' . $startRow)->getFont()->setBold(true);

        $startRow++;
        $sheet->setCellValue('A' . $startRow, 'Jumlah Barang Belum Dihitung:');
        $sheet->setCellValue('B' . $startRow, $notCalculated);
        $sheet->getStyle('A' . $startRow)->getFont()->setBold(true);

        $startRow += 2;

        // Budget summary
        $totalBudget = $this->getTotalBudget();
        $sheet->setCellValue('A' . $startRow, 'Total Anggaran Pengadaan:');
        $sheet->setCellValue('B' . $startRow, 'Rp ' . number_format($totalBudget, 0, ',', '.'));
        $sheet->getStyle('A' . $startRow)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B' . $startRow)->getFont()->setBold(true)->setSize(12);
    }

    private function getPriorityCount($priority): int
    {
        return $this->collection()->filter(function ($item) use ($priority) {
            return $this->getPriorityLabel($item->ranking_position ?? $item->ranking ?? null) === $priority;
        })->count();
    }

    private function getTotalBudget(): float
    {
        return $this->collection()->sum(function ($item) {
            $jumlahHarusBeli = max(0, $item->jumlah - ($item->stok ?? 0));
            return $jumlahHarusBeli * $item->harga_satuan;
        });
    }
}
