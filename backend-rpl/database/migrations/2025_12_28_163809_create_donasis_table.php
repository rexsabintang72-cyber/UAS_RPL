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
    $table->decimal('jumlah', 15, 2);
    $table->enum('status', ['diproses', 'diterima', 'sudah disalurkan'])->default('diproses');
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
