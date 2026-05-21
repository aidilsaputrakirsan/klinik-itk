<?php

namespace App\Http\Controllers;

use App\Models\Anamnesis;
use App\Models\RekamMedis;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class PerawatController extends Controller
{
    public function antrian(Request $request)
    {
        $filter_waktu = $request->input('filter_waktu', 'semua');
        $isFiltered = $request->query('is_filtered') == '1';
        $filterSearch = $request->query('searchTerlewat');
        $filterTanggalTerlewat = $request->query('tanggal_terlewat');

        // 1. Antrian Aktif (Hari ini dan seterusnya)
        $query = RekamMedis::with(['pasien' => function($q) {
            $q->select('id', 'nomor_rm', 'nama', 'jenis_kelamin', 'tanggal_lahir', 'tipe_pasien');
        }, 'anamnesis'])
            ->whereHas('pasien', function($q) use ($request) {
                if ($request->filled('tipe_pasien')) {
                    $q->where('tipe_pasien', $request->tipe_pasien);
                }
            })
            ->whereIn('status', [RekamMedis::STATUS_MENUNGGU_PERAWAT, RekamMedis::STATUS_PROSES_ANAMNESIS, RekamMedis::STATUS_SIAP_DOKTER])
            ->whereDate('tanggal_kunjungan', '>=', today());

        if ($request->filled('jenis_layanan')) {
            $query->where('jenis_layanan', $request->jenis_layanan);
        }

        if ($filter_waktu === 'hari_ini') {
            $query->whereDate('tanggal_kunjungan', today());
        } elseif ($filter_waktu === 'minggu_ini') {
            $query->whereBetween('tanggal_kunjungan', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($filter_waktu === 'bulan_ini') {
            $query->whereMonth('tanggal_kunjungan', now()->month)
                  ->whereYear('tanggal_kunjungan', now()->year);
        } elseif ($filter_waktu === 'custom' && $request->filled('custom_date')) {
            $query->whereDate('tanggal_kunjungan', $request->custom_date);
        }

        $antrian = $query->orderBy('tanggal_kunjungan', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Antrian Terlewat (Sebelum Hari Ini)
        $queryTerlewat = RekamMedis::with(['pasien' => function($q) {
            $q->select('id', 'nomor_rm', 'nama', 'jenis_kelamin', 'tanggal_lahir', 'tipe_pasien');
        }, 'anamnesis'])
            ->whereHas('pasien', function($q) use ($request) {
                if ($request->filled('tipe_pasien')) {
                    $q->where('tipe_pasien', $request->tipe_pasien);
                }
            })
            ->whereIn('status', [RekamMedis::STATUS_MENUNGGU_PERAWAT, RekamMedis::STATUS_PROSES_ANAMNESIS])
            ->whereDate('tanggal_kunjungan', '<', today());

        if ($request->filled('jenis_layanan')) {
            $queryTerlewat->where('jenis_layanan', $request->jenis_layanan);
        }

        if ($isFiltered) {
            if ($filterSearch) {
                $queryTerlewat->where(function($q2) use ($filterSearch) {
                    $q2->whereHas('pasien', function($q) use ($filterSearch) {
                        $q->where('nama', 'like', "%{$filterSearch}%")
                          ->orWhere('nomor_rm', 'like', "%{$filterSearch}%");
                    })->orWhere('nomor_kunjungan', 'like', "%{$filterSearch}%");
                });
            }

            if ($filterTanggalTerlewat && $filterTanggalTerlewat !== 'null' && $filterTanggalTerlewat !== '') {
                $queryTerlewat->whereDate('tanggal_kunjungan', $filterTanggalTerlewat);
            }
        }

        $antrian_terlewat = $queryTerlewat->orderBy('tanggal_kunjungan', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // 3. Antrian Selesai Diperiksa (Untuk Askep)
        $querySelesai = RekamMedis::with(['pasien' => function($q) {
            $q->select('id', 'nomor_rm', 'nama', 'jenis_kelamin', 'tanggal_lahir', 'tipe_pasien');
        }, 'anamnesis'])
            ->whereHas('pasien', function($q) use ($request) {
                if ($request->filled('tipe_pasien')) {
                    $q->where('tipe_pasien', $request->tipe_pasien);
                }
            })
            ->whereIn('status', [RekamMedis::STATUS_SEDANG_DIPERIKSA, RekamMedis::STATUS_SELESAI]);

        if ($request->filled('jenis_layanan')) {
            $querySelesai->where('jenis_layanan', $request->jenis_layanan);
        }

        $antrian_selesai = $querySelesai->orderBy('updated_at', 'desc')->get();
            
        // Get all pasien for dropdown (only name and id) for creation
        $pasiens = \App\Models\Pasien::select('id', 'nama', 'nomor_rm')->orderBy('nama')->get();

        return Inertia::render('Perawat/Antrian', [
            'antrian' => $antrian,
            'antrian_terlewat' => $antrian_terlewat,
            'antrian_selesai' => $antrian_selesai,
            'pasiens' => $pasiens,
            'filters' => [
                'filter_waktu' => $filter_waktu,
                'custom_date' => $request->custom_date,
                'searchTerlewat' => $filterSearch,
                'tanggal_terlewat' => $filterTanggalTerlewat,
                'is_filtered' => $isFiltered,
                'tipe_pasien' => $request->tipe_pasien,
                'jenis_layanan' => $request->jenis_layanan,
            ]
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
            'lingkar_perut' => 'nullable|numeric|min:30|max:200',
            'is_hamil' => 'nullable|boolean',
            'tindak_lanjut' => 'nullable|string',
            'keterangan_tindak_lanjut' => 'nullable|string',
            'gula_darah' => 'nullable|integer|min:0|max:1000',
            'asam_urat' => 'nullable|numeric|min:0|max:30',
            'kolesterol' => 'nullable|integer|min:0|max:1000',
            'hemoglobin' => 'nullable|numeric|min:0|max:30',
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

        Anamnesis::updateOrCreate(
            ['rekam_medis_id' => $validated['rekam_medis_id']],
            [
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
                'lingkar_perut' => $validated['lingkar_perut'] ?? null,
                'is_hamil' => $validated['is_hamil'] ?? false,
                'tindak_lanjut' => $validated['tindak_lanjut'] ?? null,
                'keterangan_tindak_lanjut' => $validated['keterangan_tindak_lanjut'] ?? null,
                'gula_darah' => $validated['gula_darah'] ?? null,
                'asam_urat' => $validated['asam_urat'] ?? null,
                'kolesterol' => $validated['kolesterol'] ?? null,
                'hemoglobin' => $validated['hemoglobin'] ?? null,
            ]
        );

        // Update status rekam medis menjadi siap dokter if not already passed that state, except for screening which goes straight to selesai
        $rm = RekamMedis::find($validated['rekam_medis_id']);
        if ($rm) {
            $actionType = $request->input('action_type', 'lanjut');

            if ($rm->jenis_layanan === 'screening' && in_array($rm->status, [RekamMedis::STATUS_MENUNGGU_PERAWAT, RekamMedis::STATUS_PROSES_ANAMNESIS, RekamMedis::STATUS_SIAP_DOKTER])) {
                $rm->update([
                    'status' => RekamMedis::STATUS_SELESAI,
                    'perawat_id' => auth()->id(),
                ]);
            } elseif (in_array($rm->status, [RekamMedis::STATUS_MENUNGGU_PERAWAT, RekamMedis::STATUS_PROSES_ANAMNESIS])) {
                if ($actionType === 'draft') {
                    $rm->update([
                        'status' => RekamMedis::STATUS_PROSES_ANAMNESIS,
                        'perawat_id' => auth()->id(),
                    ]);
                } else {
                    $rm->update([
                        'status' => RekamMedis::STATUS_SIAP_DOKTER,
                        'perawat_id' => auth()->id(),
                    ]);
                }
            }
        }

        return redirect()->route('perawat.antrian')
            ->with('success', 'Data anamnesis / askep berhasil disimpan.');
    }

    public function cetakAnamnesisPdf(RekamMedis $rekamMedis)
    {
        $rekamMedis->load(['pasien', 'anamnesis']);
        $pasien = $rekamMedis->pasien;

        $pdf = Pdf::loadView('pdf.anamnesis-awal', compact('rekamMedis', 'pasien'));
        $pdf->setPaper('a4', 'portrait');

        $filename = "Hasil_Anamnesis_{$pasien->nama}_{$rekamMedis->tanggal_kunjungan->format('Y-m-d')}.pdf";

        return $pdf->stream($filename);
    }

    public function storeAntrian(Request $request)
    {
        $validated = $request->validate([
            'pasien_id' => 'required|exists:pasiens,id',
            'tanggal_kunjungan' => 'nullable|date',
            'jenis_layanan' => 'nullable|in:berobat,surat_sehat,screening',
            'catatan' => 'nullable|string',
            'client_time' => 'nullable|date',
        ]);

        $clientTime = isset($validated['client_time'])
            ? \Carbon\Carbon::parse($validated['client_time'])
            : now();

        $tanggal = isset($validated['tanggal_kunjungan'])
            ? \Carbon\Carbon::parse($validated['tanggal_kunjungan'])
            : clone $clientTime;

        $rekamMedis = new RekamMedis([
            'pasien_id' => $validated['pasien_id'],
            'tanggal_kunjungan' => $tanggal,
            'jenis_layanan' => $validated['jenis_layanan'] ?? 'berobat',
            'status' => RekamMedis::STATUS_MENUNGGU_PERAWAT,
            'catatan' => $validated['catatan'] ?? null,
        ]);
        
        $rekamMedis->created_at = $clientTime;
        $rekamMedis->updated_at = $clientTime;

        // Save inside a DB transaction with lock to avoid race conditions
        $saved = false;
        $attempts = 0;
        while (! $saved && $attempts < 5) {
            $attempts++;
            try {
                \DB::transaction(function () use ($rekamMedis) {
                    // generateNomorKunjungan uses lockForUpdate on the table
                    $rekamMedis->nomor_kunjungan = RekamMedis::generateNomorKunjungan();
                    $rekamMedis->save();
                });
                $saved = true;
            } catch (\Illuminate\Database\QueryException $e) {
                // MySQL error code 1062 = duplicate entry
                if (strpos($e->getMessage(), '1062') !== false && $attempts < 5) {
                    usleep(100000);
                    continue;
                }
                throw $e;
            }
        }
        if (! $saved) {
            throw new \RuntimeException('Gagal membuat nomor kunjungan setelah beberapa percobaan. Silakan coba lagi.');
        }

        return redirect()->back()
            ->with('success', 'Kunjungan / antrian baru berhasil didaftarkan.');
    }

    public function updateAntrian(Request $request, RekamMedis $rekamMedis)
    {
        $validated = $request->validate([
            'tanggal_kunjungan' => 'required|date',
            'jenis_layanan' => 'required|in:berobat,surat_sehat,screening',
            'catatan' => 'nullable|string',
        ]);

        $tanggal = \Carbon\Carbon::parse($validated['tanggal_kunjungan']); 
        
        $rekamMedis->update([
            'tanggal_kunjungan' => $tanggal,
            'jenis_layanan' => $validated['jenis_layanan'],
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->back()
            ->with('success', 'Jadwal antrian berhasil diperbarui.');
    }

    public function destroyAntrian(RekamMedis $rekamMedis)
    {
        // To truly remove it from queue but maybe keep record we can just delete it (soft delete).
        $rekamMedis->delete();

        return redirect()->back()
            ->with('success', 'Jadwal antrian berhasil dibatalkan/dihapus.');
    }
}
