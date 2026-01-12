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
    Schema::create('donasis', function (Blueprint $table) {
    $table->id();
    $table->foreignId('donatur_id')->constrained('donaturs')->onDelete('cascade');
    $table->date('tanggal');
    $table->string('jenis_donasi');
    $table->decimal('jumlah', 15, 2)->nullable(); // untuk uang
    $table->string('nama_barang')->nullable();     // untuk barang
    $table->string('jumlah_barang')->nullable();   // untuk barang
    $table->text('keterangan')->nullable();
    $table->enum('status', ['diproses', 'diterima', 'sudah disalurkan', 'ditolak'])->default('diproses');
    $table->enum('verifikasi_admin', ['pending', 'disetujui', 'ditolak'])->default('pending');
    $table->timestamps();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donasis');
    }
};
