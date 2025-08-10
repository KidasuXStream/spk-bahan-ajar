<?php

namespace App\Exports;

use App\Models\AhpSession;
use App\Models\AhpComparison;
use App\Models\AhpResult;
use App\Models\PengajuanBahanAjar;
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

class AHPResultsExport implements WithMultipleSheets
{
    protected $sessionId;

    public function __construct(int $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Add AHP Session Info sheet
        $sheets[] = new AHPSessionInfoSheet($this->sessionId);

        // Add Comparison Matrix sheet
        $sheets[] = new AHPComparisonMatrixSheet($this->sessionId);

        // Add Results sheet
        $sheets[] = new AHPResultsSheet($this->sessionId);

        // Add Ranking sheet
        $sheets[] = new AHPRankingSheet($this->sessionId);

        return $sheets;
    }
}

class AHPSessionInfoSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $sessionId;
    protected $session;

    public function __construct(int $sessionId)
    {
        $this->sessionId = $sessionId;
        $this->session = AhpSession::find($sessionId);
    }

    public function collection()
    {
        return collect([$this->session]);
    }

    public function headings(): array
    {
        return [
            'INFORMASI SESSION AHP',
            '',
            'ID Session',
            'Tahun Ajaran',
            'Semester',
            'Tanggal Dibuat',
            'Status',
            'Keterangan'
        ];
    }

    public function map($session): array
    {
        return [
            '', // Empty for title row
            '', // Empty for title row
            $session->id ?? '-',
            $session->tahun_ajaran ?? '-',
            $session->semester ?? '-',
            $session->created_at ? $session->created_at->format('d/m/Y H:i') : '-',
            $session->status ?? '-',
            $session->keterangan ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Title styling
        $sheet->getStyle('A1:A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');

        // Header styling
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F2937'],
            ],
        ]);

        // Data styling
        $sheet->getStyle('A4:H4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
        ]);

        // Border styling
        $sheet->getStyle('A3:H4')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,  // Title
            'B' => 15,  // ID Session
            'C' => 20,  // Tahun Ajaran
            'D' => 15,  // Semester
            'E' => 20,  // Tanggal Dibuat
            'F' => 15,  // Status
            'G' => 30,  // Keterangan
        ];
    }

    public function title(): string
    {
        return 'Info Session';
    }
}

class AHPComparisonMatrixSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $sessionId;
    protected $kriterias;

    public function __construct(int $sessionId)
    {
        $this->sessionId = $sessionId;
        $this->kriterias = \App\Models\Kriteria::orderBy('id')->get();
    }

    public function collection()
    {
        $comparisons = AhpComparison::where('ahp_session_id', $this->sessionId)
            ->where('kriteria_1_id', '!=', 'kriteria_2_id')
            ->get();

        $matrix = [];
        foreach ($this->kriterias as $kriteria1) {
            $row = ['kriteria' => $kriteria1->nama];
            foreach ($this->kriterias as $kriteria2) {
                if ($kriteria1->id == $kriteria2->id) {
                    $row[$kriteria2->id] = 1;
                } else {
                    $comparison = $comparisons->where('kriteria_1_id', $kriteria1->id)
                        ->where('kriteria_2_id', $kriteria2->id)
                        ->first();
                    $row[$kriteria2->id] = $comparison ? $comparison->nilai : 0;
                }
            }
            $matrix[] = $row;
        }

        return collect($matrix);
    }

    public function headings(): array
    {
        $headers = ['Kriteria'];
        foreach ($this->kriterias as $kriteria) {
            $headers[] = $kriteria->nama;
        }
        return $headers;
    }

    public function map($row): array
    {
        $mapped = [$row['kriteria']];
        foreach ($this->kriterias as $kriteria) {
            $mapped[] = $row[$kriteria->id] ?? 0;
        }
        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // Header styling
        $sheet->getStyle('A1:' . $highestCol . '1')->applyFromArray([
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
            ],
        ]);

        // First column styling
        $sheet->getStyle('A2:A' . $highestRow)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
        ]);

        // Matrix values styling
        $sheet->getStyle('B2:' . $highestCol . $highestRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Border styling
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        return $sheet;
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 25]; // Kriteria column
        foreach ($this->kriterias as $kriteria) {
            $widths[chr(ord('A') + count($widths))] = 15;
        }
        return $widths;
    }

    public function title(): string
    {
        return 'Matrix Perbandingan';
    }
}

class AHPResultsSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $sessionId;
    protected $kriterias;

    public function __construct(int $sessionId)
    {
        $this->sessionId = $sessionId;
        $this->kriterias = \App\Models\Kriteria::orderBy('id')->get();
    }

    public function collection()
    {
        $results = AhpResult::where('ahp_session_id', $this->sessionId)->get();

        $matrix = [];
        foreach ($this->kriterias as $kriteria) {
            $result = $results->where('kriteria_id', $kriteria->id)->first();
            $matrix[] = [
                'kriteria' => $kriteria->nama,
                'bobot' => $result ? $result->bobot : 0,
                'eigen_value' => $result ? $result->eigen_value : 0,
                'consistency_ratio' => $result ? $result->consistency_ratio : 0,
            ];
        }

        return collect($matrix);
    }

    public function headings(): array
    {
        return [
            'Kriteria',
            'Bobot',
            'Eigen Value',
            'Consistency Ratio'
        ];
    }

    public function map($row): array
    {
        return [
            $row['kriteria'],
            number_format($row['bobot'], 4),
            number_format($row['eigen_value'], 4),
            number_format($row['consistency_ratio'], 4)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Header styling
        $sheet->getStyle('A1:D1')->applyFromArray([
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
            ],
        ]);

        // Data styling
        $sheet->getStyle('A2:D' . $highestRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Border styling
        $sheet->getStyle('A1:D' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,  // Kriteria
            'B' => 15,  // Bobot
            'C' => 15,  // Eigen Value
            'D' => 20,  // Consistency Ratio
        ];
    }

    public function title(): string
    {
        return 'Hasil AHP';
    }
}

class AHPRankingSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $sessionId;

    public function __construct(int $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function collection()
    {
        return PengajuanBahanAjar::where('ahp_session_id', $this->sessionId)
            ->whereNotNull('ranking_position')
            ->with(['user'])
            ->orderBy('ranking_position', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Ranking',
            'Nama Barang',
            'Prodi',
            'Nilai AHP',
            'Status Prioritas',
            'Urgensi Prodi',
            'Urgensi Tim Pengadaan',
            'Urgensi Institusi',
            'Pengaju'
        ];
    }

    public function map($pengajuan): array
    {
        $statusPrioritas = $this->getPriorityStatus($pengajuan->ranking_position);

        return [
            $pengajuan->ranking_position ?? '-',
            $pengajuan->nama_barang,
            strtoupper($pengajuan->user->prodi ?? '-'),
            number_format($pengajuan->ahp_score ?? 0, 4),
            $statusPrioritas,
            $this->getUrgensiLabel($pengajuan->urgensi_prodi),
            $this->getUrgensiLabel($pengajuan->urgensi_tim_pengadaan),
            $this->getUrgensiLabel($pengajuan->urgensi_institusi),
            $pengajuan->user->name ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Header styling
        $sheet->getStyle('A1:I1')->applyFromArray([
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
            ],
        ]);

        // Priority status coloring
        $this->applyPriorityColors($sheet);

        // Border styling
        $sheet->getStyle('A1:I' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Auto-filter
        $sheet->setAutoFilter('A1:I1');

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Ranking
            'B' => 35,  // Nama Barang
            'C' => 15,  // Prodi
            'D' => 15,  // Nilai AHP
            'E' => 18,  // Status Prioritas
            'F' => 15,  // Urgensi Prodi
            'G' => 20,  // Urgensi Tim Pengadaan
            'H' => 20,  // Urgensi Institusi
            'I' => 25,  // Pengaju
        ];
    }

    public function title(): string
    {
        return 'Ranking AHP';
    }

    protected function getPriorityStatus($ranking)
    {
        if ($ranking <= 3) {
            return 'Diprioritaskan';
        } elseif ($ranking <= 6) {
            return 'Sedang';
        } else {
            return 'Dapat Ditunda';
        }
    }

    protected function getUrgensiLabel($urgensi)
    {
        $labels = [
            'sangat_rendah' => 'Sangat Rendah',
            'rendah' => 'Rendah',
            'sedang' => 'Sedang',
            'tinggi' => 'Tinggi',
            'sangat_tinggi' => 'Sangat Tinggi'
        ];

        return $labels[$urgensi] ?? ucfirst($urgensi ?? '-');
    }

    private function applyPriorityColors(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        for ($row = 2; $row <= $highestRow; $row++) {
            $priorityCell = $sheet->getCell('E' . $row)->getValue();

            if ($priorityCell === 'Diprioritaskan') {
                $sheet->getStyle('E' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DCFCE7'],
                    ],
                    'font' => [
                        'color' => ['rgb' => '166534'],
                        'bold' => true,
                    ],
                ]);
            } elseif ($priorityCell === 'Sedang') {
                $sheet->getStyle('E' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7'],
                    ],
                    'font' => [
                        'color' => ['rgb' => '92400E'],
                        'bold' => true,
                    ],
                ]);
            } elseif ($priorityCell === 'Dapat Ditunda') {
                $sheet->getStyle('E' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEE2E2'],
                    ],
                    'font' => [
                        'color' => ['rgb' => '991B1B'],
                        'bold' => true,
                    ],
                ]);
            }
        }
    }
}
