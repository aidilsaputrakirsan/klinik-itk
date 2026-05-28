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
            ->orderBy('tanggal_kunjungan', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Kunjungan',
            'Tanggal Kunjungan',
            'No. RM',
            'Nama Pasien',
            'Jenis Kelamin',
            'Usia',
            'Tinggi Badan (cm)',
            'Berat Badan (kg)',
            'Tekanan Darah (mmHg)',
            'Suhu (°C)',
            'Nadi (x/mnt)',
            'Respirasi (x/mnt)',
            'Lingkar Perut (cm)',
            'Buta Warna',
            'Jenis Gula Darah',
            'Gula Darah (mg/dL)',
            'Asam Urat (mg/dL)',
            'Kolesterol (mg/dL)',
            'Hemoglobin (g/dL)',
        ];
    }

    public function map($rekamMedis): array
    {
        static $no = 0;
        $no++;

        $anamnesis = $rekamMedis->anamnesis;

        return [
            $no,
            $rekamMedis->nomor_kunjungan,
            $rekamMedis->tanggal_kunjungan ? $rekamMedis->tanggal_kunjungan->format('d/m/Y') : '-',
            $rekamMedis->pasien ? $rekamMedis->pasien->no_rm : '-',
            $rekamMedis->pasien ? $rekamMedis->pasien->nama : '-',
            $rekamMedis->pasien ? $rekamMedis->pasien->jenis_kelamin : '-',
            $rekamMedis->pasien ? $rekamMedis->pasien->usia . ' thn' : '-',
            $anamnesis ? $anamnesis->tinggi_badan : '-',
            $anamnesis ? $anamnesis->berat_badan : '-',
            $anamnesis ? $anamnesis->tekanan_darah : '-',
            $anamnesis ? $anamnesis->suhu : '-',
            $anamnesis ? $anamnesis->nadi : '-',
            $anamnesis ? $anamnesis->respirasi : '-',
            $anamnesis ? $anamnesis->lingkar_perut : '-',
            $anamnesis ? $anamnesis->buta_warna : '-',
            $anamnesis ? $anamnesis->jenis_gula_darah : '-',
            $anamnesis ? $anamnesis->gula_darah : '-',
            $anamnesis ? $anamnesis->asam_urat : '-',
            $anamnesis ? $anamnesis->kolesterol : '-',
            $anamnesis ? $anamnesis->hemoglobin : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
