<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\RekamMedis;
use App\Models\ResepObat;
use App\Models\Tindakan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanPemeriksaanUmumExport;
use App\Exports\LaporanScreeningExport;

class LaporanController extends Controller
{
    public function index()
    {
        return Inertia::render('Laporan/Index');
    }

    public function kunjungan(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        $kunjungan = RekamMedis::with(['pasien', 'dokter', 'perawat', 'pemeriksaan.tindakans'])
            ->whereBetween('tanggal_kunjungan', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('tanggal_kunjungan', 'desc')
            ->get();

        // Summary statistics
        $summary = [
            'total' => $kunjungan->count(),
            'selesai' => $kunjungan->where('status', 'selesai')->count(),
            'batal' => $kunjungan->where('status', 'batal')->count(),
            'menunggu' => $kunjungan->whereIn('status', ['menunggu_perawat', 'proses_anamnesis', 'siap_dokter', 'sedang_diperiksa'])->count(),
        ];

        // By jenis layanan
        $byJenisLayanan = $kunjungan->groupBy('jenis_layanan')->map->count();

        // By tipe pasien
        $byTipePasien = $kunjungan->groupBy(fn($item) => $item->pasien?->tipe_pasien ?? 'unknown')->map->count();

        return Inertia::render('Laporan/Kunjungan', [
            'kunjungan' => $kunjungan,
            'summary' => $summary,
            'byJenisLayanan' => $byJenisLayanan,
            'byTipePasien' => $byTipePasien,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function obat(Request $request): \Inertia\Response
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        /** @var \Illuminate\Database\Eloquent\Collection $resepObats */
        $resepObats = ResepObat::with(['obat', 'pemeriksaan.rekamMedis'])
            ->whereHas('pemeriksaan.rekamMedis', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_kunjungan', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->get();

        // Penggunaan obat dalam periode
        $penggunaanObat = $resepObats->groupBy('obat_id')
            ->map(function ($items): array {
                /** @var \Illuminate\Support\Collection $items */
                /** @var ResepObat $first */
                $first = $items->first();
                $obat = $first->obat;
                
                return [
                    'id' => $obat?->id,
                    'kode' => $obat?->kode,
                    'nama' => $obat?->nama ?? $first->nama_obat,
                    'satuan' => $obat?->satuan ?? $first->satuan,
                    'total_penggunaan' => $items->sum('jumlah'),
                    'frekuensi' => $items->count(),
                ];
            })
            ->sortByDesc('total_penggunaan')
            ->values();

        /** @var \Illuminate\Database\Eloquent\Collection $obats */
        $obats = Obat::where('is_active', true)
            ->orderBy('nama')
            ->get();

        // Stok obat saat ini
        $stokObat = $obats->map(function ($obat): array {
            /** @var Obat $obat */
            return [
                'id' => $obat->id,
                'kode' => $obat->kode,
                'nama' => $obat->nama,
                'satuan' => $obat->satuan,
                'stok' => $obat->stok,
                'stok_minimum' => $obat->stok_minimum,
                'status' => $obat->stok <= ($obat->stok_minimum ?? 10) ? 'rendah' : 'normal',
            ];
        });

        $obatRendah = $stokObat->where('status', 'rendah')->count();

        return Inertia::render('Laporan/Obat', [
            'penggunaanObat' => $penggunaanObat,
            'stokObat' => $stokObat,
            'summary' => [
                'totalPenggunaan' => $penggunaanObat->sum('total_penggunaan'),
                'jenisObatDigunakan' => $penggunaanObat->count(),
                'obatStokRendah' => $obatRendah,
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function tindakan(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        // Penggunaan tindakan dalam periode
        $penggunaanTindakan = DB::table('pemeriksaan_tindakans')
            ->join('pemeriksaans', 'pemeriksaan_tindakans.pemeriksaan_id', '=', 'pemeriksaans.id')
            ->join('rekam_medis', 'pemeriksaans.rekam_medis_id', '=', 'rekam_medis.id')
            ->join('tindakans', 'pemeriksaan_tindakans.tindakan_id', '=', 'tindakans.id')
            ->whereBetween('rekam_medis.tanggal_kunjungan', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereNull('tindakans.deleted_at')
            ->select(
                'tindakans.id',
                'tindakans.kode',
                'tindakans.nama',
                'tindakans.biaya',
                DB::raw('SUM(pemeriksaan_tindakans.jumlah) as total_penggunaan'),
                DB::raw('SUM(pemeriksaan_tindakans.biaya * pemeriksaan_tindakans.jumlah) as total_pendapatan'),
                DB::raw('COUNT(*) as frekuensi')
            )
            ->groupBy('tindakans.id', 'tindakans.kode', 'tindakans.nama', 'tindakans.biaya')
            ->orderByDesc('total_penggunaan')
            ->get();

        return Inertia::render('Laporan/Tindakan', [
            'penggunaanTindakan' => $penggunaanTindakan,
            'summary' => [
                'totalTindakan' => $penggunaanTindakan->sum('total_penggunaan'),
                'totalPendapatan' => $penggunaanTindakan->sum('total_pendapatan'),
                'jenisTindakan' => $penggunaanTindakan->count(),
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function kunjunganPdf(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        $kunjungan = RekamMedis::with(['pasien', 'dokter', 'perawat', 'pemeriksaan', 'pemeriksaan.tindakans'])
            ->whereBetween('tanggal_kunjungan', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('tanggal_kunjungan', 'desc')
            ->get();

        $summary = [
            'total' => $kunjungan->count(),
            'selesai' => $kunjungan->where('status', 'selesai')->count(),
            'batal' => $kunjungan->where('status', 'batal')->count(),
        ];

        $pdf = Pdf::loadView('pdf.laporan-kunjungan', [
            'kunjungan' => $kunjungan,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("Laporan_Kunjungan_{$startDate}_sampai_{$endDate}.pdf");
    }

    public function obatPdf(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        $resepObats = ResepObat::with(['obat', 'pemeriksaan.rekamMedis'])
            ->whereHas('pemeriksaan.rekamMedis', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_kunjungan', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->get();

        $penggunaanObat = $resepObats->groupBy('obat_id')
            ->map(function ($items) {
                /** @var \Illuminate\Support\Collection $items */
                $obat = $items->first()->obat;
                return [
                    'kode' => $obat?->kode,
                    'nama' => $obat?->nama ?? $items->first()->nama_obat,
                    'satuan' => $obat?->satuan ?? $items->first()->satuan,
                    'total_penggunaan' => $items->sum('jumlah'),
                    'frekuensi' => $items->count(),
                ];
            })
            ->sortByDesc('total_penggunaan')
            ->values();

        $stokObat = Obat::where('is_active', true)
            ->orderBy('nama')
            ->get();

        $pdf = Pdf::loadView('pdf.laporan-obat', [
            'penggunaanObat' => $penggunaanObat,
            'stokObat' => $stokObat,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Laporan_Obat_{$startDate}_sampai_{$endDate}.pdf");
    }

    public function tindakanPdf(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        $penggunaanTindakan = DB::table('pemeriksaan_tindakans')
            ->join('pemeriksaans', 'pemeriksaan_tindakans.pemeriksaan_id', '=', 'pemeriksaans.id')
            ->join('rekam_medis', 'pemeriksaans.rekam_medis_id', '=', 'rekam_medis.id')
            ->join('tindakans', 'pemeriksaan_tindakans.tindakan_id', '=', 'tindakans.id')
            ->whereBetween('rekam_medis.tanggal_kunjungan', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereNull('tindakans.deleted_at')
            ->select(
                'tindakans.id',
                'tindakans.kode',
                'tindakans.nama',
                'tindakans.biaya',
                DB::raw('SUM(pemeriksaan_tindakans.jumlah) as total_penggunaan'),
                DB::raw('SUM(pemeriksaan_tindakans.biaya * pemeriksaan_tindakans.jumlah) as total_pendapatan'),
                DB::raw('COUNT(*) as frekuensi')
            )
            ->groupBy('tindakans.id', 'tindakans.kode', 'tindakans.nama', 'tindakans.biaya')
            ->orderByDesc('total_penggunaan')
            ->get();

        $summary = [
            'totalTindakan' => $penggunaanTindakan->sum('total_penggunaan'),
            'totalPendapatan' => $penggunaanTindakan->sum('total_pendapatan'),
            'jenisTindakan' => $penggunaanTindakan->count(),
        ];

        $pdf = Pdf::loadView('pdf.laporan-tindakan', [
            'penggunaanTindakan' => $penggunaanTindakan,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Laporan_Tindakan_{$startDate}_sampai_{$endDate}.pdf");
    }

    public function pemeriksaanUmum(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        $rekamMedis = RekamMedis::with(['pasien', 'anamnesis', 'pemeriksaan', 'pemeriksaan.tindakans'])
            ->has('pasien')
            ->where('jenis_layanan', 'berobat')
            ->whereBetween('tanggal_kunjungan', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('Laporan/PemeriksaanUmum', [
            'rekamMedis' => $rekamMedis,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function pemeriksaanUmumPdf(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        $rekamMedis = RekamMedis::with(['pasien', 'anamnesis', 'pemeriksaan', 'pemeriksaan.tindakans'])
            ->has('pasien')
            ->where('jenis_layanan', 'berobat')
            ->whereBetween('tanggal_kunjungan', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.laporan-pemeriksaan-umum', [
            'rekamMedis' => $rekamMedis,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("Laporan_Pemeriksaan_Umum_{$startDate}_sampai_{$endDate}.pdf");
    }

    public function pemeriksaanUmumExcel(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        return Excel::download(new LaporanPemeriksaanUmumExport($startDate, $endDate), "Laporan_Pemeriksaan_Umum_{$startDate}_sampai_{$endDate}.xlsx");
    }

    public function screening(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        $rekamMedis = RekamMedis::with(['pasien', 'anamnesis'])
            ->has('pasien')
            ->where('jenis_layanan', 'screening')
            ->whereBetween('tanggal_kunjungan', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('Laporan/Screening', [
            'rekamMedis' => $rekamMedis,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function screeningPdf(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        $rekamMedis = RekamMedis::with(['pasien', 'anamnesis'])
            ->has('pasien')
            ->where('jenis_layanan', 'screening')
            ->whereBetween('tanggal_kunjungan', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.laporan-screening', [
            'rekamMedis' => $rekamMedis,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("Laporan_Screening_{$startDate}_sampai_{$endDate}.pdf");
    }

    public function screeningExcel(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        return Excel::download(new LaporanScreeningExport($startDate, $endDate), "Laporan_Screening_{$startDate}_sampai_{$endDate}.xlsx");
    }
}
