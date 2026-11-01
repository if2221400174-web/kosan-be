<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penyewa extends Model
{
    protected $table = 'penyewa';

    protected $fillable = [
        'user_id',
        'nik',
        'jenis_kelamin',
        'alamat_asal',
        'pekerjaan',
        'nama_kontak_darurat',
        'nomor_kontak_darurat',
        'foto_ktp',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kontrakSewa()
    {
        return $this->hasMany(KontrakSewa::class, 'penyewa_id');
    }

    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'penyewa_id');
    }
}
