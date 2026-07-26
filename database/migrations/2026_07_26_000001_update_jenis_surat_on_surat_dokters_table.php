<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah jenis_surat dari ENUM menjadi VARCHAR agar dapat menampung surat_rujukan dan jenis surat lainnya
        DB::statement("ALTER TABLE surat_dokters MODIFY COLUMN jenis_surat VARCHAR(255) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE surat_dokters MODIFY COLUMN jenis_surat ENUM('surat_sehat', 'surat_sakit') NOT NULL");
    }
};
