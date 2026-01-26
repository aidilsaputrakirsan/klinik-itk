<?php

namespace App\Http\Controllers;

use App\Models\Anamnesis;
use App\Models\RekamMedis;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PerawatController extends Controller
{
    public function antrian()
    {
        $antrian = RekamMedis::with(['pasien'])
            ->whereIn('status', [RekamMedis::STATUS_MENUNGGU_PERAWAT, RekamMedis::STATUS_PROSES_ANAMNESIS])
            ->whereDate('tanggal_kunjungan', today())
            ->orderBy('created_at', 'asc')
            ->get();

        return Inertia::render('Perawat/Antrian', [
            'antrian' => $antrian,
        ]);
    }

    public function storeAnamnesis(Request $request)
    {
        $validated = $request->validate([
            'rekam_medis_id' => 'required|exists:rekam_medis,id',
            'tekanan_darah_sistolik' => 'nullable|integer|min:50|max:250',
            'tekanan_darah_diastolik' => 'nullable|integer|min:30|max:150',
            'suhu' => 'nullable|numeric|min:30|max:45',
            'nadi' => 'nullable|integer|min:30|max:200',
            'respirasi' => 'nullable|integer|min:10|max:60',
            'tinggi_badan' => 'nullable|numeric|min:50|max:250',
            'berat_badan' => 'nullable|numeric|min:1|max:300',
            'keluhan_utama' => 'required|string',
            'riwayat_penyakit_sekarang' => 'nullable|string',
            'riwayat_penyakit_dahulu' => 'nullable|string',
            'riwayat_alergi' => 'nullable|string',
            'riwayat_obat' => 'nullable|string',
            'riwayat_keluarga' => 'nullable|string',
            'skala_nyeri' => 'nullable|integer|min:0|max:10',
            'diagnosa_keperawatan' => 'nullable|string',
            'intervensi_keperawatan' => 'nullable|string',
            'implementasi_keperawatan' => 'nullable|string',
            'evaluasi_keperawatan' => 'nullable|string',
        ], [
            'tekanan_darah_sistolik.min' => 'Tekanan darah sistolik minimal 50 mmHg',
            'tekanan_darah_sistolik.max' => 'Tekanan darah sistolik maksimal 250 mmHg',
            'tekanan_darah_sistolik.integer' => 'Tekanan darah sistolik harus berupa angka bulat',
            'tekanan_darah_diastolik.min' => 'Tekanan darah diastolik minimal 30 mmHg',
            'tekanan_darah_diastolik.max' => 'Tekanan darah diastolik maksimal 150 mmHg',
            'tekanan_darah_diastolik.integer' => 'Tekanan darah diastolik harus berupa angka bulat',
            'suhu.min' => 'Suhu tubuh minimal 30°C',
            'suhu.max' => 'Suhu tubuh maksimal 45°C',
            'nadi.min' => 'Nadi minimal 30x/menit',
            'nadi.max' => 'Nadi maksimal 200x/menit',
            'nadi.integer' => 'Nadi harus berupa angka bulat',
            'respirasi.min' => 'Respirasi minimal 10x/menit',
            'respirasi.max' => 'Respirasi maksimal 60x/menit',
            'respirasi.integer' => 'Respirasi harus berupa angka bulat',
            'tinggi_badan.min' => 'Tinggi badan minimal 50 cm',
            'tinggi_badan.max' => 'Tinggi badan maksimal 250 cm',
            'berat_badan.min' => 'Berat badan minimal 1 kg',
            'berat_badan.max' => 'Berat badan maksimal 300 kg',
            'keluhan_utama.required' => 'Keluhan utama wajib diisi',
        ]);

        // Format tekanan darah menjadi string "sistolik/diastolik"
        $tekananDarah = null;
        if ($validated['tekanan_darah_sistolik'] && $validated['tekanan_darah_diastolik']) {
            $tekananDarah = $validated['tekanan_darah_sistolik'] . '/' . $validated['tekanan_darah_diastolik'];
        }

        Anamnesis::create([
            'rekam_medis_id' => $validated['rekam_medis_id'],
            'perawat_id' => auth()->id(),
            'tekanan_darah' => $tekananDarah,
            'suhu' => $validated['suhu'] ?? null,
            'nadi' => $validated['nadi'] ?? null,
            'respirasi' => $validated['respirasi'] ?? null,
            'tinggi_badan' => $validated['tinggi_badan'] ?? null,
            'berat_badan' => $validated['berat_badan'] ?? null,
            'keluhan_utama' => $validated['keluhan_utama'],
            'riwayat_penyakit_sekarang' => $validated['riwayat_penyakit_sekarang'] ?? null,
            'riwayat_penyakit_dahulu' => $validated['riwayat_penyakit_dahulu'] ?? null,
            'riwayat_alergi' => $validated['riwayat_alergi'] ?? null,
            'riwayat_obat' => $validated['riwayat_obat'] ?? null,
            'riwayat_keluarga' => $validated['riwayat_keluarga'] ?? null,
            'skala_nyeri' => $validated['skala_nyeri'] ?? null,
            'diagnosa_keperawatan' => $validated['diagnosa_keperawatan'] ?? null,
            'intervensi_keperawatan' => $validated['intervensi_keperawatan'] ?? null,
            'implementasi_keperawatan' => $validated['implementasi_keperawatan'] ?? null,
            'evaluasi_keperawatan' => $validated['evaluasi_keperawatan'] ?? null,
        ]);

        // Update status rekam medis menjadi siap dokter
        RekamMedis::where('id', $validated['rekam_medis_id'])
            ->update([
                'status' => RekamMedis::STATUS_SIAP_DOKTER,
                'perawat_id' => auth()->id(),
            ]);

        return redirect()->route('perawat.antrian')
            ->with('success', 'Data anamnesis berhasil disimpan. Pasien siap diperiksa dokter.');
    }
}
