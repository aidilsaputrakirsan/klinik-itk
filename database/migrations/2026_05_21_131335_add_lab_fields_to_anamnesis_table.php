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
            $table->integer('gula_darah')->nullable()->comment('mg/dL');
            $table->decimal('asam_urat', 5, 2)->nullable()->comment('mg/dL');
            $table->integer('kolesterol')->nullable()->comment('mg/dL');
            $table->decimal('hemoglobin', 5, 2)->nullable()->comment('g/dL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anamnesis', function (Blueprint $table) {
            $table->dropColumn(['gula_darah', 'asam_urat', 'kolesterol', 'hemoglobin']);
        });
    }
};
