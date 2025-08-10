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
            $table->foreignId('ahp_session_id')
                ->nullable()
                ->constrained()
                ->after('user_id');

            $table->text('keterangan_penolakan')
                ->nullable()
                ->after('status_pengajuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_bahan_ajars', function (Blueprint $table) {
            $table->dropForeign(['ahp_session_id']);
            $table->dropColumn('ahp_session_id');
            $table->dropColumn('keterangan_penolakan');
        });
    }
};
