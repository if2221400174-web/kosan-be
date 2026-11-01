<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = [
        'admin_id',
        'judul',
        'isi',
        'tanggal_publikasi',
        'tanggal_berakhir',
        'status',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
