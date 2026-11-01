<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $table->string('kode_pengeluaran', 30)->unique();
            $table->string('kategori', 50);
            $table->string('nama_pengeluaran', 150);
            $table->date('tanggal_pengeluaran');
            $table->decimal('jumlah', 12, 2);
            $table->text('keterangan')->nullable();
            $table->string('bukti_pengeluaran', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
