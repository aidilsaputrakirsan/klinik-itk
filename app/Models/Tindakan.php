<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tindakan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tindakans';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'biaya',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'biaya' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function pemeriksaans(): BelongsToMany
    {
        return $this->belongsToMany(Pemeriksaan::class, 'pemeriksaan_tindakans')
            ->withPivot(['jumlah', 'biaya', 'keterangan'])
            ->withTimestamps();
    }

    public static function generateKode(): string
    {
        $lastTindakan = self::orderBy('id', 'desc')->first();
        $newNumber = $lastTindakan ? $lastTindakan->id + 1 : 1;
        return 'TDK' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
