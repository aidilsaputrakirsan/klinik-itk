<?php

namespace App\Http\Controllers;

use App\Models\RekamMedis;
use Illuminate\Http\Request;

class RekamMedisController extends Controller
{
    public function updateAnamnesis(Request $request, RekamMedis $rekamMedis)
    {
        $validated = $request->validate([
            'tekanan_darah' => 'nullable|string|max:20',
            'suhu' => 'nullable|numeric',
            'nadi' => 'nullable|integer',
            'respirasi' => 'nullable|integer',
            'tinggi_badan' => 'nullable|numeric',
            'berat_badan' => 'nullable|numeric',
            'skala_nyeri' => 'nullable|integer',
            'keluhan_utama' => 'nullable|string',
            'riwayat_penyakit_sekarang' => 'nullable|string',
            'riwayat_penyakit_dahulu' => 'nullable|string',
            'riwayat_keluarga' => 'nullable|string',
            'riwayat_alergi' => 'nullable|string',
            'riwayat_obat' => 'nullable|string',
            'diagnosa_keperawatan' => 'nullable|string',
            'intervensi_keperawatan' => 'nullable|string',
            'implementasi_keperawatan' => 'nullable|string',
            'evaluasi_keperawatan' => 'nullable|string',
        ]);

        if ($rekamMedis->anamnesis) {
            $rekamMedis->anamnesis->update($validated);
        } else {
            $rekamMedis->anamnesis()->create(array_merge($validated, ['perawat_id' => auth()->id()]));
        }

        if (!$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax())) {
            return response()->json(['status' => 'success', 'message' => 'Data anamnesis berhasil diperbarui.']);
        }

        return redirect()->back()->with('success', 'Data anamnesis & keperawatan berhasil diperbarui.');
    }

    public function updatePemeriksaan(Request $request, RekamMedis $rekamMedis)
    {
        $validated = $request->validate([
            'diagnosis_utama' => 'nullable|string',
            'diagnosis_sekunder' => 'nullable|string',
            'kode_icd10' => 'nullable|string',
            'pemeriksaan_fisik' => 'nullable|string',
            'hasil_pemeriksaan' => 'nullable|string',
            'penatalaksanaan_medis' => 'nullable|string',
            'prognosis' => 'nullable|string',
            'anjuran' => 'nullable|string',
        ]);

        if ($rekamMedis->pemeriksaan) {
            $rekamMedis->pemeriksaan->update($validated);
        } else {
            $rekamMedis->pemeriksaan()->create(array_merge($validated, ['dokter_id' => auth()->id()]));
        }

        if (!$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax())) {
            return response()->json(['status' => 'success', 'message' => 'Data pemeriksaan diperbarui.']);
        }

        return redirect()->back()->with('success', 'Data pemeriksaan rekam medis berhasil diperbarui.');
    }
}
