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
                  ->orWhere('no_rm', 'like', "%{$search}%");
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
            'status_pasien' => 'required|in:mahasiswa,dosen,staff,umum',
            'nim_nip' => 'nullable|string|max:50',
            'fakultas' => 'nullable|string|max:100',
            'program_studi' => 'nullable|string|max:100',
        ]);

        $validated['no_rm'] = Pasien::generateNoRM();

        $pasien = Pasien::create($validated);

        // Otomatis buat rekam medis / kunjungan baru
        RekamMedis::create([
            'no_kunjungan' => RekamMedis::generateNoKunjungan(),
            'pasien_id' => $pasien->id,
            'tanggal_kunjungan' => now(),
            'status' => 'menunggu',
            'keluhan_utama' => $request->keluhan_utama,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('pasien.index')
            ->with('success', 'Pasien berhasil didaftarkan dan masuk ke antrian.');
    }

    public function show(Pasien $pasien)
    {
        $pasien->load(['rekamMedis' => function ($query) {
            $query->with(['anamnesis', 'pemeriksaan', 'resepObat.obat'])
                  ->orderBy('tanggal_kunjungan', 'desc');
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
            'status_pasien' => 'required|in:mahasiswa,dosen,staff,umum',
            'nim_nip' => 'nullable|string|max:50',
            'fakultas' => 'nullable|string|max:100',
            'program_studi' => 'nullable|string|max:100',
        ]);

        $pasien->update($validated);

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
            'keluhan_utama' => 'nullable|string',
        ]);

        RekamMedis::create([
            'no_kunjungan' => RekamMedis::generateNoKunjungan(),
            'pasien_id' => $pasien->id,
            'tanggal_kunjungan' => now(),
            'status' => 'menunggu',
            'keluhan_utama' => $validated['keluhan_utama'] ?? null,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', 'Kunjungan baru berhasil didaftarkan.');
    }
}
