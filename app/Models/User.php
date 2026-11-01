<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama',
        'email',
        'password',
        'nomor_telepon',
        'role',
        'status_akun',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function penyewa()
    {
        return $this->hasOne(Penyewa::class, 'user_id');
    }

    public function pengumuman()
    {
        return $this->hasMany(Pengumuman::class, 'admin_id');
    }

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'admin_id');
    }

    public function laporanDitangani()
    {
        return $this->hasMany(Laporan::class, 'ditangani_oleh');
    }

    public function pembayaranDiverifikasi()
    {
        return $this->hasMany(Pembayaran::class, 'diverifikasi_oleh');
    }
}
