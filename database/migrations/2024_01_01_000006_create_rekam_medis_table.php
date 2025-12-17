<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kunjungan')->unique();
            $table->foreignId('pasien_id')->constrained('pasiens')->cascadeOnDelete();
            $table->foreignId('perawat_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dokter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_kunjungan');
            $table->enum('jenis_layanan', ['berobat', 'surat_sehat', 'screening'])->default('berobat');
            $table->string('keperluan_surat')->nullable()->comment('Keperluan untuk surat sehat');
            $table->string('nama_event')->nullable()->comment('Nama event untuk screening');
            $table->enum('status', [
                'menunggu_perawat',
                'proses_anamnesis',
                'siap_dokter',
                'sedang_diperiksa',
                'selesai',
                'batal'
            ])->default('menunggu_perawat');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tanggal_kunjungan', 'status']);
            $table->index('pasien_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekam_medis');
    }
};
