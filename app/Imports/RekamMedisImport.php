<?php

namespace App\Imports;

use App\Models\RekamMedis;
use App\Models\Anamnesis;
use App\Models\Pemeriksaan;
use App\Models\Pasien;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class RekamMedisImport implements ToCollection, WithHeadingRow
{
    protected $pasien;
    protected $perawatMap = [];
    protected $dokterMap = [];

    public function __construct(Pasien $pasien)
    {
        $this->pasien = $pasien;
        $this->perawatMap = User::where('role', 'perawat')->where('is_active', true)->get()->keyBy('name')->toArray();
        $this->dokterMap = User::where('role', 'dokter')->where('is_active', true)->get()->keyBy('name')->toArray();
    }

    public function collection(Collection $rows)
    {
        Log::info("Memulai import Excel. Total baris (tidak termasuk header): " . $rows->count());

        if ($rows->isEmpty()) {
            throw new \Exception("File Excel tidak memiliki data baris yang valid (kosong atau format tidak terbaca).");
        }

        foreach ($rows as $index => $rowArray) {
            $row = $rowArray->toArray();
            Log::info("Memproses baris ke-" . ($index + 1), $row);

            // Cari perawat by name
            $perawatId = $this->findUserId($row['perawat'] ?? null, 'perawat');
            // Cari dokter by name
            $dokterId = $this->findUserId($row['dokter'] ?? null, 'dokter');

            // Handle excel date formatting if necessary
            $tanggal = now();
            if (!empty($row['tanggal_kunjungan'])) {
                try {
                    if (is_numeric($row['tanggal_kunjungan'])) {
                        $tanggal = Carbon::instance(Date::excelToDateTimeObject($row['tanggal_kunjungan']))->setTimeFrom(Carbon::now());
                    } else {
                        $tanggal = Carbon::parse($row['tanggal_kunjungan'])->setTimeFrom(Carbon::now());
                    }
                } catch (\Exception $e) {
                    $tanggal = now();
                }
            }

            DB::transaction(function () use ($row, $perawatId, $dokterId, $tanggal) {
                $rekamMedis = RekamMedis::create([
                    'nomor_kunjungan' => RekamMedis::generateNomorKunjungan(),
                    'pasien_id' => $this->pasien->id,
                    'tanggal_kunjungan' => $tanggal,
                    'jenis_layanan' => $row['jenis_layanan'] ?? 'berobat',
                    'status' => RekamMedis::STATUS_SELESAI,
                    'catatan' => $row['catatan_kunjungan'] ?? null,
                ]);

                $rekamMedis->anamnesis()->create([
                    'perawat_id' => $perawatId,
                    'tekanan_darah' => $row['tekanan_darah'] ?? null,
                    'suhu' => $row['suhu'] ?? null,
                    'nadi' => $row['nadi'] ?? null,
                    'respirasi' => $row['respirasi'] ?? null,
                    'tinggi_badan' => $row['tinggi_badan'] ?? null,
                    'berat_badan' => $row['berat_badan'] ?? null,
                    'skala_nyeri' => $row['skala_nyeri'] ?? null,
                    'keluhan_utama' => $row['keluhan_utama'] ?? null,
                    'riwayat_penyakit_sekarang' => $row['riwayat_penyakit_sekarang'] ?? null,
                    'riwayat_penyakit_dahulu' => $row['riwayat_penyakit_dahulu'] ?? null,
                    'riwayat_keluarga' => $row['riwayat_keluarga'] ?? null,
                    'riwayat_alergi' => $row['riwayat_alergi'] ?? null,
                    'riwayat_obat' => $row['riwayat_obat'] ?? null,
                    'diagnosa_keperawatan' => $row['diagnosa_keperawatan'] ?? null,
                    'intervensi_keperawatan' => $row['intervensi_keperawatan'] ?? null,
                    'implementasi_keperawatan' => $row['implementasi_keperawatan'] ?? null,
                    'evaluasi_keperawatan' => $row['evaluasi_keperawatan'] ?? null,
                ]);

                $rekamMedis->pemeriksaan()->create([
                    'dokter_id' => $dokterId,
                    'pemeriksaan_fisik' => $row['pemeriksaan_fisik'] ?? null,
                    'hasil_pemeriksaan' => $row['hasil_pemeriksaan'] ?? null,
                    'diagnosis_utama' => $row['diagnosis_utama'] ?? null,
                    'diagnosis_sekunder' => $row['diagnosis_sekunder'] ?? null,
                    'kode_icd10' => $row['kode_icd10'] ?? null,
                    'prognosis' => $row['prognosis'] ?? null,
                    'anjuran' => $row['anjuran'] ?? null,
                    'penatalaksanaan_medis' => $row['penatalaksanaan_medis'] ?? null,
                ]);
            });
        }
    }

    private function findUserId($name, $role)
    {
        if (empty($name) || $name === '-') {
            // Default backup to auth id
            return auth()->check() ? auth()->id() : 1; 
        }

        $map = $role === 'perawat' ? $this->perawatMap : $this->dokterMap;
        
        // Exact match
        if (isset($map[$name])) {
            return $map[$name]['id'];
        }

        // Fuzzy match
        foreach ($map as $userName => $user) {
            if (stripos($userName, $name) !== false || stripos($name, $userName) !== false) {
                return $user['id'];
            }
        }

        // Last fallback
        return auth()->check() ? auth()->id() : 1;
    }
}
