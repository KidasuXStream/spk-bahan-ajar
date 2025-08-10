<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // ADD THIS IMPORT

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles; // ADD HasRoles TRAIT

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nidn',
        'nip',
        'prodi',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Helper methods for role checking (alternative to hasRole if needed)
    public function isKaprodi(): bool
    {
        return $this->hasRole('Kaprodi');
    }

    public function isTimPengadaan(): bool
    {
        return $this->hasRole('Tim Pengadaan');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function debugRoles()
    {
        dd([
            'traits' => class_uses_recursive($this),
            'methods' => get_class_methods($this),
            'roles' => $this->roles()->get(),
            'has_hasRole_method' => method_exists($this, 'hasRole')
        ]);
    }
}
