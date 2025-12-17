<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resep_obats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemeriksaan_id')->constrained('pemeriksaans')->cascadeOnDelete();
            $table->foreignId('obat_id')->nullable()->constrained('obats')->nullOnDelete();
            $table->string('nama_obat')->comment('Nama obat (bisa custom untuk racikan)');
            $table->string('dosis')->nullable();
            $table->string('aturan_pakai')->nullable()->comment('Contoh: 3x1 sehari');
            $table->integer('jumlah')->default(1);
            $table->string('satuan')->default('tablet');
            $table->boolean('is_racikan')->default(false);
            $table->text('komposisi_racikan')->nullable()->comment('JSON komposisi untuk obat racikan');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resep_obats');
    }
};
