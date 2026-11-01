<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fasilitas extends Model
{
    protected $table = 'fasilitas';

    protected $fillable = [
        'nama_fasilitas',
        'jenis_fasilitas',
        'ikon',
        'deskripsi',
    ];

    public function kamar()
    {
        return $this->belongsToMany(Kamar::class, 'kamar_fasilitas', 'fasilitas_id', 'kamar_id');
    }
}
