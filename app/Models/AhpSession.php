<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AhpResult;

class AhpSession extends Model
{
    protected $fillable = ['tahun_ajaran', 'semester', 'status', 'is_active'];

    protected $casts = [
        'status' => 'string',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => false,
    ];

    public function comparisons(): HasMany
    {
        return $this->hasMany(AhpComparison::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(AhpResult::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // Methods
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
