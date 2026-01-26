<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Models\RekamMedis;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PasienController extends Controller
{
    public function index(Request $request)
    {
        $query = Pasien::query();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nomor_rm', 'like', "%{$search}%");
            });
        }

        $pasiens = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Pasien/Index', [
            'pasiens' => $pasiens,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Pasien/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|size:16|unique:pasiens,nik',
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'status_pasien' => 'required|in:mahasiswa,dosen,tendik,umum',
            'nim_nip' => 'nullable|string|max:50',
            'fakultas' => 'nullable|string|max:100',
            'program_studi' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|in:pns,pppk,swasta,wiraswasta,pelajar_mahasiswa,lainnya',
            'status_perkawinan' => 'nullable|in:belum_kawin,kawin,cerai_hidup,cerai_mati',
            'agama' => 'nullable|in:islam,kristen,katolik,hindu,buddha,konghucu,lainnya',
            'pendidikan_terakhir' => 'nullable|in:sd,smp,sma_smk,d1,d2,d3,d4_s1,s2,s3',
            'consent' => 'required|accepted',
        ]);

        $pasien = Pasien::create([
            'nomor_rm' => Pasien::generateNomorRM(),
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'alamat' => $validated['alamat'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'golongan_darah' => $validated['golongan_darah'] ?? null,
            'tipe_pasien' => $validated['status_pasien'],
            'nomor_identitas' => $validated['nim_nip'] ?? null,
            'fakultas' => $validated['fakultas'] ?? null,
            'prodi' => $validated['program_studi'] ?? null,
            'pekerjaan' => $validated['pekerjaan'] ?? null,
            'status_perkawinan' => $validated['status_perkawinan'] ?? null,
            'agama' => $validated['agama'] ?? null,
            'pendidikan_terakhir' => $validated['pendidikan_terakhir'] ?? null,
            'consent_at' => now(),
        ]);

        // Otomatis buat rekam medis / kunjungan baru
        RekamMedis::create([
            'nomor_kunjungan' => RekamMedis::generateNomorKunjungan(),
            'pasien_id' => $pasien->id,
            'tanggal_kunjungan' => now(),
            'status' => RekamMedis::STATUS_MENUNGGU_PERAWAT,
        ]);

        return redirect()->route('pasien.index')
            ->with('success', 'Pasien berhasil didaftarkan dan masuk ke antrian.');
    }

    public function show(Pasien $pasien)
    {
        $pasien->load(['rekamMedis' => function ($query) {
            $query->with([
                'anamnesis.perawat:id,name',
                'pemeriksaan.dokter:id,name',
                'pemeriksaan.resepObats.obat',
                'pemeriksaan.tindakans',
                'suratDokter.dokter:id,name'
            ])->orderBy('tanggal_kunjungan', 'desc');
        }]);

        return Inertia::render('Pasien/Show', [
            'pasien' => $pasien,
        ]);
    }

    public function edit(Pasien $pasien)
    {
        return Inertia::render('Pasien/Edit', [
            'pasien' => $pasien,
        ]);
    }

    public function update(Request $request, Pasien $pasien)
    {
        $validated = $request->validate([
            'nik' => 'required|string|size:16|unique:pasiens,nik,' . $pasien->id,
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'status_pasien' => 'required|in:mahasiswa,dosen,tendik,umum',
            'nim_nip' => 'nullable|string|max:50',
            'fakultas' => 'nullable|string|max:100',
            'program_studi' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|in:pns,pppk,swasta,wiraswasta,pelajar_mahasiswa,lainnya',
            'status_perkawinan' => 'nullable|in:belum_kawin,kawin,cerai_hidup,cerai_mati',
            'agama' => 'nullable|in:islam,kristen,katolik,hindu,buddha,konghucu,lainnya',
            'pendidikan_terakhir' => 'nullable|in:sd,smp,sma_smk,d1,d2,d3,d4_s1,s2,s3',
        ]);

        $pasien->update([
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'alamat' => $validated['alamat'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'golongan_darah' => $validated['golongan_darah'] ?? null,
            'tipe_pasien' => $validated['status_pasien'],
            'nomor_identitas' => $validated['nim_nip'] ?? null,
            'fakultas' => $validated['fakultas'] ?? null,
            'prodi' => $validated['program_studi'] ?? null,
            'pekerjaan' => $validated['pekerjaan'] ?? null,
            'status_perkawinan' => $validated['status_perkawinan'] ?? null,
            'agama' => $validated['agama'] ?? null,
            'pendidikan_terakhir' => $validated['pendidikan_terakhir'] ?? null,
        ]);

        return redirect()->route('pasien.index')
            ->with('success', 'Data pasien berhasil diperbarui.');
    }

    public function destroy(Pasien $pasien)
    {
        $pasien->delete();

        return redirect()->route('pasien.index')
            ->with('success', 'Data pasien berhasil dihapus.');
    }

    // Daftarkan kunjungan baru untuk pasien yang sudah ada
    public function daftarKunjungan(Request $request, Pasien $pasien)
    {
        $validated = $request->validate([
            'jenis_layanan' => 'nullable|in:berobat,surat_sehat,screening',
            'catatan' => 'nullable|string',
        ]);

        RekamMedis::create([
            'nomor_kunjungan' => RekamMedis::generateNomorKunjungan(),
            'pasien_id' => $pasien->id,
            'tanggal_kunjungan' => now(),
            'jenis_layanan' => $validated['jenis_layanan'] ?? 'berobat',
            'status' => RekamMedis::STATUS_MENUNGGU_PERAWAT,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->back()
            ->with('success', 'Kunjungan baru berhasil didaftarkan.');
    }
}
