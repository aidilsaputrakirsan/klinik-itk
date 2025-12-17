<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anamnesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekam_medis_id')->constrained('rekam_medis')->cascadeOnDelete();
            $table->foreignId('perawat_id')->constrained('users');
            
            // Vital Signs
            $table->string('tekanan_darah')->nullable()->comment('Format: sistol/diastol mmHg');
            $table->decimal('suhu', 4, 1)->nullable()->comment('Celcius');
            $table->integer('nadi')->nullable()->comment('Per menit');
            $table->integer('respirasi')->nullable()->comment('Per menit');
            $table->decimal('berat_badan', 5, 2)->nullable()->comment('Kg');
            $table->decimal('tinggi_badan', 5, 2)->nullable()->comment('Cm');
            
            // Keluhan
            $table->text('keluhan_utama');
            $table->text('riwayat_penyakit_sekarang')->nullable();
            $table->text('riwayat_penyakit_dahulu')->nullable();
            $table->text('riwayat_alergi')->nullable();
            $table->text('riwayat_obat')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anamnesis');
    }
};
