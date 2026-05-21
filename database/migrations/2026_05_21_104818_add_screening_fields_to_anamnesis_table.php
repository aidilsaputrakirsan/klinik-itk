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
            $table->decimal('lingkar_perut', 5, 2)->nullable()->comment('Cm');
            $table->boolean('is_hamil')->default(false);
            $table->string('tindak_lanjut')->nullable();
            $table->text('keterangan_tindak_lanjut')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anamnesis', function (Blueprint $table) {
            $table->dropColumn(['lingkar_perut', 'is_hamil', 'tindak_lanjut', 'keterangan_tindak_lanjut']);
        });
    }
};
