<?php

namespace App\Http\Controllers;

use App\Models\SuratDokter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SuratDokterController extends Controller
{
    public function generatePdf(SuratDokter $suratDokter)
    {
        $suratDokter->load([
            'rekamMedis.pasien',
            'rekamMedis.pemeriksaan',
            'dokter'
        ]);

        $pasien = $suratDokter->rekamMedis->pasien;
        $dokter = $suratDokter->dokter;
        $pemeriksaan = $suratDokter->rekamMedis->pemeriksaan;

        $data = [
            'surat' => $suratDokter,
            'pasien' => $pasien,
            'dokter' => $dokter,
            'pemeriksaan' => $pemeriksaan,
        ];

        $view = $suratDokter->isSuratSehat()
            ? 'pdf.surat-sehat'
            : 'pdf.surat-sakit';

        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a4', 'portrait');

        $filename = $suratDokter->isSuratSehat()
            ? "Surat_Keterangan_Sehat_{$pasien->nama}_{$suratDokter->tanggal_surat->format('Y-m-d')}.pdf"
            : "Surat_Keterangan_Sakit_{$pasien->nama}_{$suratDokter->tanggal_surat->format('Y-m-d')}.pdf";

        // Update printed_at
        $suratDokter->update(['printed_at' => now()]);

        return $pdf->download($filename);
    }

    public function previewPdf(SuratDokter $suratDokter)
    {
        $suratDokter->load([
            'rekamMedis.pasien',
            'rekamMedis.pemeriksaan',
            'dokter'
        ]);

        $pasien = $suratDokter->rekamMedis->pasien;
        $dokter = $suratDokter->dokter;
        $pemeriksaan = $suratDokter->rekamMedis->pemeriksaan;

        $data = [
            'surat' => $suratDokter,
            'pasien' => $pasien,
            'dokter' => $dokter,
            'pemeriksaan' => $pemeriksaan,
        ];

        $view = $suratDokter->isSuratSehat()
            ? 'pdf.surat-sehat'
            : 'pdf.surat-sakit';

        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream();
    }
}
