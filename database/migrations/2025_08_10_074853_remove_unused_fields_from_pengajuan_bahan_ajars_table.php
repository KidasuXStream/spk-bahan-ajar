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
        Schema::table('pengajuan_bahan_ajars', function (Blueprint $table) {
            // Hapus field yang duplikat dan tidak diperlukan
            $table->dropColumn([
                'urgensi_tim_pengadaan',    // Field duplikat untuk urgensi
                'catatan_tim_pengadaan'     // Field catatan khusus yang tidak diperlukan
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_bahan_ajars', function (Blueprint $table) {
            // Tambahkan kembali field jika rollback
            $table->string('urgensi_tim_pengadaan')->nullable()->after('urgensi_institusi');
            $table->text('catatan_tim_pengadaan')->nullable()->after('catatan_pengadaan');
        });
    }
};
