<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyewa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nik', 16)->unique();
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan']);
            $table->text('alamat_asal');
            $table->string('pekerjaan', 100)->nullable();
            $table->string('nama_kontak_darurat', 100)->nullable();
            $table->string('nomor_kontak_darurat', 20)->nullable();
            $table->string('foto_ktp', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyewa');
    }
};
