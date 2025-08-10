<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kriteria extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_kriteria',
        'nama_kriteria',
        'deskripsi',
        'bobot_awal',
        'jenis',
        'satuan',
        'is_active'
    ];

    protected $casts = [
        'bobot_awal' => 'float',
        'is_active' => 'boolean'
    ];

    public function comparisons()
    {
        return $this->hasMany(AhpComparison::class, 'kriteria_1_id')
            ->orWhere('kriteria_2_id', $this->id);
    }
}
