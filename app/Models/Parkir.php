<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parkir extends Model
{
    protected $table = 'parkirs';
    
    protected $fillable = [
        'nomor_kartu',
        'plat_nomor',
        'jenis_kendaraan',
        'waktu_masuk',
        'waktu_keluar'
    ];

    protected $dates = [
        'waktu_masuk',
        'waktu_keluar'
    ];

    public function kartuParkir()
    {
        return $this->belongsTo(KartuParkir::class, 'nomor_kartu', 'nomor_kartu');
    }
}