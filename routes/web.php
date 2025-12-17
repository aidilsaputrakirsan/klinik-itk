<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\PerawatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TindakanController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard - semua role
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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
    // LAPORAN ROUTES
    // =====================
    Route::middleware('role:superadmin,admin,dokter')->group(function () {
        Route::get('/laporan', function () {
            return Inertia::render('Laporan/Index');
        })->name('laporan.index');
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
    });
});

require __DIR__.'/auth.php';
