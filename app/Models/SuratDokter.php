<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratDokter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'surat_dokters';

    protected $fillable = [
        'nomor_surat',
        'rekam_medis_id',
        'dokter_id',
        'jenis_surat',
        'tanggal_surat',
        'keperluan',
        'jumlah_hari_istirahat',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'file_path',
        'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'jumlah_hari_istirahat' => 'integer',
            'printed_at' => 'datetime',
        ];
    }

    const JENIS_SEHAT = 'surat_sehat';
    const JENIS_SAKIT = 'surat_sakit';

    public function rekamMedis(): BelongsTo
    {
        return $this->belongsTo(RekamMedis::class);
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dokter_id');
    }

    public function isSuratSehat(): bool
    {
        return $this->jenis_surat === self::JENIS_SEHAT;
    }

    public function isSuratSakit(): bool
    {
        return $this->jenis_surat === self::JENIS_SAKIT;
    }

    public static function generateNomorSurat(string $jenis): string
    {
        $tahun = date('Y');
        $bulan = date('m');
        $prefix = $jenis === self::JENIS_SEHAT ? 'SKS' : 'SKK';
        $fullPrefix = "{$prefix}/{$bulan}/{$tahun}";
        
        $lastSurat = self::where('nomor_surat', 'like', "%/{$bulan}/{$tahun}/%")
            ->where('jenis_surat', $jenis)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastSurat) {
            $parts = explode('/', $lastSurat->nomor_surat);
            $lastNumber = (int) end($parts);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "{$fullPrefix}/" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
