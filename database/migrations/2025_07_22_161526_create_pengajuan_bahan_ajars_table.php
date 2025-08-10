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
        Schema::create('pengajuan_bahan_ajars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nama_barang');
            $table->text('spesifikasi');
            $table->string('vendor');
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 12, 2);
            $table->string('masa_pakai');
            $table->integer('stok');
            $table->enum('status_pengajuan', ['diajukan', 'acc_kaprodi', 'ditolak', 'diproses'])->default('diajukan');
            $table->enum('urgensi', ['tinggi', 'sedang', 'rendah']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_bahan_ajars');
    }
};
