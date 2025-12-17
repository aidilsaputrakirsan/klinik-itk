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
            ->where('status', 'menunggu')
            ->whereDate('tanggal_kunjungan', today())
            ->orderBy('tanggal_kunjungan', 'asc')
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
            'suhu_tubuh' => 'nullable|numeric|min:30|max:45',
            'nadi' => 'nullable|integer|min:30|max:200',
            'pernapasan' => 'nullable|integer|min:10|max:60',
            'tinggi_badan' => 'nullable|numeric|min:50|max:250',
            'berat_badan' => 'nullable|numeric|min:1|max:300',
            'riwayat_penyakit' => 'nullable|string',
            'riwayat_alergi' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $validated['perawat_id'] = auth()->id();
        $validated['waktu_anamnesis'] = now();

        Anamnesis::create($validated);

        // Update status rekam medis
        RekamMedis::where('id', $validated['rekam_medis_id'])
            ->update(['status' => 'anamnesis']);

        return redirect()->route('perawat.antrian')
            ->with('success', 'Data anamnesis berhasil disimpan. Pasien siap diperiksa dokter.');
    }
}
