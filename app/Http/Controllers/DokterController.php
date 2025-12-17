<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\Pemeriksaan;
use App\Models\RekamMedis;
use App\Models\ResepObat;
use App\Models\Tindakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DokterController extends Controller
{
    public function antrian()
    {
        $antrian = RekamMedis::with(['pasien', 'anamnesis'])
            ->where('status', 'anamnesis')
            ->whereDate('tanggal_kunjungan', today())
            ->orderBy('tanggal_kunjungan', 'asc')
            ->get();

        $obats = Obat::where('is_active', true)
            ->where('stok', '>', 0)
            ->orderBy('nama_obat')
            ->get();

        $tindakans = Tindakan::where('is_active', true)
            ->orderBy('nama_tindakan')
            ->get();

        return Inertia::render('Dokter/Antrian', [
            'antrian' => $antrian,
            'obats' => $obats,
            'tindakans' => $tindakans,
        ]);
    }

    public function storePemeriksaan(Request $request)
    {
        $validated = $request->validate([
            'rekam_medis_id' => 'required|exists:rekam_medis,id',
            'diagnosis' => 'required|string',
            'diagnosis_sekunder' => 'nullable|string',
            'icd_10' => 'nullable|string|max:20',
            'catatan_pemeriksaan' => 'nullable|string',
            'anjuran' => 'nullable|string',
            'selectedTindakans' => 'nullable|array',
            'selectedTindakans.*' => 'exists:tindakans,id',
            'resepObat' => 'nullable|array',
            'resepObat.*.obat_id' => 'required_with:resepObat|exists:obats,id',
            'resepObat.*.jumlah' => 'required_with:resepObat|integer|min:1',
            'resepObat.*.aturan_pakai' => 'nullable|string',
            'resepObat.*.catatan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            // Simpan pemeriksaan
            $pemeriksaan = Pemeriksaan::create([
                'rekam_medis_id' => $validated['rekam_medis_id'],
                'dokter_id' => auth()->id(),
                'diagnosis' => $validated['diagnosis'],
                'diagnosis_sekunder' => $validated['diagnosis_sekunder'] ?? null,
                'icd_10' => $validated['icd_10'] ?? null,
                'catatan_pemeriksaan' => $validated['catatan_pemeriksaan'] ?? null,
                'anjuran' => $validated['anjuran'] ?? null,
                'waktu_pemeriksaan' => now(),
            ]);

            // Simpan tindakan
            if (!empty($validated['selectedTindakans'])) {
                foreach ($validated['selectedTindakans'] as $tindakanId) {
                    $tindakan = Tindakan::find($tindakanId);
                    DB::table('pemeriksaan_tindakans')->insert([
                        'pemeriksaan_id' => $pemeriksaan->id,
                        'tindakan_id' => $tindakanId,
                        'tarif' => $tindakan->tarif,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Simpan resep obat
            if (!empty($validated['resepObat'])) {
                foreach ($validated['resepObat'] as $resep) {
                    if ($resep['obat_id'] > 0) {
                        $obat = Obat::find($resep['obat_id']);

                        ResepObat::create([
                            'rekam_medis_id' => $validated['rekam_medis_id'],
                            'obat_id' => $resep['obat_id'],
                            'jumlah' => $resep['jumlah'],
                            'satuan' => $obat->satuan,
                            'aturan_pakai' => $resep['aturan_pakai'] ?? null,
                            'catatan' => $resep['catatan'] ?? null,
                        ]);

                        // Kurangi stok obat
                        $obat->decrement('stok', $resep['jumlah']);
                    }
                }
            }

            // Update status rekam medis
            RekamMedis::where('id', $validated['rekam_medis_id'])
                ->update(['status' => 'selesai']);
        });

        return redirect()->route('dokter.antrian')
            ->with('success', 'Pemeriksaan berhasil disimpan.');
    }
}
