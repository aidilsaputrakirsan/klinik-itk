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

        return Inertia::render('Dashboard', [
            'stats' => $stats
        ]);
    }
}
