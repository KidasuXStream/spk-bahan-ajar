<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AhpResult extends Model
{
    protected $fillable = [
        'ahp_session_id',
        'kriteria_id',
        'bobot'
    ];

    protected $casts = [
        'bobot' => 'double',
        'ahp_session_id' => 'integer',
        'kriteria_id' => 'integer',
    ];

    // Relationships
    public function session(): BelongsTo
    {
        return $this->belongsTo(AhpSession::class, 'ahp_session_id');
    }

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_id');
    }
}