<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->nullable()->constrained('kamar')->cascadeOnDelete();
            $table->string('judul', 150);
            $table->string('foto', 255);
            $table->string('kategori', 50);
            $table->integer('urutan')->default(0);
            $table->enum('status_publikasi', ['draft', 'terbit'])->default('terbit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri');
    }
};
