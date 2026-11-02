<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesanChat extends Model
{
    use HasFactory;

    protected $table = 'pesan_chat';

    protected $fillable = [
        'penyewa_user_id',
        'pengirim_id',
        'isi_pesan',
        'dibaca',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
    ];

    public function penyewaUser()
    {
        return $this->belongsTo(User::class, 'penyewa_user_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }
}
