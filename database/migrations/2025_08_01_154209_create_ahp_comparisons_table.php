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
        Schema::create('ahp_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ahp_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('kriteria_1_id')->constrained('kriterias')->onDelete('cascade');
            $table->foreignId('kriteria_2_id')->constrained('kriterias')->onDelete('cascade');
            $table->float('nilai'); // nilai perbandingan (1-9 atau 1/9 - 9)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ahp_comparisons');
    }
};
