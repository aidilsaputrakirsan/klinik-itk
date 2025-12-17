<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
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

    // Placeholder routes untuk menu (akan diimplementasi nanti)
    Route::get('/pasien', fn() => Inertia::render('Pasien/Index'))->name('pasien.index');
    Route::get('/pasien/create', fn() => Inertia::render('Pasien/Create'))->name('pasien.create');
    Route::get('/perawat/antrian', fn() => Inertia::render('Perawat/Antrian'))->name('perawat.antrian');
    Route::get('/dokter/antrian', fn() => Inertia::render('Dokter/Antrian'))->name('dokter.antrian');
    Route::get('/obat', fn() => Inertia::render('Obat/Index'))->name('obat.index');
    Route::get('/tindakan', fn() => Inertia::render('Tindakan/Index'))->name('tindakan.index');
    Route::get('/laporan', fn() => Inertia::render('Laporan/Index'))->name('laporan.index');
    Route::get('/users', fn() => Inertia::render('Users/Index'))->name('users.index');
});

require __DIR__.'/auth.php';
