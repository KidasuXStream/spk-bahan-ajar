<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('ahp_comparisons');

        Schema::create('ahp_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ahp_session_id')->constrained('ahp_sessions')->onDelete('cascade');
            $table->foreignId('kriteria_1_id')->constrained('kriterias')->onDelete('cascade');
            $table->foreignId('kriteria_2_id')->constrained('kriterias')->onDelete('cascade');
            $table->decimal('nilai', 10, 6)->default(1);
            $table->timestamps();

            $table->index(['ahp_session_id']);
            $table->index(['kriteria_1_id', 'kriteria_2_id']);
            $table->unique(['ahp_session_id', 'kriteria_1_id', 'kriteria_2_id'], 'ahp_comp_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ahp_comparisons');
    }
};
