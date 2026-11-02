<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesan_chat', function (Blueprint $table) {
            $table->id();
            // user_id dari calon penyewa / penyewa yang memiliki ruang percakapan ini
            $table->foreignId('penyewa_user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            // user_id pengirim pesan (bisa penyewa itu sendiri, admin, atau administrator)
            $table->foreignId('pengirim_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->text('isi_pesan');
            $table->boolean('dibaca')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesan_chat');
    }
};
