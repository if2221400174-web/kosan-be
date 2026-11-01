<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_tagihan', 30)->unique();
            $table->foreignId('kontrak_sewa_id')->constrained('kontrak_sewa')->restrictOnDelete();
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->date('tanggal_terbit');
            $table->date('tanggal_jatuh_tempo');
            $table->decimal('nominal_pokok', 12, 2);
            $table->decimal('denda', 12, 2)->default(0);
            $table->decimal('jumlah_tagihan', 12, 2);
            $table->enum('status_tagihan', ['belum_bayar', 'sebagian', 'lunas', 'dibatalkan'])->default('belum_bayar');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['kontrak_sewa_id', 'periode_mulai', 'periode_selesai'], 'tagihan_kontrak_periode_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
