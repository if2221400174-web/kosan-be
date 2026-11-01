<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_kos', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kos', 150);
            $table->string('slug', 150)->unique();
            $table->string('tagline', 255)->nullable();
            $table->text('deskripsi');
            $table->string('logo', 255)->nullable();
            $table->string('foto_utama', 255)->nullable();
            $table->text('alamat');
            $table->text('google_maps_url')->nullable();
            $table->string('telepon', 20);
            $table->string('email', 100)->nullable();
            $table->string('whatsapp', 20);
            $table->text('info_rekening')->nullable();
            $table->text('aturan_kos')->nullable();
            $table->time('jam_malam')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_kos');
    }
};
