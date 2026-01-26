<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pasien extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pasiens';

    protected $fillable = [
        'nomor_rm',
        'nik',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'phone',
        'email',
        'tipe_pasien',
        'nomor_identitas',
        'fakultas',
        'prodi',
        'golongan_darah',
        'riwayat_alergi',
        'riwayat_penyakit',
        'kontak_darurat_nama',
        'kontak_darurat_phone',
        'kontak_darurat_hubungan',
        'pekerjaan',
        'status_perkawinan',
        'agama',
        'pendidikan_terakhir',
        'consent_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'consent_at' => 'datetime',
        ];
    }

    public function rekamMedis(): HasMany
    {
        return $this->hasMany(RekamMedis::class);
    }

    public function getUmurAttribute(): ?int
    {
        if (!$this->tanggal_lahir) {
            return null;
        }
        return $this->tanggal_lahir->age;
    }

    public static function generateNomorRM(): string
    {
        $tahun = date('Y');
        $bulan = date('m');
        $prefix = "RM{$tahun}{$bulan}";
        
        $lastRM = self::where('nomor_rm', 'like', $prefix . '%')
            ->orderBy('nomor_rm', 'desc')
            ->first();
        
        if ($lastRM) {
            $lastNumber = (int) substr($lastRM->nomor_rm, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
