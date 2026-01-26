<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\PerawatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuratDokterController;
use App\Http\Controllers\TindakanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard - semua role
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // =====================
    // ADMIN ROUTES
    // =====================
    Route::middleware('role:superadmin,admin')->group(function () {
        // Pasien Management
        Route::get('/pasien', [PasienController::class, 'index'])->name('pasien.index');
        Route::get('/pasien/create', [PasienController::class, 'create'])->name('pasien.create');
        Route::post('/pasien', [PasienController::class, 'store'])->name('pasien.store');
        Route::get('/pasien/{pasien}', [PasienController::class, 'show'])->name('pasien.show');
        Route::get('/pasien/{pasien}/edit', [PasienController::class, 'edit'])->name('pasien.edit');
        Route::put('/pasien/{pasien}', [PasienController::class, 'update'])->name('pasien.update');
        Route::delete('/pasien/{pasien}', [PasienController::class, 'destroy'])->name('pasien.destroy');
        Route::post('/pasien/{pasien}/kunjungan', [PasienController::class, 'daftarKunjungan'])->name('pasien.kunjungan');

        // Master Obat
        Route::get('/obat', [ObatController::class, 'index'])->name('obat.index');
        Route::post('/obat', [ObatController::class, 'store'])->name('obat.store');
        Route::put('/obat/{obat}', [ObatController::class, 'update'])->name('obat.update');
        Route::delete('/obat/{obat}', [ObatController::class, 'destroy'])->name('obat.destroy');

        // Master Tindakan
        Route::get('/tindakan', [TindakanController::class, 'index'])->name('tindakan.index');
        Route::post('/tindakan', [TindakanController::class, 'store'])->name('tindakan.store');
        Route::put('/tindakan/{tindakan}', [TindakanController::class, 'update'])->name('tindakan.update');
        Route::delete('/tindakan/{tindakan}', [TindakanController::class, 'destroy'])->name('tindakan.destroy');
    });

    // =====================
    // PERAWAT ROUTES
    // =====================
    Route::middleware('role:superadmin,perawat')->group(function () {
        Route::get('/perawat/antrian', [PerawatController::class, 'antrian'])->name('perawat.antrian');
        Route::post('/perawat/anamnesis', [PerawatController::class, 'storeAnamnesis'])->name('perawat.anamnesis.store');
    });

    // =====================
    // DOKTER ROUTES
    // =====================
    Route::middleware('role:superadmin,dokter')->group(function () {
        Route::get('/dokter/antrian', [DokterController::class, 'antrian'])->name('dokter.antrian');
        Route::post('/dokter/pemeriksaan', [DokterController::class, 'storePemeriksaan'])->name('dokter.pemeriksaan.store');
    });

    // =====================
    // SURAT DOKTER PDF ROUTES
    // =====================
    Route::middleware('role:superadmin,admin,dokter')->group(function () {
        Route::get('/surat-dokter/{suratDokter}/pdf', [SuratDokterController::class, 'generatePdf'])->name('surat-dokter.pdf');
        Route::get('/surat-dokter/{suratDokter}/preview', [SuratDokterController::class, 'previewPdf'])->name('surat-dokter.preview');
    });

    // =====================
    // LAPORAN ROUTES
    // =====================
    Route::middleware('role:superadmin,admin,dokter')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/kunjungan', [LaporanController::class, 'kunjungan'])->name('laporan.kunjungan');
        Route::get('/laporan/obat', [LaporanController::class, 'obat'])->name('laporan.obat');
        Route::get('/laporan/tindakan', [LaporanController::class, 'tindakan'])->name('laporan.tindakan');
        Route::get('/laporan/kunjungan/pdf', [LaporanController::class, 'kunjunganPdf'])->name('laporan.kunjungan.pdf');
        Route::get('/laporan/obat/pdf', [LaporanController::class, 'obatPdf'])->name('laporan.obat.pdf');
        Route::get('/laporan/tindakan/pdf', [LaporanController::class, 'tindakanPdf'])->name('laporan.tindakan.pdf');
    });

    // =====================
    // SUPERADMIN ROUTES
    // =====================
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        
        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});

require __DIR__.'/auth.php';
