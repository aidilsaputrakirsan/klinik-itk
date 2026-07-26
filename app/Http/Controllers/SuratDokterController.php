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
            'rekamMedis.pemeriksaan.tindakans',
            'rekamMedis.pemeriksaan.resepObat.obat',
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

        if ($suratDokter->isSuratSehat()) {
            $view = 'pdf.surat-sehat';
        } elseif ($suratDokter->isSuratRujukan()) {
            $view = 'pdf.surat-rujukan';
        } else {
            $view = 'pdf.surat-sakit';
        }

        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a4', 'portrait');

        $tanggal = \Carbon\Carbon::parse($suratDokter->tanggal_surat)->format('Y-m-d');

        if ($suratDokter->isSuratSehat()) {
            $filename = "Surat_Keterangan_Sehat_{$pasien->nama}_{$tanggal}.pdf";
        } elseif ($suratDokter->isSuratRujukan()) {
            $filename = "Surat_Rujukan_{$pasien->nama}_{$tanggal}.pdf";
        } else {
            $filename = "Surat_Keterangan_Sakit_{$pasien->nama}_{$tanggal}.pdf";
        }

        // Update printed_at
        $suratDokter->update(['printed_at' => now()]);

        return $pdf->download($filename);
    }

    public function previewPdf(SuratDokter $suratDokter)
    {
        $suratDokter->load([
            'rekamMedis.pasien',
            'rekamMedis.pemeriksaan',
            'rekamMedis.pemeriksaan.tindakans',
            'rekamMedis.pemeriksaan.resepObat.obat',
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

        if ($suratDokter->isSuratSehat()) {
            $view = 'pdf.surat-sehat';
        } elseif ($suratDokter->isSuratRujukan()) {
            $view = 'pdf.surat-rujukan';
        } else {
            $view = 'pdf.surat-sakit';
        }

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

        // Cek apakah nomor surat yang diformat sudah ada
        $exists = SuratDokter::where('nomor_surat', $formatNomor)
            ->where('id', '!=', $suratDokter->id)
            ->exists();

        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'nomor_input' => 'Nomor surat tersebut sudah digunakan.'
            ]);
        }

        $suratDokter->update([
            'nomor_surat' => $formatNomor
        ]);

        return redirect()->back()->with('success', 'Nomor surat berhasil disimpan.');
    }
}
