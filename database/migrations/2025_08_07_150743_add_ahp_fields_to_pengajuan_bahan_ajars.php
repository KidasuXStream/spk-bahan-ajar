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
            // Add urgensi_prodi if not exists
            if (!Schema::hasColumn('pengajuan_bahan_ajars', 'urgensi_prodi')) {
                $table->enum('urgensi_prodi', ['tinggi', 'sedang', 'rendah'])
                    ->nullable()
                    ->after('masa_pakai')
                    ->comment('Urgensi dinilai oleh Prodi');
            }

            // Add urgensi_institusi if not exists
            if (!Schema::hasColumn('pengajuan_bahan_ajars', 'urgensi_institusi')) {
                $table->enum('urgensi_institusi', ['tinggi', 'sedang', 'rendah'])
                    ->nullable()
                    ->after('urgensi_prodi')
                    ->comment('Urgensi dinilai oleh Tim Pengadaan');
            }

            // Add stok if not exists
            if (!Schema::hasColumn('pengajuan_bahan_ajars', 'stok')) {
                $table->integer('stok')
                    ->default(0)
                    ->after('urgensi_institusi')
                    ->comment('Stok existing yang masih ada');
            }

            // Add ahp_session_id if not exists
            if (!Schema::hasColumn('pengajuan_bahan_ajars', 'ahp_session_id')) {
                $table->unsignedBigInteger('ahp_session_id')->nullable()->after('stok');
                $table->foreign('ahp_session_id')->references('id')->on('ahp_sessions')->onDelete('set null');
            }

            // Add ahp_score if not exists
            if (!Schema::hasColumn('pengajuan_bahan_ajars', 'ahp_score')) {
                $table->decimal('ahp_score', 8, 4)->nullable()->after('ahp_session_id');
            }

            // Add ranking_position if not exists
            if (!Schema::hasColumn('pengajuan_bahan_ajars', 'ranking_position')) {
                $table->integer('ranking_position')->nullable()->after('ahp_score');
            }

            // Update harga_satuan precision if needed
            if (Schema::hasColumn('pengajuan_bahan_ajars', 'harga_satuan')) {
                $table->decimal('harga_satuan', 15, 2)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_bahan_ajars', function (Blueprint $table) {
            // Drop columns in reverse order
            $table->dropForeign(['ahp_session_id']);
            $table->dropColumn([
                'urgensi_prodi',
                'urgensi_institusi', 
                'stok',
                'ahp_session_id',
                'ahp_score',
                'ranking_position'
            ]);

            // Revert harga_satuan if needed
            if (Schema::hasColumn('pengajuan_bahan_ajars', 'harga_satuan')) {
                $table->decimal('harga_satuan', 12, 2)->change();
            }
        });
    }
};
