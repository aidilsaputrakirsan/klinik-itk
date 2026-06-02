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
    protected $importedCount = 0;

    public function __construct(Pasien $pasien)
    {
        $this->pasien = $pasien;
        $this->perawatMap = User::where('role', 'perawat')->where('is_active', true)->get()->keyBy('name')->toArray();
        $this->dokterMap = User::where('role', 'dokter')->where('is_active', true)->get()->keyBy('name')->toArray();
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function collection(Collection $rows)
    {
        Log::info("Memulai import Excel. Raw row count: " . $rows->count());

        if ($rows->isEmpty()) {
            throw new \Exception("File Excel tidak memiliki data baris yang valid (kosong).");
        }

        $firstRow = $rows->first();
        if ($firstRow instanceof Collection) {
            Log::info("First row keys: " . implode(', ', $firstRow->keys()->toArray()));
        }

        $rawHeaders = [
            'tanggal_kunjungan', 'jenis_layanan', 'catatan_kunjungan',
            'perawat', 'tekanan_darah', 'suhu', 'nadi', 'respirasi', 
            'tinggi_badan', 'berat_badan', 'skala_nyeri', 'keluhan_utama', 
            'riwayat_penyakit_sekarang', 'riwayat_penyakit_dahulu', 'riwayat_keluarga', 
            'riwayat_alergi', 'riwayat_obat', 
            'diagnosa_keperawatan', 'intervensi_keperawatan', 'implementasi_keperawatan', 'evaluasi_keperawatan',
            'dokter', 'pemeriksaan_fisik', 'hasil_pemeriksaan', 'diagnosis_utama', 
            'diagnosis_sekunder', 'kode_icd10', 'prognosis', 'anjuran', 'penatalaksanaan_medis'
        ];

        $processedInSession = 0;

        foreach ($rows as $index => $rowArray) {
            $rowRaw = ($rowArray instanceof Collection) ? $rowArray->toArray() : (array)$rowArray;
            
            // Map data (Handle numeric vs named keys)
            $row = [];
            foreach ($rawHeaders as $idx => $key) {
                $row[$key] = $rowRaw[$key] ?? ($rowRaw[$idx] ?? null);
            }

            // Skip header if it is numeric keys and the first row contains headers
            if ($index === 0 && ($row['tanggal_kunjungan'] === 'Tanggal Kunjungan' || $row['tanggal_kunjungan'] === 'tanggal_kunjungan')) {
                continue;
            }

            // Skip baris yang benar-benar kosong (hanya ada null, kosong, atau -)
            $nonEmptyValues = array_filter($row, function($value) {
                 return !is_null($value) && $value !== '' && $value !== '-';
            });

            if (empty($nonEmptyValues)) {
                continue;
            }

            Log::info("Memproses baris ke-" . ($index + 1), $row);

            $processedInSession++;

            // Cari perawat by name
            $perawatId = $this->findUserId($row['perawat'] ?? null, 'perawat');
            // Cari dokter by name
            $dokterId = $this->findUserId($row['dokter'] ?? null, 'dokter');

            // Handle excel date formatting if necessary, atau gunakan tanggal import (hari ini)
            // Sesuai permintaan pengguna: menggunakan "tanggal pengimportan" (sekarang) agar sama dengan tanggal pembuatan
            $tanggal = now();

            DB::transaction(function () use ($row, $perawatId, $dokterId, $tanggal) {
                // Loop retry safe save for UniqueConstraintViolationException
                $attempts = 0;
                $rekamMedis = null;
                do {
                    try {
                        $rekamMedis = RekamMedis::create([
                            'nomor_kunjungan' => RekamMedis::generateNomorKunjungan(),
                            'pasien_id' => $this->pasien->id,
                            'tanggal_kunjungan' => $tanggal,
                            'jenis_layanan' => $row['jenis_layanan'] ?? 'berobat',
                            'status' => RekamMedis::STATUS_MENUNGGU_PERAWAT, // diubah agar data dapat diedit dan diakses di dashboard antrian
                            'catatan' => $row['catatan_kunjungan'] ?? null,
                        ]);
                        break;
                    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                        $attempts++;
                        if ($attempts >= 5) throw $e;
                        usleep(100000); // 100ms
                    }
                } while ($attempts < 5);

                // Helper closure to sanitize numeric values
                $numericValue = function($val) {
                    return (!is_numeric($val) || $val === '-' || $val === '') ? null : $val;
                };

                $rekamMedis->anamnesis()->create([
                    'perawat_id' => $perawatId,
                    'tekanan_darah' => $row['tekanan_darah'] ?? null,
                    'suhu' => $numericValue($row['suhu'] ?? null),
                    'nadi' => $numericValue($row['nadi'] ?? null),
                    'respirasi' => $numericValue($row['respirasi'] ?? null),
                    'tinggi_badan' => $numericValue($row['tinggi_badan'] ?? null),
                    'berat_badan' => $numericValue($row['berat_badan'] ?? null),
                    'skala_nyeri' => $numericValue($row['skala_nyeri'] ?? null),
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

        if ($processedInSession === 0) {
            throw new \Exception("File Excel tidak memiliki baris data yang valid untuk diimpor. Pastikan Anda menuliskan data di bawah header.");
        }

        $this->importedCount = $processedInSession;
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
