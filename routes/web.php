<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\PerawatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RekamMedisController;
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
Route::get('/dashboard/analitik-pasien', [DashboardController::class, 'analitikPasien'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.analitik-pasien');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // =====================
    // ADMIN ROUTES
    // =====================
    Route::middleware('role:superadmin,admin')->group(function () {
        // Pasien Management
        Route::get('/pasien/create', [PasienController::class, 'create'])->name('pasien.create');
        Route::post('/pasien', [PasienController::class, 'store'])->name('pasien.store');
        Route::get('/pasien/{pasien}/edit', [PasienController::class, 'edit'])->name('pasien.edit');
        Route::put('/pasien/{pasien}', [PasienController::class, 'update'])->name('pasien.update');
        Route::delete('/pasien/{pasien}', [PasienController::class, 'destroy'])->name('pasien.destroy');
        Route::post('/pasien/{pasien}/kunjungan', [PasienController::class, 'daftarKunjungan'])->name('pasien.kunjungan');
        Route::post('/pasien/{pasien}/activate', [PasienController::class, 'activate'])->name('pasien.activate');
        Route::get('/pasien/{pasien}/draf/pdf', [PasienController::class, 'cetakDrafPdf'])->name('pasien.draf.pdf');

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
    // PERAWAT & ANTRIAN ROUTES
    // =====================
    Route::middleware('role:superadmin,admin,perawat')->group(function () {
        Route::get('/perawat/antrian', [PerawatController::class, 'antrian'])->name('perawat.antrian');
        Route::post('/perawat/anamnesis', [PerawatController::class, 'storeAnamnesis'])->name('perawat.anamnesis.store');
        Route::get('/perawat/anamnesis/{rekamMedis}/pdf', [PerawatController::class, 'cetakAnamnesisPdf'])->name('perawat.anamnesis.pdf');
        Route::post('/perawat/anamnesis/{rekamMedis}/send-email', [PerawatController::class, 'sendEmailScreening'])->name('perawat.anamnesis.send-email');
        
        // Admin/Superadmin Antrian Management
        Route::post('/antrian', [PerawatController::class, 'storeAntrian'])->name('antrian.store');
        Route::put('/antrian/{rekamMedis}', [PerawatController::class, 'updateAntrian'])->name('antrian.update');
        Route::delete('/antrian/{rekamMedis}', [PerawatController::class, 'destroyAntrian'])->name('antrian.destroy');
    });

    // =====================
    // DOKTER ROUTES
    // =====================
    Route::middleware('role:superadmin,admin,dokter')->group(function () {
        Route::get('/dokter/antrian', [DokterController::class, 'antrian'])->name('dokter.antrian');
    });
    
    Route::middleware('role:superadmin,dokter')->group(function () {
        Route::post('/dokter/pemeriksaan', [DokterController::class, 'storePemeriksaan'])->name('dokter.pemeriksaan.store');
    });

    // =====================
    // SURAT DOKTER PDF ROUTES
    // =====================
    Route::middleware('role:superadmin,admin,dokter')->group(function () {
        Route::get('/surat-dokter/{suratDokter}/pdf', [SuratDokterController::class, 'generatePdf'])->name('surat-dokter.pdf');
        Route::get('/surat-dokter/{suratDokter}/preview', [SuratDokterController::class, 'previewPdf'])->name('surat-dokter.preview');
    });

    Route::middleware('role:superadmin,admin')->group(function () {
        Route::patch('/surat-dokter/{suratDokter}/nomor', [SuratDokterController::class, 'updateNomor'])->name('surat-dokter.update-nomor');
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
        Route::get('/laporan/pemeriksaan-umum', [LaporanController::class, 'pemeriksaanUmum'])->name('laporan.pemeriksaan-umum');
        Route::get('/laporan/pemeriksaan-umum/pdf', [LaporanController::class, 'pemeriksaanUmumPdf'])->name('laporan.pemeriksaan-umum.pdf');
        Route::get('/laporan/pemeriksaan-umum/excel', [LaporanController::class, 'pemeriksaanUmumExcel'])->name('laporan.pemeriksaan-umum.excel');
        Route::get('/laporan/screening', [LaporanController::class, 'screening'])->name('laporan.screening');
        Route::get('/laporan/screening/pdf', [LaporanController::class, 'screeningPdf'])->name('laporan.screening.pdf');
        Route::get('/laporan/screening/excel', [LaporanController::class, 'screeningExcel'])->name('laporan.screening.excel');
    });

    // =====================
    // REKAM MEDIS (ALL ROLES)
    // =====================
    Route::middleware('role:superadmin,admin,dokter,perawat')->group(function () {
        Route::get('/pasien', [PasienController::class, 'index'])->name('pasien.index');
        Route::get('/pasien/{pasien}', [PasienController::class, 'show'])->name('pasien.show');
        Route::get('/pasien/{pasien}/rekam-medis', [PasienController::class, 'rekamMedis'])->name('pasien.rekam-medis');
        Route::put('/rekam-medis/{rekamMedis}/anamnesis', [RekamMedisController::class, 'updateAnamnesis'])->name('rekam-medis.anamnesis.update');
        Route::put('/rekam-medis/{rekamMedis}/pemeriksaan', [RekamMedisController::class, 'updatePemeriksaan'])->name('rekam-medis.pemeriksaan.update');
        
        // Fitur Export & Import Excel Rekam Medis
        Route::get('/pasien/rekam-medis/template', [RekamMedisController::class, 'downloadTemplate'])->name('pasien.rekam-medis.template');
        Route::post('/pasien/{pasien}/rekam-medis/import', [RekamMedisController::class, 'importExcel'])->name('pasien.rekam-medis.import');
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
        
        // Hapus Rekam Medis
        Route::delete('/rekam-medis/{rekamMedis}', [RekamMedisController::class, 'destroy'])->name('rekam-medis.destroy');
    });

    // =====================
    // ACTIVITY LOGS (ALL ROLES)
    // =====================
    Route::middleware('role:superadmin,admin,perawat,dokter')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});

require __DIR__.'/auth.php';
