<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Models\RekamMedis;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        if ($request->tipe_pasien) {
            $query->where('tipe_pasien', $request->tipe_pasien);
        }

        $query->where('is_draft', false);

        $pasiens = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Pasien/Index', [
            'pasiens' => $pasiens,
            'filters' => $request->only(['search', 'tipe_pasien']),
        ]);
    }

    public function create(Request $request)
    {
        $query = Pasien::where('is_draft', true);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nomor_rm', 'like', "%{$search}%");
            });
        }

        if ($request->tipe_pasien) {
            $query->where('tipe_pasien', $request->tipe_pasien);
        }

        $draftPasiens = $query->orderBy('created_at', 'desc')->get();

        return Inertia::render('Pasien/Create', [
            'draftPasiens' => $draftPasiens,
            'filters' => $request->only(['search', 'tipe_pasien']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => [
                'required',
                'string',
                'size:16',
                Rule::unique('pasiens', 'nik')->whereNull('deleted_at'),
            ],
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'golongan_darah' => 'nullable|in:A,B,AB,O,Tidak Tahu',
            'status_pasien' => 'required|in:mahasiswa,dosen,tendik,umum',
            'nim_nip' => 'nullable|string|max:50',
            'fakultas' => 'nullable|string|max:100',
            'program_studi' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|in:pns,pppk,swasta,wiraswasta,pelajar_mahasiswa,dosen,tenaga_kependidikan,lainnya',
            'status_perkawinan' => 'nullable|in:belum_kawin,kawin,cerai_hidup,cerai_mati',
            'agama' => 'nullable|in:islam,kristen,katolik,hindu,buddha,konghucu,lainnya',
            'pendidikan_terakhir' => 'nullable|in:sd,smp,sma_smk,d1,d2,d3,d4_s1,s2,s3',
        ]);

        $pasien = \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            return Pasien::create([
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
                'is_draft' => true,
            ]);
        });

        return redirect()->route('pasien.create')
            ->with('success', 'Pasien berhasil disimpan ke dalam daftar registrasi (Tab Tersimpan).');
    }

    public function activate(Pasien $pasien)
    {
        // Periksa apakah ada pasien aktif lain dengan NIK yang sama
        $activePasienExists = Pasien::where('nik', $pasien->nik)
            ->where('is_draft', false)
            ->where('id', '!=', $pasien->id)
            ->exists();

        if ($activePasienExists) {
            return redirect()->back()
                ->with('error', 'Pasien dengan NIK tersebut sudah terdaftar di Daftar Pasien Utama.');
        }

        $pasien->update(['is_draft' => false]);
        return redirect()->back()
            ->with('success', 'Pasien berhasil dipindahkan ke Daftar Pasien Utama.');
    }

    public function cetakDrafPdf(Pasien $pasien)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.pasien-draf', [
            'pasien' => $pasien
        ]);
        $pdf->setPaper('a4', 'portrait');

        $filename = "Formulir_Registrasi_{$pasien->nama}_{$pasien->nomor_rm}.pdf";

        return $pdf->download($filename);
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
            'nik' => [
                'required',
                'string',
                'size:16',
                Rule::unique('pasiens', 'nik')->ignore($pasien->id)->whereNull('deleted_at'),
            ],
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'golongan_darah' => 'nullable|in:A,B,AB,O,Tidak Tahu',
            'status_pasien' => 'required|in:mahasiswa,dosen,tendik,umum',
            'nim_nip' => 'nullable|string|max:50',
            'fakultas' => 'nullable|string|max:100',
            'program_studi' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|in:pns,pppk,swasta,wiraswasta,pelajar_mahasiswa,dosen,tenaga_kependidikan,lainnya',
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

        if ($pasien->is_draft) {
            return redirect()->route('pasien.create')
                ->with('success', 'Draft pasien berhasil diperbarui.');
        }

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
            'tanggal_kunjungan' => 'nullable|date',
            'jenis_layanan' => 'nullable|in:berobat,surat_sehat,screening',
            'catatan' => 'nullable|string',
            'client_time' => 'nullable|date',
        ]);

        $clientTime = isset($validated['client_time'])
            ? \Carbon\Carbon::parse($validated['client_time'])->setTimezone(config('app.timezone'))
            : now();

        $tanggal = isset($validated['tanggal_kunjungan'])
            ? \Carbon\Carbon::parse($validated['tanggal_kunjungan'])->setTimezone(config('app.timezone'))->setTimeFrom($clientTime)
            : clone $clientTime;

        $rekamMedis = new RekamMedis([
            'pasien_id' => $pasien->id,
            'tanggal_kunjungan' => $tanggal,
            'jenis_layanan' => $validated['jenis_layanan'] ?? 'berobat',
            'status' => RekamMedis::STATUS_MENUNGGU_PERAWAT,
            'catatan' => $validated['catatan'] ?? null,
        ]);
        
        $rekamMedis->created_at = $clientTime;
        $rekamMedis->updated_at = $clientTime;
        
        $attempts = 0;
        // Save inside DB transaction with retry
        $saved = false;
        $attempts = 0;
        while (! $saved && $attempts < 5) {
            $attempts++;
            try {
                \DB::transaction(function () use ($rekamMedis) {
                    $rekamMedis->nomor_kunjungan = RekamMedis::generateNomorKunjungan();
                    $rekamMedis->save();
                });
                $saved = true;
            } catch (\Illuminate\Database\QueryException $e) {
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
            ->with('success', 'Kunjungan baru berhasil didaftarkan.');
    }

    public function rekamMedis(Pasien $pasien)
    {
        $pasien->load(['rekamMedis' => function ($query) {
            $query->with([
                'anamnesis.perawat:id,name',
                'pemeriksaan.dokter:id,name',
                'pemeriksaan.resepObats.obat',
                'pemeriksaan.tindakans',
                'suratDokter.dokter:id,name'
            ])->orderBy('tanggal_kunjungan', 'desc')->orderBy('id', 'desc');
        }]);

        // Load reference data to be able to edit Rekam Medis correctly
        $obats = \App\Models\Obat::orderBy('nama', 'asc')->get();
        $tindakans = \App\Models\Tindakan::orderBy('nama', 'asc')->get();
        $perawatList = \App\Models\User::where('role', 'perawat')->where('is_active', true)->get(['id', 'name']);
        $dokterList = \App\Models\User::where('role', 'dokter')->where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Pasien/RekamMedis', [
            'pasien' => $pasien,
            'obats' => $obats,
            'tindakans' => $tindakans,
            'perawatList' => $perawatList,
            'dokterList' => $dokterList,
        ]);
    }
}
