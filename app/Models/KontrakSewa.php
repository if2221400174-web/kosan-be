<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KontrakSewa extends Model
{
    protected $table = 'kontrak_sewa';

    protected $fillable = [
        'kode_kontrak',
        'penyewa_id',
        'kamar_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'tipe_sewa',
        'harga_sewa',
        'status_kontrak',
        'catatan',
    ];

    public function penyewa()
    {
        return $this->belongsTo(Penyewa::class, 'penyewa_id');
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }

    public function tagihan()
    {
        return $this->hasMany(Tagihan::class, 'kontrak_sewa_id');
    }
}
