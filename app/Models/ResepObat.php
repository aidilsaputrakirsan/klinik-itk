<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResepObat extends Model
{
    use HasFactory;

    protected $table = 'resep_obats';

    protected $fillable = [
        'pemeriksaan_id',
        'obat_id',
        'nama_obat',
        'dosis',
        'aturan_pakai',
        'jumlah',
        'satuan',
        'is_racikan',
        'komposisi_racikan',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'is_racikan' => 'boolean',
            'komposisi_racikan' => 'array',
        ];
    }

    public function pemeriksaan(): BelongsTo
    {
        return $this->belongsTo(Pemeriksaan::class);
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->obat) {
            return $this->obat->nama;
        }
        return $this->nama_obat;
    }
}
