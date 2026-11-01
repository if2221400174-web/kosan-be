<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilKos extends Model
{
    protected $table = 'profil_kos';

    protected $fillable = [
        'nama_kos',
        'slug',
        'tagline',
        'deskripsi',
        'logo',
        'foto_utama',
        'alamat',
        'google_maps_url',
        'telepon',
        'email',
        'whatsapp',
        'info_rekening',
        'aturan_kos',
        'jam_malam',
    ];
}
