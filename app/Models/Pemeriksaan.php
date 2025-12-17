<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pemeriksaan extends Model
{
    use HasFactory;

    protected $table = 'pemeriksaans';

    protected $fillable = [
        'rekam_medis_id',
        'dokter_id',
        'pemeriksaan_fisik',
        'hasil_pemeriksaan',
        'diagnosis_utama',
        'diagnosis_sekunder',
        'kode_icd10',
        'prognosis',
        'anjuran',
        'hari_istirahat',
        'tanggal_mulai_istirahat',
        'tanggal_selesai_istirahat',
        'status_kesehatan',
        'catatan_kesehatan',
    ];

    protected function casts(): array
    {
        return [
            'hari_istirahat' => 'integer',
            'tanggal_mulai_istirahat' => 'date',
            'tanggal_selesai_istirahat' => 'date',
        ];
    }

    public function rekamMedis(): BelongsTo
    {
        return $this->belongsTo(RekamMedis::class);
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dokter_id');
    }

    public function tindakans(): BelongsToMany
    {
        return $this->belongsToMany(Tindakan::class, 'pemeriksaan_tindakans')
            ->withPivot(['jumlah', 'biaya', 'keterangan'])
            ->withTimestamps();
    }

    public function resepObats(): HasMany
    {
        return $this->hasMany(ResepObat::class);
    }
}
