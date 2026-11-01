<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kamar', 20)->unique();
            $table->string('tipe_kamar', 50);
            $table->integer('lantai')->default(1);
            $table->decimal('harga_bulanan', 12, 2);
            $table->decimal('harga_tahunan', 12, 2)->nullable();
            $table->string('luas_kamar', 30)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('foto_utama', 255)->nullable();
            $table->enum('status_kamar', ['tersedia', 'terisi', 'perbaikan', 'nonaktif'])->default('tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};
