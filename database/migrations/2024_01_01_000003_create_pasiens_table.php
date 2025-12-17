<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasiens', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_rm')->unique()->comment('Nomor Rekam Medis');
            $table->string('nik', 16)->nullable();
            $table->string('nama');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('tipe_pasien', ['mahasiswa', 'dosen', 'tendik', 'umum'])->default('mahasiswa');
            $table->string('nomor_identitas')->nullable()->comment('NIM/NIP');
            $table->string('fakultas')->nullable();
            $table->string('prodi')->nullable();
            $table->string('golongan_darah', 3)->nullable();
            $table->text('riwayat_alergi')->nullable();
            $table->text('riwayat_penyakit')->nullable();
            $table->string('kontak_darurat_nama')->nullable();
            $table->string('kontak_darurat_phone')->nullable();
            $table->string('kontak_darurat_hubungan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['nama', 'nomor_rm']);
            $table->index('tipe_pasien');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasiens');
    }
};
