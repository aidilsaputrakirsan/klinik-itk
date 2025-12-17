<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeriksaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekam_medis_id')->constrained('rekam_medis')->cascadeOnDelete();
            $table->foreignId('dokter_id')->constrained('users');
            
            // Pemeriksaan Fisik
            $table->text('pemeriksaan_fisik')->nullable();
            $table->text('hasil_pemeriksaan')->nullable();
            
            // Diagnosis
            $table->string('diagnosis_utama')->nullable();
            $table->text('diagnosis_sekunder')->nullable();
            $table->string('kode_icd10')->nullable();
            
            // Prognosis & Anjuran
            $table->text('prognosis')->nullable();
            $table->text('anjuran')->nullable();
            
            // Khusus Surat Sakit
            $table->integer('hari_istirahat')->nullable()->comment('Jumlah hari istirahat');
            $table->date('tanggal_mulai_istirahat')->nullable();
            $table->date('tanggal_selesai_istirahat')->nullable();
            
            // Khusus Surat Sehat
            $table->enum('status_kesehatan', ['sehat', 'sehat_bersyarat', 'tidak_sehat'])->nullable();
            $table->text('catatan_kesehatan')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaans');
    }
};
