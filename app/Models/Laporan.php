<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporan';

    protected $fillable = [
        'kode_laporan',
        'penyewa_id',
        'kamar_id',
        'judul',
        'deskripsi',
        'foto',
        'status',
        'ditangani_oleh',
        'tanggal_selesai',
        'catatan_admin',
    ];

    public function penyewa()
    {
        return $this->belongsTo(Penyewa::class, 'penyewa_id');
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }
}
