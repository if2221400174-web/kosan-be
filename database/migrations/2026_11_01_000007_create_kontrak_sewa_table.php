<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontrak_sewa', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kontrak', 30)->unique();
            $table->foreignId('penyewa_id')->constrained('penyewa')->restrictOnDelete();
            $table->foreignId('kamar_id')->constrained('kamar')->restrictOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('tipe_sewa', ['bulanan', 'tahunan']);
            $table->decimal('harga_sewa', 12, 2);
            $table->enum('status_kontrak', ['menunggu', 'aktif', 'selesai', 'dibatalkan'])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontrak_sewa');
    }
};
