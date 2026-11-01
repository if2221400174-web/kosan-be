<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    protected $table = 'kamar';

    protected $fillable = [
        'nomor_kamar',
        'tipe_kamar',
        'lantai',
        'harga_bulanan',
        'harga_tahunan',
        'luas_kamar',
        'deskripsi',
        'foto_utama',
        'status_kamar',
    ];

    public function fasilitas()
    {
        return $this->belongsToMany(Fasilitas::class, 'kamar_fasilitas', 'kamar_id', 'fasilitas_id');
    }

    public function kamarFasilitas()
    {
        return $this->hasMany(KamarFasilitas::class, 'kamar_id');
    }

    public function galeri()
    {
        return $this->hasMany(Galeri::class, 'kamar_id');
    }

    public function kontrakSewa()
    {
        return $this->hasMany(KontrakSewa::class, 'kamar_id');
    }

    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'kamar_id');
    }
}
