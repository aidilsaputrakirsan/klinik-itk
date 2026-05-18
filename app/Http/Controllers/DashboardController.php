<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Models\RekamMedis;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = now()->toDateString();
        $now = now();
        
        $stats = [
            'total_pasien' => Pasien::count(),
            'kunjungan_hari_ini' => RekamMedis::whereDate('tanggal_kunjungan', $today)->count(),
            'menunggu_perawat' => RekamMedis::where('status', 'menunggu_perawat')
                ->whereDate('tanggal_kunjungan', $today)->count(),
            'siap_dokter' => RekamMedis::where('status', 'siap_dokter')
                ->whereDate('tanggal_kunjungan', $today)->count(),
            'sedang_diperiksa' => RekamMedis::where('status', 'sedang_diperiksa')
                ->whereDate('tanggal_kunjungan', $today)->count(),
            'selesai_hari_ini' => RekamMedis::where('status', 'selesai')
                ->whereDate('tanggal_kunjungan', $today)->count(),
        ];

        // Analitik Pasien Baru
        $analitik_pasien = [
            'harian' => Pasien::whereDate('created_at', $today)->count(),
            'mingguan' => Pasien::whereBetween('created_at', [$now->copy()->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()])->count(),
            'bulanan' => Pasien::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),
            'tahunan' => Pasien::whereYear('created_at', $now->year)->count(),
        ];

        // Aktivitas Terbaru (Gabungan Kunjungan dan Pasien Baru)
        $recent_visits = RekamMedis::with(['pasien:id,nama'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($rm) {
                return [
                    'id' => 'rm_' . $rm->id,
                    'tipe' => 'Kunjungan',
                    'deskripsi' => 'Pendaftaran Kunjungan Medis',
                    'pasien_nama' => $rm->pasien ? $rm->pasien->nama : 'Unknown',
                    'waktu' => $rm->created_at->diffForHumans(),
                    'timestamp' => $rm->created_at->timestamp,
                    'icon' => 'pi pi-stethoscope',
                    'color' => 'bg-blue-100 text-blue-600',
                ];
            });

        $recent_patients = Pasien::orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => 'p_' . $p->id,
                    'tipe' => 'Pasien Baru',
                    'deskripsi' => 'Pasien Baru Terdaftar',
                    'pasien_nama' => $p->nama,
                    'waktu' => $p->created_at->diffForHumans(),
                    'timestamp' => $p->created_at->timestamp,
                    'icon' => 'pi pi-user-plus',
                    'color' => 'bg-emerald-100 text-emerald-600',
                ];
            });

        // Gabungkan dan urutkan berdasarkan waktu terbaru
        $aktivitas_terbaru = $recent_visits->concat($recent_patients)
            ->sortByDesc('timestamp')
            ->take(5)
            ->values();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'analitik_pasien' => $analitik_pasien,
            'aktivitas_terbaru' => $aktivitas_terbaru,
        ]);
    }

    public function analitikPasien(Request $request): Response
    {
        $kategori = $request->query('kategori', 'harian');
        $offset = (int) $request->query('offset', 0);
        
        $baseDate = now();
        $startDate = null;
        $endDate = null;
        $label = '';
        
        switch ($kategori) {
            case 'mingguan':
                $targetDate = $baseDate->copy()->addWeeks($offset);
                $startDate = $targetDate->copy()->startOfWeek();
                $endDate = $targetDate->copy()->endOfWeek();
                if ($offset === 0) $label = 'Minggu Ini';
                else if ($offset === -1) $label = 'Minggu Lalu';
                else $label = 'Minggu: ' . $startDate->format('d M') . ' - ' . $endDate->format('d M Y');
                break;
            case 'bulanan':
                $targetDate = $baseDate->copy()->addMonths($offset);
                $startDate = $targetDate->copy()->startOfMonth();
                $endDate = $targetDate->copy()->endOfMonth();
                if ($offset === 0) $label = 'Bulan Ini (' . $targetDate->format('F Y') . ')';
                else $label = 'Bulan: ' . $targetDate->format('F Y');
                break;
            case 'tahunan':
                $targetDate = $baseDate->copy()->addYears($offset);
                $startDate = $targetDate->copy()->startOfYear();
                $endDate = $targetDate->copy()->endOfYear();
                if ($offset === 0) $label = 'Tahun Ini (' . $targetDate->year . ')';
                else $label = 'Tahun: ' . $targetDate->year;
                break;
            case 'harian':
            default:
                $kategori = 'harian';
                $targetDate = $baseDate->copy()->addDays($offset);
                $startDate = $targetDate->copy()->startOfDay();
                $endDate = $targetDate->copy()->endOfDay();
                if ($offset === 0) $label = 'Hari Ini (' . $targetDate->format('d M Y') . ')';
                else if ($offset === -1) $label = 'Kemarin (' . $targetDate->format('d M Y') . ')';
                else $label = $targetDate->format('d M Y');
                break;
        }

        $pasiens = Pasien::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Dashboard/AnalitikPasien', [
            'pasiens' => $pasiens,
            'kategori' => $kategori,
            'offset' => $offset,
            'label' => $label,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
        ]);
    }
}
