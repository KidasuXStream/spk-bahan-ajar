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
        Schema::table('kriterias', function (Blueprint $table) {
            // Hapus field is_active karena tidak logis untuk kriteria
            // Kriteria selalu sama setiap semester, tidak perlu aktif/nonaktif
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kriterias', function (Blueprint $table) {
            // Tambahkan kembali field is_active jika rollback
            $table->boolean('is_active')->default(true)->after('satuan');
        });
    }
};
