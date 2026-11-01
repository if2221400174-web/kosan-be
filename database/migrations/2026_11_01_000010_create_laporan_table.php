<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_laporan', 30)->unique();
            $table->foreignId('penyewa_id')->constrained('penyewa')->cascadeOnDelete();
            $table->foreignId('kamar_id')->nullable()->constrained('kamar')->nullOnDelete();
            $table->string('judul', 150);
            $table->text('deskripsi');
            $table->string('foto', 255)->nullable();
            $table->enum('status', ['menunggu', 'diproses', 'selesai', 'ditolak'])->default('menunggu');
            $table->foreignId('ditangani_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('tanggal_selesai')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
