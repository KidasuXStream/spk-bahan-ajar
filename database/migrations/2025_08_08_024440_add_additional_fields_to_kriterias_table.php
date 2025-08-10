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
            // Tambah kolom yang hilang
            $table->enum('jenis', ['benefit', 'cost'])->default('benefit')->after('deskripsi');
            $table->string('satuan', 50)->nullable()->after('jenis');
            $table->boolean('is_active')->default(true)->after('satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kriterias', function (Blueprint $table) {
            $table->dropColumn(['jenis', 'satuan', 'is_active']);
        });
    }
};
