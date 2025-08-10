<?php

namespace App\Exports;

use App\Models\PengajuanBahanAjar;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PengajuanPerProdiExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $prodis = ['trpl', 'mesin', 'elektro', 'mekatronika'];
        $sheets = [];

        foreach ($prodis as $prodi) {
            $sheets[] = new PengajuanProdiSheet($prodi);
        }

        return $sheets;
    }
}

class PengajuanProdiSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles
{
    protected $prodi;

    public function __construct($prodi)
    {
        $this->prodi = $prodi;
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

    public function collection()
    {
        return PengajuanBahanAjar::whereHas('user', function ($query) {
            $query->where('prodi', $this->prodi);
        })
            ->with(['user', 'ahpSession'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Spesifikasi',
            'Vendor',
            'Jumlah',
            'Harga Satuan',
            'Total Harga',
            'Masa Pakai',
            'Stok',
            'Urgensi Prodi',
            'Urgensi Institusi',
            'Status',
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
        static $no = 0;
        $no++;

        return [
            $no,
            $pengajuan->nama_barang,
            $pengajuan->spesifikasi,
            $pengajuan->vendor,
            $pengajuan->jumlah,
            'Rp ' . number_format($pengajuan->harga_satuan, 0, ',', '.'),
            'Rp ' . number_format($pengajuan->harga_satuan * $pengajuan->jumlah, 0, ',', '.'),
            $pengajuan->masa_pakai,
            $pengajuan->stok,
            ucfirst($pengajuan->urgensi_prodi ?? '-'),
            ucfirst($pengajuan->urgensi_institusi ?? '-'),
            ucfirst($pengajuan->status_pengajuan),
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
                'startColor' => ['rgb' => '4472C4'],
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

        return $sheet;
    }
}
