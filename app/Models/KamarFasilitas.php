<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KamarFasilitas extends Model
{
    protected $table = 'kamar_fasilitas';

    public $timestamps = false;

    protected $fillable = [
        'kamar_id',
        'fasilitas_id',
    ];

    public function kamar()
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }

    public function fasilitas()
    {
        return $this->belongsTo(Fasilitas::class, 'fasilitas_id');
    }
}
