<?php

namespace Database\Seeders;

use App\Models\Obat;
use Illuminate\Database\Seeder;

class ObatSeeder extends Seeder
{
    public function run(): void
    {
        $obats = [
            ['kode' => 'OBT0001', 'nama' => 'Paracetamol 500mg', 'satuan' => 'tablet', 'jenis' => 'tablet', 'stok' => 500, 'stok_minimum' => 50, 'harga' => 500],
            ['kode' => 'OBT0002', 'nama' => 'Amoxicillin 500mg', 'satuan' => 'kapsul', 'jenis' => 'kapsul', 'stok' => 300, 'stok_minimum' => 30, 'harga' => 2000],
            ['kode' => 'OBT0003', 'nama' => 'Ibuprofen 400mg', 'satuan' => 'tablet', 'jenis' => 'tablet', 'stok' => 200, 'stok_minimum' => 20, 'harga' => 1500],
            ['kode' => 'OBT0004', 'nama' => 'Omeprazole 20mg', 'satuan' => 'kapsul', 'jenis' => 'kapsul', 'stok' => 150, 'stok_minimum' => 15, 'harga' => 3000],
            ['kode' => 'OBT0005', 'nama' => 'Cetirizine 10mg', 'satuan' => 'tablet', 'jenis' => 'tablet', 'stok' => 250, 'stok_minimum' => 25, 'harga' => 1000],
            ['kode' => 'OBT0006', 'nama' => 'Vitamin C 500mg', 'satuan' => 'tablet', 'jenis' => 'tablet', 'stok' => 400, 'stok_minimum' => 40, 'harga' => 500],
            ['kode' => 'OBT0007', 'nama' => 'Antasida Sirup', 'satuan' => 'botol', 'jenis' => 'sirup', 'stok' => 50, 'stok_minimum' => 10, 'harga' => 15000],
            ['kode' => 'OBT0008', 'nama' => 'OBH Combi', 'satuan' => 'botol', 'jenis' => 'sirup', 'stok' => 60, 'stok_minimum' => 10, 'harga' => 20000],
            ['kode' => 'OBT0009', 'nama' => 'Salep 2-4', 'satuan' => 'tube', 'jenis' => 'salep', 'stok' => 30, 'stok_minimum' => 5, 'harga' => 10000],
            ['kode' => 'OBT0010', 'nama' => 'Betadine', 'satuan' => 'botol', 'jenis' => 'lainnya', 'stok' => 40, 'stok_minimum' => 10, 'harga' => 25000],
            ['kode' => 'OBT0011', 'nama' => 'Metformin 500mg', 'satuan' => 'tablet', 'jenis' => 'tablet', 'stok' => 200, 'stok_minimum' => 20, 'harga' => 1000],
            ['kode' => 'OBT0012', 'nama' => 'Amlodipine 5mg', 'satuan' => 'tablet', 'jenis' => 'tablet', 'stok' => 180, 'stok_minimum' => 18, 'harga' => 1500],
            ['kode' => 'OBT0013', 'nama' => 'Lansoprazole 30mg', 'satuan' => 'kapsul', 'jenis' => 'kapsul', 'stok' => 100, 'stok_minimum' => 10, 'harga' => 3500],
            ['kode' => 'OBT0014', 'nama' => 'Domperidone 10mg', 'satuan' => 'tablet', 'jenis' => 'tablet', 'stok' => 150, 'stok_minimum' => 15, 'harga' => 800],
            ['kode' => 'OBT0015', 'nama' => 'Loratadine 10mg', 'satuan' => 'tablet', 'jenis' => 'tablet', 'stok' => 200, 'stok_minimum' => 20, 'harga' => 1200],
        ];

        foreach ($obats as $obat) {
            Obat::create($obat);
        }
    }
}
