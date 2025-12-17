<?php

namespace Database\Seeders;

use App\Models\Tindakan;
use Illuminate\Database\Seeder;

class TindakanSeeder extends Seeder
{
    public function run(): void
    {
        $tindakans = [
            ['kode' => 'TDK0001', 'nama' => 'Konsultasi Dokter Umum', 'deskripsi' => 'Konsultasi dengan dokter umum', 'biaya' => 0],
            ['kode' => 'TDK0002', 'nama' => 'Pemeriksaan Tekanan Darah', 'deskripsi' => 'Pengukuran tekanan darah', 'biaya' => 0],
            ['kode' => 'TDK0003', 'nama' => 'Pemeriksaan Gula Darah', 'deskripsi' => 'Tes gula darah sewaktu', 'biaya' => 15000],
            ['kode' => 'TDK0004', 'nama' => 'Pemeriksaan Kolesterol', 'deskripsi' => 'Tes kolesterol darah', 'biaya' => 25000],
            ['kode' => 'TDK0005', 'nama' => 'Injeksi Obat', 'deskripsi' => 'Pemberian obat melalui suntikan', 'biaya' => 10000],
            ['kode' => 'TDK0006', 'nama' => 'Perawatan Luka', 'deskripsi' => 'Pembersihan dan perawatan luka', 'biaya' => 15000],
            ['kode' => 'TDK0007', 'nama' => 'Nebulizer', 'deskripsi' => 'Terapi uap untuk pernapasan', 'biaya' => 20000],
            ['kode' => 'TDK0008', 'nama' => 'Pemasangan Infus', 'deskripsi' => 'Pemasangan jalur infus intravena', 'biaya' => 50000],
            ['kode' => 'TDK0009', 'nama' => 'EKG', 'deskripsi' => 'Pemeriksaan jantung dengan elektrokardiogram', 'biaya' => 50000],
            ['kode' => 'TDK0010', 'nama' => 'Jahit Luka', 'deskripsi' => 'Penjahitan luka', 'biaya' => 75000],
            ['kode' => 'TDK0011', 'nama' => 'Cabut Jahitan', 'deskripsi' => 'Pencabutan benang jahit', 'biaya' => 25000],
            ['kode' => 'TDK0012', 'nama' => 'Pemeriksaan Asam Urat', 'deskripsi' => 'Tes asam urat darah', 'biaya' => 20000],
        ];

        foreach ($tindakans as $tindakan) {
            Tindakan::create($tindakan);
        }
    }
}
