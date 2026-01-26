<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('anamnesis', function (Blueprint $table) {
            $table->text('riwayat_keluarga')->nullable()->after('riwayat_penyakit_dahulu');
            $table->integer('skala_nyeri')->nullable()->default(0)->comment('0-10')->after('respirasi');
            
            // Asuhan Keperawatan
            $table->text('diagnosa_keperawatan')->nullable()->after('riwayat_obat');
            $table->text('intervensi_keperawatan')->nullable()->after('diagnosa_keperawatan');
            $table->text('implementasi_keperawatan')->nullable()->after('intervensi_keperawatan');
            $table->text('evaluasi_keperawatan')->nullable()->after('implementasi_keperawatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anamnesis', function (Blueprint $table) {
            $table->dropColumn([
                'riwayat_keluarga',
                'skala_nyeri',
                'diagnosa_keperawatan',
                'intervensi_keperawatan',
                'implementasi_keperawatan',
                'evaluasi_keperawatan'
            ]);
        });
    }
};
