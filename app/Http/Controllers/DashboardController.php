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

        // Aktivitas Terbaru dari Activity Log
        $aktivitas_terbaru = \App\Models\ActivityLog::with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function (\App\Models\ActivityLog $log) {
                $subject = $log->subject();
                $pasienNama = '-';
                
                if ($subject) {
                    if ($log->model_type === 'App\\Models\\Pasien') {
                        $pasienNama = $subject->nama;
                    } elseif (in_array($log->model_type, ['App\\Models\\RekamMedis', 'App\\Models\\Anamnesis', 'App\\Models\\Pemeriksaan'])) {
                        // Assuming they have a rekamMedis relation or are RekamMedis themselves
                        if ($log->model_type === 'App\\Models\\RekamMedis') {
                            $pasienNama = $subject->pasien ? $subject->pasien->nama : '-';
                        } else {
                            $pasienNama = $subject->rekamMedis && $subject->rekamMedis->pasien ? $subject->rekamMedis->pasien->nama : '-';
                        }
                    }
                }

                $deskripsi = $log->action_label . ' ' . $log->model_name;
                $icon = 'pi pi-info-circle';
                $color = 'bg-gray-100 text-gray-600';

                if ($log->model_type === 'App\\Models\\Pasien') {
                    $icon = 'pi pi-user-plus';
                    $color = 'bg-emerald-100 text-emerald-600';
                } elseif ($log->model_type === 'App\\Models\\RekamMedis') {
                    $icon = 'pi pi-stethoscope';
                    $color = 'bg-blue-100 text-blue-600';
                } elseif ($log->model_type === 'App\\Models\\Anamnesis') {
                    $icon = 'pi pi-clipboard';
                    $color = 'bg-amber-100 text-amber-600';
                } elseif ($log->model_type === 'App\\Models\\Pemeriksaan') {
                    $icon = 'pi pi-heart';
                    $color = 'bg-rose-100 text-rose-600';
                }

                // Add user info
                $userName = $log->user ? $log->user->name : 'System';

                return [
                    'id' => $log->id,
                    'tipe' => $log->model_name,
                    'deskripsi' => $deskripsi,
                    'pasien_nama' => $userName . ' - ' . $pasienNama,
                    'waktu' => $log->created_at->diffForHumans(),
                    'timestamp' => $log->created_at->timestamp,
                    'icon' => $icon,
                    'color' => $color,
                ];
            });

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
