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
            // Add alasan_penolakan if not exists
            if (!Schema::hasColumn('pengajuan_bahan_ajars', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')
                    ->nullable()
                    ->after('status_pengajuan')
                    ->comment('Alasan penolakan dari Kaprodi');
            }

            // Add catatan_pengadaan if not exists
            if (!Schema::hasColumn('pengajuan_bahan_ajars', 'catatan_pengadaan')) {
                $table->text('catatan_pengadaan')
                    ->nullable()
                    ->after('alasan_penolakan')
                    ->comment('Catatan dari Tim Pengadaan untuk pengajuan ini');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_bahan_ajars', function (Blueprint $table) {
            $table->dropColumn(['alasan_penolakan', 'catatan_pengadaan']);
        });
    }
};
