<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tagihan';

    protected $fillable = [
        'kode_tagihan',
        'kontrak_sewa_id',
        'periode_mulai',
        'periode_selesai',
        'tanggal_terbit',
        'tanggal_jatuh_tempo',
        'nominal_pokok',
        'denda',
        'jumlah_tagihan',
        'status_tagihan',
        'keterangan',
    ];

    public function kontrakSewa()
    {
        return $this->belongsTo(KontrakSewa::class, 'kontrak_sewa_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id');
    }
}
