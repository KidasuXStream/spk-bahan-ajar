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
            // Add AHP score field
            if (!Schema::hasColumn('pengajuan_bahan_ajars', 'ahp_score')) {
                $table->decimal('ahp_score', 10, 6)
                    ->nullable()
                    ->after('urgensi_institusi')
                    ->comment('AHP score hasil perhitungan');
            }

            // Add ranking position field
            if (!Schema::hasColumn('pengajuan_bahan_ajars', 'ranking_position')) {
                $table->integer('ranking_position')
                    ->nullable()
                    ->after('ahp_score')
                    ->comment('Posisi ranking berdasarkan AHP score');
            }

            // Add ranking field (legacy support)
            if (!Schema::hasColumn('pengajuan_bahan_ajars', 'ranking')) {
                $table->integer('ranking')
                    ->nullable()
                    ->after('ranking_position')
                    ->comment('Legacy ranking field for backward compatibility');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_bahan_ajars', function (Blueprint $table) {
            $table->dropColumn(['ahp_score', 'ranking_position', 'ranking']);
        });
    }
};
