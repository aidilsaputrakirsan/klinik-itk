<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RekamMedis extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rekam_medis';

    protected $fillable = [
        'nomor_kunjungan',
        'pasien_id',
        'perawat_id',
        'dokter_id',
        'tanggal_kunjungan',
        'jenis_layanan',
        'keperluan_surat',
        'nama_event',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kunjungan' => 'date',
        ];
    }

    const STATUS_MENUNGGU_PERAWAT = 'menunggu_perawat';
    const STATUS_PROSES_ANAMNESIS = 'proses_anamnesis';
    const STATUS_SIAP_DOKTER = 'siap_dokter';
    const STATUS_SEDANG_DIPERIKSA = 'sedang_diperiksa';
    const STATUS_SELESAI = 'selesai';
    const STATUS_BATAL = 'batal';

    public static function getStatusList(): array
    {
        return [
            self::STATUS_MENUNGGU_PERAWAT => 'Menunggu Perawat',
            self::STATUS_PROSES_ANAMNESIS => 'Proses Anamnesis',
            self::STATUS_SIAP_DOKTER => 'Siap untuk Dokter',
            self::STATUS_SEDANG_DIPERIKSA => 'Sedang Diperiksa',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_BATAL => 'Batal',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? $this->status;
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function perawat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'perawat_id');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dokter_id');
    }

    public function anamnesis(): HasOne
    {
        return $this->hasOne(Anamnesis::class);
    }

    public function pemeriksaan(): HasOne
    {
        return $this->hasOne(Pemeriksaan::class);
    }

    public function suratDokters(): HasMany
    {
        return $this->hasMany(SuratDokter::class);
    }

    public function suratDokter(): HasOne
    {
        return $this->hasOne(SuratDokter::class);
    }

    public static function generateNomorKunjungan(): string
    {
        $tanggal = date('Ymd');
        $prefix = "KNJ{$tanggal}";
        
        $lastKunjungan = self::where('nomor_kunjungan', 'like', $prefix . '%')
            ->orderBy('nomor_kunjungan', 'desc')
            ->first();
        
        if ($lastKunjungan) {
            $lastNumber = (int) substr($lastKunjungan->nomor_kunjungan, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
