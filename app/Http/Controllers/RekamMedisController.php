<?php

namespace App\Http\Controllers;

use App\Models\RekamMedis;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekamMedisTemplateExport;
use App\Imports\RekamMedisImport;

class RekamMedisController extends Controller
{
    public function updateAnamnesis(Request $request, RekamMedis $rekamMedis)
    {
        $validated = $request->validate([
            'perawat_id' => 'required|exists:users,id',
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
            'lingkar_perut' => 'nullable|numeric',
            'is_hamil' => 'nullable|boolean',
            'tindak_lanjut' => 'nullable|string',
            'keterangan_tindak_lanjut' => 'nullable|string',
            'gula_darah' => 'nullable|numeric',
            'asam_urat' => 'nullable|numeric',
            'kolesterol' => 'nullable|numeric',
            'hemoglobin' => 'nullable|numeric',
        ]);

        if ($rekamMedis->anamnesis) {
            $rekamMedis->anamnesis->update($validated);
        } else {
            $rekamMedis->anamnesis()->create($validated);
        }

        if (!$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax())) {
            return response()->json(['status' => 'success', 'message' => 'Data anamnesis berhasil diperbarui.']);
        }

        return redirect()->back()->with('success', 'Data anamnesis & keperawatan berhasil diperbarui.');
    }

    public function updatePemeriksaan(Request $request, RekamMedis $rekamMedis)
    {
        $validated = $request->validate([
            'dokter_id' => 'required|exists:users,id',
            'diagnosis_utama' => 'nullable|string',
            'diagnosis_sekunder' => 'nullable|string',
            'kode_icd10' => 'nullable|string',
            'pemeriksaan_fisik' => 'nullable|string',
            'hasil_pemeriksaan' => 'nullable|string',
            'penatalaksanaan_medis' => 'nullable|string',
            'prognosis' => 'nullable|string',
            'anjuran' => 'nullable|string',
            'selectedTindakans' => 'nullable|array',
            'selectedTindakans.*' => 'exists:tindakans,id',
            'resepObat' => 'nullable|array',
            'resepObat.*.obat_id' => 'required_with:resepObat|exists:obats,id',
            'resepObat.*.jumlah' => 'required_with:resepObat|integer|min:1',
            'resepObat.*.dosis' => 'nullable|string',
            'resepObat.*.aturan_pakai' => 'nullable|string',
            'resepObat.*.keterangan' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $rekamMedis, $request) {
            $pemeriksaanData = \Illuminate\Support\Arr::except($validated, ['selectedTindakans', 'resepObat']);
            
            if ($rekamMedis->pemeriksaan) {
                $rekamMedis->pemeriksaan->update($pemeriksaanData);
                $pemeriksaan = $rekamMedis->pemeriksaan;
            } else {
                $pemeriksaan = $rekamMedis->pemeriksaan()->create($pemeriksaanData);
            }

            // Sync tindakans
            $syncData = [];
            if (isset($validated['selectedTindakans'])) {
                foreach ($validated['selectedTindakans'] as $tindakanId) {
                    $tindakan = \App\Models\Tindakan::find($tindakanId);
                    if ($tindakan) {
                        $syncData[$tindakanId] = [
                            'biaya' => $tindakan->biaya,
                            'jumlah' => 1,
                        ];
                    }
                }
            }
            $pemeriksaan->tindakans()->sync($syncData);

            // Revert stock for all old resepObats of this pemeriksaan
            foreach ($pemeriksaan->resepObats as $oldResep) {
                if ($oldResep->obat) {
                    $oldResep->obat->increment('stok', $oldResep->jumlah);
                }
            }
            // Delete old resepObats
            $pemeriksaan->resepObats()->delete();

            // Create new resepObats and decrement stock
            if (!empty($validated['resepObat'])) {
                foreach ($validated['resepObat'] as $resep) {
                    if (!empty($resep['obat_id']) && $resep['obat_id'] > 0) {
                        $obat = \App\Models\Obat::find($resep['obat_id']);
                        if ($obat) {
                            \App\Models\ResepObat::create([
                                'pemeriksaan_id' => $pemeriksaan->id,
                                'obat_id' => $resep['obat_id'],
                                'nama_obat' => $obat->nama,
                                'jumlah' => $resep['jumlah'],
                                'satuan' => $obat->satuan,
                                'dosis' => $resep['dosis'] ?? null,
                                'aturan_pakai' => $resep['aturan_pakai'] ?? null,
                                'keterangan' => $resep['keterangan'] ?? null,
                            ]);
                            $obat->decrement('stok', $resep['jumlah']);
                        }
                    }
                }
            }
        });

        if (!$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax())) {
            return response()->json(['status' => 'success', 'message' => 'Data pemeriksaan diperbarui.']);
        }

        return redirect()->back()->with('success', 'Data pemeriksaan rekam medis berhasil diperbarui.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new RekamMedisTemplateExport, 'Template_Rekam_Medis.xlsx');
    }

    public function importExcel(Request $request, Pasien $pasien)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,csv,xls|max:2048',
        ]);

        try {
            $importer = new RekamMedisImport($pasien);
            Excel::import($importer, $request->file('file_excel'));
            $count = $importer->getImportedCount();
            return redirect()->back()->with('success', "Berhasil mengimpor $count data Rekam Medis dari file Excel.");
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            return redirect()->back()->withErrors(['file_excel' => 'Gagal validasi data Excel baris: ' . $failures[0]->row()]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file_excel' => 'Gagal mengimpor data: ' . $e->getMessage()]);
        }
    }
    public function destroy(RekamMedis $rekamMedis)
    {
        // Hapus relasi jika tidak cascade
        if ($rekamMedis->anamnesis) {
            $rekamMedis->anamnesis()->delete();
        }
        if ($rekamMedis->pemeriksaan) {
            $rekamMedis->pemeriksaan()->delete();
        }
        if ($rekamMedis->suratDokter) {
            $rekamMedis->suratDokter()->delete();
        }
        // Hapus rekam medis
        $rekamMedis->delete();

        return redirect()->back()->with('success', 'Rekam medis berhasil dihapus.');
    }
}
