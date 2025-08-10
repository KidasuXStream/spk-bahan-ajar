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

class RankingPerProdiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $prodiId;
    protected $prodiName;
    protected $sessionId;
    protected $sessionName;

    public function __construct($prodiId = null, $sessionId = null)
    {
        $this->prodiId = $prodiId;
        $this->sessionId = $sessionId;

        if ($prodiId) {
            $this->prodiName = $this->getProdiName($prodiId);
        } else {
            $this->prodiName = 'Semua Prodi';
        }

        if ($sessionId) {
            $this->sessionName = $this->getSessionName($sessionId);
        } else {
            $this->sessionName = 'Semua Session';
        }
    }

    public function collection()
    {
        $query = PengajuanBahanAjar::with([
            'user',
            'ahpSession'
        ]);

        // Filter by prodi if specified
        if ($this->prodiId) {
            $query->whereHas('user', function ($q) {
                $q->where('prodi', $this->prodiId);
            });
        }

        // Filter by AHP session if specified, otherwise prioritize active sessions
        if ($this->sessionId) {
            $query->where('ahp_session_id', $this->sessionId);
        } else {
            $query->whereHas('ahpSession', function ($q) {
                $q->where('is_active', true);
            });
        }

        // Get AHP scores and rankings
        $pengajuan = $query->get();

        // Calculate AHP scores if not available
        foreach ($pengajuan as $item) {
            if ($item->ahp_session_id && !$item->ahp_score) {
                // Try to get AHP score from AhpResult
                $ahpResult = \App\Models\AhpResult::where('ahp_session_id', $item->ahp_session_id)
                    ->where('kriteria_id', function ($query) {
                        $query->select('id')
                            ->from('kriterias')
                            ->where('nama_kriteria', 'Harga')
                            ->first();
                    })
                    ->first();

                if ($ahpResult) {
                    $item->ahp_score = $ahpResult->bobot;
                }
            }
        }

        // Sort by ranking position, then by AHP score, then by urgensi
        return $pengajuan->sortBy([
            ['ranking_position', 'asc'],
            ['ahp_score', 'desc'],
            ['urgensi_prodi', 'desc'],
            ['urgensi_tim_pengadaan', 'desc']
        ])->values();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Spesifikasi',
            'Prodi',
            'Jumlah Dibutuhkan',
            'Stok Tersedia',
            'Jumlah Harus Dibeli',
            'Harga Satuan (Rp)',
            'Harga Total (Rp)',
            'Urgensi Prodi',
            'Urgensi Tim Pengadaan',
            'Urgensi Institusi',
            'Nilai AHP',
            'Ranking',
            'Status Prioritas',
            'Session AHP',
            'Keterangan'
        ];
    }

    public function map($pengajuan): array
    {
        $jumlahHarusBeli = max(0, $pengajuan->jumlah - ($pengajuan->stok ?? 0));
        $hargaTotal = $jumlahHarusBeli * $pengajuan->harga_satuan;

        $statusPrioritas = $this->getPriorityStatus($pengajuan->ranking_position ?? $pengajuan->ranking ?? 0);
        $keterangan = $this->getKeterangan($pengajuan);
        $sessionInfo = $pengajuan->ahpSession ?
            $pengajuan->ahpSession->tahun_ajaran . ' - ' . $pengajuan->ahpSession->semester :
            'Tidak ada session';

        return [
            $pengajuan->ranking_position ?? $pengajuan->ranking ?? '-',
            $pengajuan->nama_barang,
            $pengajuan->spesifikasi ?? '-',
            strtoupper($pengajuan->user->prodi ?? '-'),
            $pengajuan->jumlah,
            $pengajuan->stok ?? 0,
            $jumlahHarusBeli,
            number_format($pengajuan->harga_satuan, 0, ',', '.'),
            number_format($hargaTotal, 0, ',', '.'),
            $this->getUrgensiLabel($pengajuan->urgensi_prodi),
            $this->getUrgensiLabel($pengajuan->urgensi_tim_pengadaan),
            $this->getUrgensiLabel($pengajuan->urgensi_institusi),
            number_format($pengajuan->ahp_score ?? 0, 4),
            $pengajuan->ranking_position ?? $pengajuan->ranking ?? '-',
            $statusPrioritas,
            $sessionInfo,
            $keterangan
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:Q1')->applyFromArray([
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

        // Border styling
        $sheet->getStyle('A1:Q' . ($sheet->getHighestRow()))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Priority status coloring
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $priorityCell = $sheet->getCell('O' . $row)->getValue();

            if ($priorityCell === 'Diprioritaskan') {
                $sheet->getStyle('O' . $row)->applyFromArray([
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
                $sheet->getStyle('O' . $row)->applyFromArray([
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
                $sheet->getStyle('O' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEE2E2'],
                    ],
                    'font' => [
                        'color' => ['rgb' => '991B1B'],
                        'bold' => true,
                    ],
                ]);
            } elseif ($priorityCell === 'Belum Dihitung') {
                $sheet->getStyle('O' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3F4F6'],
                    ],
                    'font' => [
                        'color' => ['rgb' => '6B7280'],
                        'bold' => true,
                    ],
                ]);
            }
        }

        // Number formatting
        $sheet->getStyle('E:G')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('H:I')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('M')->getNumberFormat()->setFormatCode('0.0000');

        // Auto-filter
        $sheet->setAutoFilter('A1:Q1');

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // No
            'B' => 35,  // Nama Barang
            'C' => 30,  // Spesifikasi
            'D' => 15,  // Prodi
            'E' => 20,  // Jumlah Dibutuhkan
            'F' => 18,  // Stok Tersedia
            'G' => 22,  // Jumlah Harus Dibeli
            'H' => 20,  // Harga Satuan
            'I' => 20,  // Harga Total
            'J' => 15,  // Urgensi Prodi
            'K' => 20,  // Urgensi Tim Pengadaan
            'L' => 20,  // Urgensi Institusi
            'M' => 15,  // Nilai AHP
            'N' => 12,  // Ranking
            'O' => 18,  // Status Prioritas
            'P' => 25,  // Session AHP
            'Q' => 40,  // Keterangan
        ];
    }

    public function title(): string
    {
        $title = 'Ranking Pengajuan ' . $this->prodiName;
        if ($this->sessionName !== 'Semua Session') {
            $title .= ' - ' . $this->sessionName;
        }
        return $title;
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

    protected function getSessionName($sessionId)
    {
        $session = \App\Models\AhpSession::find($sessionId);
        return $session ? $session->tahun_ajaran . ' - ' . $session->semester : 'Session tidak ditemukan';
    }

    protected function getPriorityStatus($ranking)
    {
        // Handle null or invalid rankings
        if (empty($ranking) || $ranking === '-' || $ranking === 0) {
            return 'Belum Dihitung';
        }

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

    protected function getKeterangan($pengajuan)
    {
        $keterangan = [];

        if ($pengajuan->stok >= $pengajuan->jumlah) {
            $keterangan[] = 'Stok mencukupi';
        } else {
            $keterangan[] = 'Perlu pembelian';
        }

        if ($pengajuan->urgensi_prodi === 'tinggi') {
            $keterangan[] = 'Urgensi prodi tinggi';
        }

        if ($pengajuan->urgensi_tim_pengadaan === 'tinggi') {
            $keterangan[] = 'Urgensi tim pengadaan tinggi';
        }

        if ($pengajuan->urgensi_institusi === 'tinggi') {
            $keterangan[] = 'Urgensi institusi tinggi';
        }

        return implode(', ', $keterangan);
    }
}
