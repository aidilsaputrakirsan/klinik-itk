<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Obat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'obats';

    protected $fillable = [
        'kode',
        'nama',
        'satuan',
        'jenis',
        'stok',
        'stok_minimum',
        'harga',
        'keterangan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stok' => 'integer',
            'stok_minimum' => 'integer',
            'harga' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function resepObats(): HasMany
    {
        return $this->hasMany(ResepObat::class);
    }

    public function isStokRendah(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }

    public static function generateKode(): string
    {
        $lastObat = self::orderBy('id', 'desc')->first();
        $newNumber = $lastObat ? $lastObat->id + 1 : 1;
        return 'OBT' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
