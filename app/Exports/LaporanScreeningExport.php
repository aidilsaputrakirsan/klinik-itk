<?php

namespace App\Exports;

use App\Models\RekamMedis;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanScreeningExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
        return RekamMedis::with(['pasien', 'anamnesis'])
            ->where('jenis_layanan', 'screening')
            ->whereBetween('tanggal_kunjungan', [$this->startDate, $this->endDate])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Timestamp',
            'Nama Pasien',
            'Umur',
            'J.K',
            'Status ITK',
            'Fakultas',
            'Unit Kerja',
            'Program Studi',
            'Tinggi Badan (cm)',
            'Berat Badan (kg)',
            'IMT',
            'Kategori IMT',
            'Lingkar Perut (cm)',
            'Status LP',
            'Tensi Sistolik/Diastolik',
            'Kategori Tekanan Darah',
            'Gula Darah (mg/dL)',
            'Kategori Gula Darah',
            'Asam Urat (mg/dL)',
            'Kategori Asam Urat',
            'Kolesterol (mg/dL)',
            'Kategori Kolesterol',
            'Hemoglobin (g/dL)',
            'Kategori Hemoglobin',
            'Keterangan',
            'Tindak Lanjut',
        ];
    }

    public function map($rekamMedis): array
    {
        static $no = 0;
        $no++;

        $anamnesis = $rekamMedis->anamnesis;
        $pasien = $rekamMedis->pasien;

        // Helpers
        $getStatusLabel = function($status) {
            $labels = ['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'tendik' => 'Tendik', 'umum' => 'Umum'];
            return $labels[$status] ?? $status ?? '-';
        };

        $getBmiData = function($tb, $bb) {
            if (!$tb || !$bb) return ['value' => '-', 'category' => '-'];
            $h = $tb / 100;
            $bmi = $bb / ($h * $h);
            $category = '';
            if ($bmi < 18) $category = 'Underweight (<18)';
            elseif ($bmi <= 22.9) $category = 'Normal (18-22.9)';
            elseif ($bmi <= 24.9) $category = 'Overweight (23-24.9)';
            elseif ($bmi <= 29.9) $category = 'Obesitas Tingkat 1 (>25-29.9)';
            else $category = 'Obesitas Tingkat 2 (>30)';
            return ['value' => number_format($bmi, 2), 'category' => $category];
        };

        $getLpData = function($lp, $isHamil, $gender) {
            if ($isHamil) return ['value' => $lp ?: '-', 'status' => 'Hamil'];
            if (!$lp) return ['value' => '-', 'status' => '-'];
            $isCritical = false;
            if ($gender === 'L' && $lp > 90) $isCritical = true;
            if ($gender === 'P' && $lp > 80) $isCritical = true;
            return ['value' => $lp, 'status' => $isCritical ? 'Obesitas Sentral' : 'Normal'];
        };

        $getTdData = function($td) {
            if (!$td) return ['value' => '-', 'category' => '-'];
            $parts = explode('/', $td);
            if (count($parts) !== 2) return ['value' => $td, 'category' => '-'];
            $sys = (int)$parts[0];
            $dia = (int)$parts[1];
            $category = '';
            if ($sys < 130 && $dia < 85) $category = 'Normal (<129/84)';
            elseif ($sys <= 139 || $dia <= 89) $category = 'Prehipertensi (130/85-139/89)';
            elseif ($sys <= 159 || $dia <= 99) $category = 'Hipertensi Grade 1 (140/90-159/99)';
            else $category = 'Hipertensi Grade 2 (>160/100)';
            return ['value' => $td, 'category' => $category];
        };

        $getGdData = function($gd, $jenis) {
            if (!$gd) return ['value' => '-', 'category' => '-'];
            $category = 'Normal';
            if ($jenis === 'puasa') {
                if ($gd > 120) $category = 'Hiperglikemia (GDP: >120)';
            } else {
                if ($gd > 200) $category = 'Hiperglikemia (GDS: >200)';
            }
            return ['value' => $gd, 'category' => $category];
        };

        $getAuData = function($au, $gender) {
            if (!$au) return ['value' => '-', 'category' => '-'];
            $category = 'Normal';
            if ($gender === 'L' && $au > 7) $category = 'Hiperuricemia (L: >7)';
            if ($gender === 'P' && $au > 6) $category = 'Hiperuricemia (P: >6)';
            return ['value' => $au, 'category' => $category];
        };

        $getCholData = function($chol) {
            if (!$chol) return ['value' => '-', 'category' => '-'];
            return ['value' => $chol, 'category' => $chol > 200 ? 'Hipercholesterolemia (>200)' : 'Normal'];
        };

        $getHbData = function($hb) {
            if (!$hb) return ['value' => '-', 'category' => '-'];
            return ['value' => $hb, 'category' => $hb < 12 ? 'Anemia (< 12)' : 'Normal'];
        };

        $gender = $pasien ? $pasien->jenis_kelamin : null;
        $bmi = $getBmiData($anamnesis ? $anamnesis->tinggi_badan : null, $anamnesis ? $anamnesis->berat_badan : null);
        $lp = $getLpData($anamnesis ? $anamnesis->lingkar_perut : null, $anamnesis ? $anamnesis->is_hamil : false, $gender);
        $td = $getTdData($anamnesis ? $anamnesis->tekanan_darah : null);
        $gd = $getGdData($anamnesis ? $anamnesis->gula_darah : null, $anamnesis ? $anamnesis->jenis_gula_darah : null);
        $au = $getAuData($anamnesis ? $anamnesis->asam_urat : null, $gender);
        $chol = $getCholData($anamnesis ? $anamnesis->kolesterol : null);
        $hb = $getHbData($anamnesis ? $anamnesis->hemoglobin : null);

        $tindakLanjutLabel = 'Belum Ada';
        if ($anamnesis && $anamnesis->tindak_lanjut) {
            if ($anamnesis->tindak_lanjut === 'rujuk') $tindakLanjutLabel = 'Kembali ke Faskes 1';
            elseif ($anamnesis->tindak_lanjut === 'rawat_jalan') $tindakLanjutLabel = 'Rawat Jalan';
            elseif ($anamnesis->tindak_lanjut === 'edukasi') $tindakLanjutLabel = 'Edukasi';
        }

        return [
            $no,
            $rekamMedis->tanggal_kunjungan ? $rekamMedis->tanggal_kunjungan->format('d/m/Y H:i') : '-',
            $pasien ? $pasien->nama : '-',
            $pasien ? $pasien->umur . ' Thn' : '-',
            $pasien ? ($pasien->jenis_kelamin === 'L' ? 'L' : 'P') : '-',
            $pasien ? $getStatusLabel($pasien->tipe_pasien) : '-',
            $pasien && in_array($pasien->tipe_pasien, ['mahasiswa', 'dosen']) ? ($pasien->fakultas ?: '-') : '-',
            $pasien && $pasien->tipe_pasien === 'tendik' ? ($pasien->fakultas ?: '-') : '-',
            $pasien && in_array($pasien->tipe_pasien, ['mahasiswa', 'dosen']) ? ($pasien->prodi ?: '-') : '-',
            $anamnesis ? $anamnesis->tinggi_badan : '-',
            $anamnesis ? $anamnesis->berat_badan : '-',
            $bmi['value'],
            $bmi['category'],
            $lp['value'],
            $lp['status'],
            $td['value'],
            $td['category'],
            $gd['value'],
            $gd['category'],
            $au['value'],
            $au['category'],
            $chol['value'],
            $chol['category'],
            $hb['value'],
            $hb['category'],
            $anamnesis ? $anamnesis->keterangan_tindak_lanjut : '-',
            $tindakLanjutLabel,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
