<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nip',
        'phone',
        'specialization',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPerawat(): bool
    {
        return $this->role === 'perawat';
    }

    public function isDokter(): bool
    {
        return $this->role === 'dokter';
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->role === $roles;
        }
        return in_array($this->role, $roles);
    }

    public function rekamMedisAsPerawat(): HasMany
    {
        return $this->hasMany(RekamMedis::class, 'perawat_id');
    }

    public function rekamMedisAsDokter(): HasMany
    {
        return $this->hasMany(RekamMedis::class, 'dokter_id');
    }

    public function anamnesis(): HasMany
    {
        return $this->hasMany(Anamnesis::class, 'perawat_id');
    }

    public function pemeriksaans(): HasMany
    {
        return $this->hasMany(Pemeriksaan::class, 'dokter_id');
    }

    public function suratDokters(): HasMany
    {
        return $this->hasMany(SuratDokter::class, 'dokter_id');
    }
}
