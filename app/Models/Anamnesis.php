<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anamnesis extends Model
{
    use HasFactory;

    protected $table = 'anamnesis';

    protected $fillable = [
        'rekam_medis_id',
        'perawat_id',
        'tekanan_darah',
        'suhu',
        'nadi',
        'respirasi',
        'berat_badan',
        'tinggi_badan',
        'keluhan_utama',
        'riwayat_penyakit_sekarang',
        'riwayat_penyakit_dahulu',
        'riwayat_alergi',
        'riwayat_obat',
    ];

    protected function casts(): array
    {
        return [
            'suhu' => 'decimal:1',
            'nadi' => 'integer',
            'respirasi' => 'integer',
            'berat_badan' => 'decimal:2',
            'tinggi_badan' => 'decimal:2',
        ];
    }

    public function rekamMedis(): BelongsTo
    {
        return $this->belongsTo(RekamMedis::class);
    }

    public function perawat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'perawat_id');
    }

    public function getBmiAttribute(): ?float
    {
        if (!$this->berat_badan || !$this->tinggi_badan) {
            return null;
        }
        $tinggiMeter = $this->tinggi_badan / 100;
        return round($this->berat_badan / ($tinggiMeter * $tinggiMeter), 2);
    }

    public function getBmiCategoryAttribute(): ?string
    {
        $bmi = $this->bmi;
        if (!$bmi) {
            return null;
        }
        
        if ($bmi < 18.5) return 'Kurus';
        if ($bmi < 25) return 'Normal';
        if ($bmi < 30) return 'Gemuk';
        return 'Obesitas';
    }
}
