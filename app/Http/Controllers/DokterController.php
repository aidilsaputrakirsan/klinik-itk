<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\Pemeriksaan;
use App\Models\RekamMedis;
use App\Models\ResepObat;
use App\Models\SuratDokter;
use App\Models\Tindakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DokterController extends Controller
{
    public function antrian()
    {
        $antrian = RekamMedis::with(['pasien', 'anamnesis'])
            ->whereIn('status', [RekamMedis::STATUS_SIAP_DOKTER, RekamMedis::STATUS_SEDANG_DIPERIKSA])
            ->whereDate('tanggal_kunjungan', today())
            ->orderBy('created_at', 'asc')
            ->get();

        $obats = Obat::where('is_active', true)
            ->where('stok', '>', 0)
            ->orderBy('nama')
            ->get();

        $tindakans = Tindakan::where('is_active', true)
            ->orderBy('nama')
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
            'pemeriksaan_fisik' => 'nullable|string',
            'hasil_pemeriksaan' => 'nullable|string',
            'diagnosis_utama' => 'required|string',
            'diagnosis_sekunder' => 'nullable|string',
            'kode_icd10' => 'nullable|string|max:20',
            'prognosis' => 'nullable|string',
            'anjuran' => 'nullable|string',
            'penatalaksanaan_medis' => 'nullable|string',
            'selectedTindakans' => 'nullable|array',
            'selectedTindakans.*' => 'exists:tindakans,id',
            'resepObat' => 'nullable|array',
            'resepObat.*.obat_id' => 'required_with:resepObat|exists:obats,id',
            'resepObat.*.jumlah' => 'required_with:resepObat|integer|min:1',
            'resepObat.*.dosis' => 'nullable|string',
            'resepObat.*.aturan_pakai' => 'nullable|string',
            'resepObat.*.keterangan' => 'nullable|string',
            // Surat Keterangan Dokter
            'buat_surat' => 'nullable|boolean',
            'jenis_surat' => 'required_if:buat_surat,true|nullable|in:surat_sehat,surat_sakit',
            'keperluan_surat' => 'nullable|string|max:255',
            'jumlah_hari_istirahat' => 'nullable|integer|min:1|max:14',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ], [
            'diagnosis_utama.required' => 'Diagnosis utama wajib diisi',
            'jenis_surat.required_if' => 'Jenis surat wajib dipilih',
        ]);

        DB::transaction(function () use ($validated) {
            // Simpan pemeriksaan
            $pemeriksaan = Pemeriksaan::create([
                'rekam_medis_id' => $validated['rekam_medis_id'],
                'dokter_id' => auth()->id(),
                'pemeriksaan_fisik' => $validated['pemeriksaan_fisik'] ?? null,
                'hasil_pemeriksaan' => $validated['hasil_pemeriksaan'] ?? null,
                'diagnosis_utama' => $validated['diagnosis_utama'],
                'diagnosis_sekunder' => $validated['diagnosis_sekunder'] ?? null,
                'kode_icd10' => $validated['kode_icd10'] ?? null,
                'prognosis' => $validated['prognosis'] ?? null,
                'anjuran' => $validated['anjuran'] ?? null,
                'penatalaksanaan_medis' => $validated['penatalaksanaan_medis'] ?? null,
            ]);

            // Simpan tindakan
            if (!empty($validated['selectedTindakans'])) {
                foreach ($validated['selectedTindakans'] as $tindakanId) {
                    $tindakan = Tindakan::find($tindakanId);
                    DB::table('pemeriksaan_tindakans')->insert([
                        'pemeriksaan_id' => $pemeriksaan->id,
                        'tindakan_id' => $tindakanId,
                        'biaya' => $tindakan->biaya,
                        'jumlah' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Simpan resep obat
            if (!empty($validated['resepObat'])) {
                foreach ($validated['resepObat'] as $resep) {
                    if (!empty($resep['obat_id']) && $resep['obat_id'] > 0) {
                        $obat = Obat::find($resep['obat_id']);

                        ResepObat::create([
                            'pemeriksaan_id' => $pemeriksaan->id,
                            'obat_id' => $resep['obat_id'],
                            'nama_obat' => $obat->nama,
                            'jumlah' => $resep['jumlah'],
                            'satuan' => $obat->satuan,
                            'dosis' => $resep['dosis'] ?? null,
                            'aturan_pakai' => $resep['aturan_pakai'] ?? null,
                            'keterangan' => $resep['keterangan'] ?? null,
                        ]);

                        // Kurangi stok obat
                        $obat->decrement('stok', $resep['jumlah']);
                    }
                }
            }

            // Simpan surat keterangan dokter jika diminta
            if (!empty($validated['buat_surat']) && !empty($validated['jenis_surat'])) {
                SuratDokter::create([
                    'nomor_surat' => SuratDokter::generateNomorSurat($validated['jenis_surat']),
                    'rekam_medis_id' => $validated['rekam_medis_id'],
                    'dokter_id' => auth()->id(),
                    'jenis_surat' => $validated['jenis_surat'],
                    'tanggal_surat' => now(),
                    'keperluan' => $validated['keperluan_surat'] ?? null,
                    'jumlah_hari_istirahat' => $validated['jenis_surat'] === 'surat_sakit'
                        ? ($validated['jumlah_hari_istirahat'] ?? 1)
                        : null,
                    'tanggal_mulai' => $validated['jenis_surat'] === 'surat_sakit'
                        ? ($validated['tanggal_mulai'] ?? now())
                        : null,
                    'tanggal_selesai' => $validated['jenis_surat'] === 'surat_sakit'
                        ? ($validated['tanggal_selesai'] ?? now()->addDays($validated['jumlah_hari_istirahat'] ?? 1))
                        : null,
                ]);
            }

            // Update status rekam medis
            RekamMedis::where('id', $validated['rekam_medis_id'])
                ->update([
                    'status' => RekamMedis::STATUS_SELESAI,
                    'dokter_id' => auth()->id(),
                ]);
        });

        return redirect()->route('dokter.antrian')
            ->with('success', 'Pemeriksaan berhasil disimpan.');
    }
}
