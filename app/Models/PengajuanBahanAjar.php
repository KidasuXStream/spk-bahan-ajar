<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanBahanAjar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_barang',
        'spesifikasi',
        'vendor',
        'jumlah',
        'harga_satuan',
        'masa_pakai',
        'stok',
        'status_pengajuan',
        'alasan_penolakan',      // Catatan dari Kaprodi
        'catatan_pengadaan',     // Catatan dari Tim Pengadaan
        'urgensi_prodi',
        'urgensi_institusi',
        'ahp_session_id',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'jumlah' => 'integer',
        'stok' => 'integer',
        'urgensi_prodi' => 'string',
        'urgensi_institusi' => 'string',

    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ahpSession(): BelongsTo
    {
        return $this->belongsTo(AhpSession::class);
    }

    // Helper methods for AHP criteria values
    public function getHargaValue(): float
    {
        return (float) $this->harga_satuan;
    }

    public function getJumlahValue(): int
    {
        return $this->jumlah;
    }

    public function getStokValue(): int
    {
        return $this->stok;
    }

    public function getUrgensiProdiValue(): int
    {
        return match ($this->urgensi_prodi) {
            'tinggi' => 3,
            'sedang' => 2,
            'rendah' => 1,
            default => 1,
        };
    }

    public function getUrgensiInstitusiValue(): int
    {
        return match ($this->urgensi_institusi) {
            'tinggi' => 3,
            'sedang' => 2,
            'rendah' => 1,
            default => 1,
        };
    }

    // Method untuk mendapatkan nilai urgensi yang digunakan dalam AHP
    public function getUrgensiAHPValue(): int
    {
        // Gunakan urgensi_institusi sebagai nilai utama untuk AHP
        return $this->getUrgensiInstitusiValue();
    }



    public function getMasaPakaiValue(): int
    {
        // Extract numeric value from masa_pakai string
        // Example: "12 bulan" -> 12, "2 tahun" -> 2
        preg_match('/\d+/', $this->masa_pakai, $matches);
        return isset($matches[0]) ? (int) $matches[0] : 0;
    }

    // Helper method to check if item is ready for AHP calculation
    public function isReadyForAHP(): bool
    {
        return !empty($this->urgensi_prodi) &&
            !empty($this->urgensi_institusi) &&
            !empty($this->urgensi_tim_pengadaan);
    }

    // Helper method to get priority status based on AHP score
    public function getPriorityStatus(): string
    {
        // This will be calculated based on AHP results
        return 'pending';
    }
}
