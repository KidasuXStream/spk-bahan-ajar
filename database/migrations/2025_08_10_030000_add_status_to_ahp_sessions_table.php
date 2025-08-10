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
        Schema::table('ahp_sessions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])
                ->default('pending')
                ->after('semester')
                ->comment('Status perhitungan AHP');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ahp_sessions', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
