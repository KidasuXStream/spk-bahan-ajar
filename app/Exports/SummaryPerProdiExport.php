<?php

namespace App\Exports;

use App\Models\PengajuanBahanAjar;
use App\Models\AhpSession;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SummaryPerProdiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithMultipleSheets
{
    protected $sessionId;

    public function __construct(int $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Get all prodi from users
        $prodis = User::distinct()->pluck('prodi')->filter()->toArray();

        foreach ($prodis as $prodi) {
            $sheets[] = new SummaryPerProdiSheet($this->sessionId, $prodi);
        }

        // Add summary sheet
        $sheets[] = new SummaryAllProdiSheet($this->sessionId);

        return $sheets;
    }

    public function collection()
    {
        // This won't be used due to WithMultipleSheets
        return collect([]);
    }

    public function headings(): array
    {
        return [];
    }

    public function map($item): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return $sheet;
    }

    public function columnWidths(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Summary Per Prodi';
    }
}

class SummaryPerProdiSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $sessionId;
    protected $prodi;

    public function __construct(int $sessionId, string $prodi)
    {
        $this->sessionId = $sessionId;
        $this->prodi = $prodi;
    }

    public function collection()
    {
        return PengajuanBahanAjar::where('ahp_session_id', $this->sessionId)
            ->whereHas('user', function ($q) {
                $q->where('prodi', $this->prodi);
            })
            ->with(['user'])
            ->whereNotNull('ahp_score')
            ->orderBy('ranking_position', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'RANGKUMAN PENGADAAN BAHAN AJAR',
            '',
            'Prodi: ' . $this->prodi,
            'Tahun Ajaran: ' . $this->getTahunAjaran(),
            'Semester: ' . $this->getSemester(),
            '',
            'No',
            'Nama Barang',
            'Jumlah',
            'Stok',
            'Harga Satuan',
            'Harga Total',
            'Status Prioritas',
            'Grade AHP',
            'Pengaju'
        ];
    }

    public function map($item): array
    {
        $priorityStatus = $this->getPriorityStatus($item->ahp_score);
        $grade = $this->getGrade($item->ahp_score);
        $hargaTotal = $item->jumlah * $item->harga_satuan;

        return [
            '', // Empty for title row
            '', // Empty for title row
            '', // Empty for title row
            '', // Empty for title row
            '', // Empty for title row
            '', // Empty for title row
            $item->ranking_position ?? 'N/A',
            $item->nama_barang,
            $item->jumlah,
            $item->stok ?? 0,
            'Rp ' . number_format($item->harga_satuan, 0, ',', '.'),
            'Rp ' . number_format($hargaTotal, 0, ',', '.'),
            $priorityStatus,
            $grade,
            $item->user->name ?? 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Title styling
        $sheet->getStyle('A1:A5')->applyFromArray([
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
        $sheet->mergeCells('A5:O5');

        // Header styling
        $sheet->getStyle('A7:O7')->applyFromArray([
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

        // Priority status colors
        $this->applyPriorityColors($sheet);

        // Border styling
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A7:O' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Auto-filter
        $sheet->setAutoFilter('A7:O7');

        // Add summary at bottom
        $this->addSummarySection($sheet);

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // No
            'B' => 35,  // Nama Barang
            'C' => 12,  // Jumlah
            'D' => 12,  // Stok
            'E' => 18,  // Harga Satuan
            'F' => 18,  // Harga Total
            'G' => 18,  // Status Prioritas
            'H' => 12,  // Grade
            'I' => 20,  // Pengaju
        ];
    }

    public function title(): string
    {
        return $this->prodi;
    }

    private function getTahunAjaran(): string
    {
        $session = AhpSession::find($this->sessionId);
        return $session ? $session->tahun_ajaran : 'N/A';
    }

    private function getSemester(): string
    {
        $session = AhpSession::find($this->sessionId);
        return $session ? $session->semester : 'N/A';
    }

    private function getPriorityStatus($score): string
    {
        if ($score >= 0.25) return 'Diprioritaskan';
        if ($score >= 0.15) return 'Sedang';
        return 'Dapat Ditunda';
    }

    private function getGrade($score): string
    {
        if ($score >= 0.3) return 'A';
        if ($score >= 0.2) return 'B';
        if ($score >= 0.15) return 'C';
        if ($score >= 0.1) return 'D';
        return 'E';
    }

    private function applyPriorityColors(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        for ($row = 8; $row <= $highestRow; $row++) {
            $priorityCell = $sheet->getCell('G' . $row)->getValue();

            switch ($priorityCell) {
                case 'Diprioritaskan':
                    $sheet->getStyle('G' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('C6EFCE');
                    break;

                case 'Sedang':
                    $sheet->getStyle('G' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FFEB9C');
                    break;

                case 'Dapat Ditunda':
                    $sheet->getStyle('G' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FFC7CE');
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
        $prioritized = $this->getPriorityCount('Diprioritaskan');
        $medium = $this->getPriorityCount('Sedang');
        $delayed = $this->getPriorityCount('Dapat Ditunda');

        $sheet->setCellValue('A' . $startRow, 'Jumlah Barang Diprioritaskan:');
        $sheet->setCellValue('B' . $startRow, $prioritized);
        $sheet->getStyle('A' . $startRow)->getFont()->setBold(true);

        $startRow++;
        $sheet->setCellValue('A' . $startRow, 'Jumlah Barang Sedang:');
        $sheet->setCellValue('B' . $startRow, $medium);
        $sheet->getStyle('A' . $startRow)->getFont()->setBold(true);

        $startRow++;
        $sheet->setCellValue('A' . $startRow, 'Jumlah Barang Dapat Ditunda:');
        $sheet->setCellValue('B' . $startRow, $delayed);
        $sheet->getStyle('A' . $startRow)->getFont()->setBold(true);

        $startRow += 2;

        // Budget summary
        $totalBudget = $this->getTotalBudget();
        $sheet->setCellValue('A' . $startRow, 'Total Anggaran:');
        $sheet->setCellValue('B' . $startRow, 'Rp ' . number_format($totalBudget, 0, ',', '.'));
        $sheet->getStyle('A' . $startRow)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B' . $startRow)->getFont()->setBold(true)->setSize(12);
    }

    private function getPriorityCount($priority): int
    {
        $collection = $this->collection();
        return $collection->filter(function ($item) use ($priority) {
            return $this->getPriorityStatus($item->ahp_score) === $priority;
        })->count();
    }

    private function getTotalBudget(): float
    {
        $collection = $this->collection();
        return $collection->sum(function ($item) {
            return $item->jumlah * $item->harga_satuan;
        });
    }
}

class SummaryAllProdiSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $sessionId;

    public function __construct(int $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function collection()
    {
        $prodis = User::distinct()->pluck('prodi')->filter()->toArray();
        $summary = [];

        foreach ($prodis as $prodi) {
            $items = PengajuanBahanAjar::where('ahp_session_id', $this->sessionId)
                ->whereHas('user', function ($q) use ($prodi) {
                    $q->where('prodi', $prodi);
                })
                ->whereNotNull('ahp_score')
                ->get();

            $totalBudget = $items->sum(function ($item) {
                return $item->jumlah * $item->harga_satuan;
            });

            $prioritized = $items->filter(function ($item) {
                return $this->getPriorityStatus($item->ahp_score) === 'Diprioritaskan';
            })->count();

            $summary[] = [
                'prodi' => $prodi,
                'total_items' => $items->count(),
                'prioritized' => $prioritized,
                'total_budget' => $totalBudget,
            ];
        }

        return collect($summary);
    }

    public function headings(): array
    {
        return [
            'RINGKASAN SEMUA PRODI',
            '',
            'Tahun Ajaran: ' . $this->getTahunAjaran(),
            'Semester: ' . $this->getSemester(),
            '',
            'Prodi',
            'Total Barang',
            'Barang Diprioritaskan',
            'Total Anggaran'
        ];
    }

    public function map($item): array
    {
        return [
            '', // Empty for title row
            '', // Empty for title row
            '', // Empty for title row
            '', // Empty for title row
            '', // Empty for title row
            $item['prodi'],
            $item['total_items'],
            $item['prioritized'],
            'Rp ' . number_format($item['total_budget'], 0, ',', '.')
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

        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');
        $sheet->mergeCells('A4:I4');

        // Header styling
        $sheet->getStyle('A6:I6')->applyFromArray([
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

        // Border styling
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A6:I' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,  // Prodi
            'B' => 15,  // Total Barang
            'C' => 20,  // Barang Diprioritaskan
            'D' => 20,  // Total Anggaran
        ];
    }

    public function title(): string
    {
        return 'Ringkasan Semua Prodi';
    }

    private function getTahunAjaran(): string
    {
        $session = AhpSession::find($this->sessionId);
        return $session ? $session->tahun_ajaran : 'N/A';
    }

    private function getSemester(): string
    {
        $session = AhpSession::find($this->sessionId);
        return $session ? $session->semester : 'N/A';
    }

    private function getPriorityStatus($score): string
    {
        if ($score >= 0.25) return 'Diprioritaskan';
        if ($score >= 0.15) return 'Sedang';
        return 'Dapat Ditunda';
    }
}
