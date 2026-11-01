<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pembayaran', 30)->unique();
            $table->foreignId('tagihan_id')->constrained('tagihan')->restrictOnDelete();
            $table->dateTime('tanggal_bayar');
            $table->decimal('jumlah_bayar', 12, 2);
            $table->enum('metode_pembayaran', ['tunai', 'transfer', 'qris']);
            $table->string('bukti_bayar', 255)->nullable();
            $table->enum('status_pembayaran', ['menunggu_verifikasi', 'diterima', 'ditolak'])->default('menunggu_verifikasi');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('tanggal_verifikasi')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
