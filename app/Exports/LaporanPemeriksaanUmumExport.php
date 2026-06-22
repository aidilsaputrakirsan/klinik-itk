<?php

namespace App\Exports;

use App\Models\RekamMedis;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPemeriksaanUmumExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return RekamMedis::with(['pasien', 'anamnesis', 'pemeriksaan', 'pemeriksaan.tindakans'])
            ->has('pasien')
            ->where('jenis_layanan', 'berobat')
            ->whereBetween('tanggal_kunjungan', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->orderBy('tanggal_kunjungan', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Timestamp',
            'No. RM',
            'Nama Pasien',
            'Tanggal Lahir',
            'No. Identitas (NIK/NIP/NIM)',
            'No. Telp',
            'Jenis Kelamin',
            'Alamat',
            'Agama',
            'Status Perkawinan',
            'Pendidikan terakhir',
            'Status di ITK',
            'Fakultas',
            'Unit Kerja',
            'Program Studi',
            'Golongan Darah',
            'Petugas Administrasi',
            'Keluhan Utama',
            'Riwayat penyakit sekarang',
            'Riwayat penyakit dahulu',
            'Riwayat Keluarga',
            'Alergi',
            'TTV.1 TD',
            'TTV.2 Nadi',
            'TTV.3 Suhu',
            'TTV.4 RR',
            'Berat Badan',
            'Tinggi Badan',
            'IMT',
            'Kondisi Khusus',
            'Skala Nyeri',
            'Pemeriksaan Fisik Lain',
            'Dokter penanggung jawab',
            'Diagnosa medis',
            'Penatalaksanaan Medis',
            'Diagnosa Keperawatan',
            'Intervensi Keperawatan',
            'Implementasi Keperawatan',
            'Evaluasi Keperawatan',
            'Perawat',
        ];
    }

    public function map($rekamMedis): array
    {
        $pasien = $rekamMedis->pasien;
        $anamnesis = $rekamMedis->anamnesis;
        $pemeriksaan = $rekamMedis->pemeriksaan;

        $imt = '-';
        if ($anamnesis && $anamnesis->berat_badan && $anamnesis->tinggi_badan) {
            $imt = number_format($anamnesis->berat_badan / pow($anamnesis->tinggi_badan / 100, 2), 2);
        }

        $formatText = function($text) {
            if (!$text) return '-';
            return ucwords(strtolower(str_replace('_', ' ', $text)));
        };

        $getStatusLabel = function($status) {
            $labels = [
                'mahasiswa' => 'Mahasiswa',
                'dosen' => 'Dosen',
                'tendik' => 'Tendik',
                'umum' => 'Umum'
            ];
            return $labels[$status] ?? $status ?? '-';
        };

        return [
            $rekamMedis->tanggal_kunjungan ? $rekamMedis->tanggal_kunjungan->format('d/m/Y H:i') : '-',
            $pasien ? $pasien->nomor_rm : '-',
            $pasien ? $pasien->nama : '-',
            $pasien && $pasien->tanggal_lahir ? $pasien->tanggal_lahir : '-',
            $pasien ? ($pasien->nik ?: ($pasien->nomor_identitas ?: '-')) : '-',
            $pasien ? $pasien->phone : '-',
            $pasien ? ($pasien->jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan') : '-',
            $pasien ? $pasien->alamat : '-',
            $pasien ? $formatText($pasien->agama) : '-',
            $pasien ? $formatText($pasien->status_perkawinan) : '-',
            $pasien ? $formatText($pasien->pendidikan_terakhir) : '-',
            $pasien ? $getStatusLabel($pasien->tipe_pasien) : '-',
            $pasien && in_array($pasien->tipe_pasien, ['mahasiswa', 'dosen']) ? ($pasien->fakultas ?: '-') : '-',
            $pasien && $pasien->tipe_pasien === 'tendik' ? ($pasien->fakultas ?: '-') : '-',
            $pasien && in_array($pasien->tipe_pasien, ['mahasiswa', 'dosen']) ? ($pasien->prodi ?: '-') : '-',
            $pasien ? $pasien->golongan_darah : '-',
            'Admin / Sistem',
            $anamnesis ? $anamnesis->keluhan_utama : '-',
            $anamnesis ? $anamnesis->riwayat_penyakit_sekarang : '-',
            $anamnesis ? $anamnesis->riwayat_penyakit_dahulu : '-',
            $anamnesis ? $anamnesis->riwayat_keluarga : '-',
            $anamnesis ? $anamnesis->riwayat_alergi : '-',
            $anamnesis ? $anamnesis->tekanan_darah : '-',
            $anamnesis ? $anamnesis->nadi : '-',
            $anamnesis ? $anamnesis->suhu : '-',
            $anamnesis ? $anamnesis->respirasi : '-',
            $anamnesis ? $anamnesis->berat_badan : '-',
            $anamnesis ? $anamnesis->tinggi_badan : '-',
            $imt,
            $pasien && $pasien->jenis_kelamin === 'P' && $anamnesis ? ($anamnesis->is_hamil ? 'Hamil' : ($anamnesis->is_menyusui ? 'Menyusui' : '-')) : '-',
            $anamnesis ? $anamnesis->skala_nyeri : '-',
            $pemeriksaan ? $pemeriksaan->pemeriksaan_fisik : '-',
            $pemeriksaan && $pemeriksaan->dokter ? $pemeriksaan->dokter->name : '-',
            $pemeriksaan ? $pemeriksaan->diagnosis_utama : '-',
            $pemeriksaan ? $pemeriksaan->penatalaksanaan_medis : '-',
            $anamnesis ? $anamnesis->diagnosa_keperawatan : '-',
            $anamnesis ? $anamnesis->intervensi_keperawatan : '-',
            $anamnesis ? $anamnesis->implementasi_keperawatan : '-',
            $anamnesis ? $anamnesis->evaluasi_keperawatan : '-',
            $anamnesis && $anamnesis->perawat ? $anamnesis->perawat->name : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
