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
            'rekamMedis.anamnesis',
            'dokter'
        ]);

        $pasien = $suratDokter->rekamMedis->pasien;
        $dokter = $suratDokter->dokter;
        $pemeriksaan = $suratDokter->rekamMedis->pemeriksaan;
        $anamnesis = $suratDokter->rekamMedis->anamnesis;

        $data = [
            'surat' => $suratDokter,
            'pasien' => $pasien,
            'dokter' => $dokter,
            'pemeriksaan' => $pemeriksaan,
            'anamnesis' => $anamnesis,
        ];

        $view = $suratDokter->isSuratSehat()
            ? 'pdf.surat-sehat'
            : 'pdf.surat-sakit';

        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a4', 'portrait');

        $tanggal = \Carbon\Carbon::parse($suratDokter->tanggal_surat)->format('Y-m-d');

        $filename = $suratDokter->isSuratSehat()
            ? "Surat_Keterangan_Sehat_{$pasien->nama}_{$tanggal}.pdf"
            : "Surat_Keterangan_Sakit_{$pasien->nama}_{$tanggal}.pdf";

        // Update printed_at
        $suratDokter->update(['printed_at' => now()]);

        return $pdf->download($filename);
    }

    public function previewPdf(SuratDokter $suratDokter)
    {
        $suratDokter->load([
            'rekamMedis.pasien',
            'rekamMedis.pemeriksaan',
            'rekamMedis.anamnesis',
            'dokter'
        ]);

        $pasien = $suratDokter->rekamMedis->pasien;
        $dokter = $suratDokter->dokter;
        $pemeriksaan = $suratDokter->rekamMedis->pemeriksaan;
        $anamnesis = $suratDokter->rekamMedis->anamnesis;

        $data = [
            'surat' => $suratDokter,
            'pasien' => $pasien,
            'dokter' => $dokter,
            'pemeriksaan' => $pemeriksaan,
            'anamnesis' => $anamnesis,
        ];

        $view = $suratDokter->isSuratSehat()
            ? 'pdf.surat-sehat'
            : 'pdf.surat-sakit';

        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream();
    }

    public function updateNomor(Request $request, SuratDokter $suratDokter)
    {
        $validated = $request->validate([
            'nomor_input' => 'required|numeric'
        ]);

        $tahun = date('Y');
        $formatNomor = "{$validated['nomor_input']}/IT10/TU.03/{$tahun}";

        $suratDokter->update([
            'nomor_surat' => $formatNomor
        ]);

        return redirect()->back()->with('success', 'Nomor surat berhasil disimpan.');
    }
}
