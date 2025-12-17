<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_dokters', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique();
            $table->foreignId('rekam_medis_id')->constrained('rekam_medis')->cascadeOnDelete();
            $table->foreignId('dokter_id')->constrained('users');
            $table->enum('jenis_surat', ['surat_sehat', 'surat_sakit']);
            $table->date('tanggal_surat');
            
            // Untuk Surat Sehat
            $table->string('keperluan')->nullable();
            
            // Untuk Surat Sakit
            $table->integer('jumlah_hari_istirahat')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable()->comment('Path ke file PDF');
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('jenis_surat');
            $table->index('tanggal_surat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_dokters');
    }
};
