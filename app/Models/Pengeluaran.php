<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';

    protected $fillable = [
        'admin_id',
        'kode_pengeluaran',
        'kategori',
        'nama_pengeluaran',
        'tanggal_pengeluaran',
        'jumlah',
        'keterangan',
        'bukti_pengeluaran',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
