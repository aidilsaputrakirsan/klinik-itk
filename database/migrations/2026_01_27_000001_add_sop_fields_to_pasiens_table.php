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
        Schema::table('pasiens', function (Blueprint $table) {
            // FASE 1: Field data pasien tambahan
            $table->string('pekerjaan')->nullable()->after('prodi');
            $table->string('status_perkawinan')->nullable()->after('pekerjaan');
            
            // FASE 2: Consent timestamp
            $table->timestamp('consent_at')->nullable()->after('status_perkawinan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasiens', function (Blueprint $table) {
            $table->dropColumn(['pekerjaan', 'status_perkawinan', 'consent_at']);
        });
    }
};
