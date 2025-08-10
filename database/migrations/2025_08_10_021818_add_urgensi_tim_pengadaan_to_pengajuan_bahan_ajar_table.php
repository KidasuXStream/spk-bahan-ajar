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
            $table->enum('urgensi_tim_pengadaan', ['tinggi', 'sedang', 'rendah'])->nullable()->after('urgensi_institusi');
            $table->text('catatan_tim_pengadaan')->nullable()->after('catatan_pengadaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_bahan_ajars', function (Blueprint $table) {
            $table->dropColumn(['urgensi_tim_pengadaan', 'catatan_tim_pengadaan']);
        });
    }
};
