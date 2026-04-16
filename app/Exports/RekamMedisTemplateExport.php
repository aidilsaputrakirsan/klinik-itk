<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RekamMedisTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Tanggal Kunjungan', 'Jenis Layanan', 'Catatan Kunjungan',
            'Perawat', 'Tekanan Darah', 'Suhu', 'Nadi', 'Respirasi', 
            'Tinggi Badan', 'Berat Badan', 'Skala Nyeri', 'Keluhan Utama', 
            'Riwayat Penyakit Sekarang', 'Riwayat Penyakit Dahulu', 'Riwayat Keluarga', 
            'Riwayat Alergi', 'Riwayat Obat', 
            'Diagnosa Keperawatan', 'Intervensi Keperawatan', 'Implementasi Keperawatan', 'Evaluasi Keperawatan',
            'Dokter', 'Pemeriksaan Fisik', 'Hasil Pemeriksaan', 'Diagnosis Utama', 
            'Diagnosis Sekunder', 'Kode ICD10', 'Prognosis', 'Anjuran', 'Penatalaksanaan Medis'
        ];
    }

    public function array(): array
    {
        return [
            [
                '2026-04-16', 'berobat', 'Tinggalkan kosong bila tidak ada',
                'Contoh Perawat B', '120/80', 36.5, 80, 20,
                170, 65, 0, 'Sakit kepala ringan',
                '-', '-', '-', 
                'Tidak ada', '-', 
                '-', '-', '-', '-',
                'Contoh Dokter C', 'Normal', 'Dalam batas normal', 'Tension Headache', 
                '-', 'G44.2', 'Baik', 'Banyak minum air', 'Paracetamol 500mg'
            ]
        ];
    }
}
