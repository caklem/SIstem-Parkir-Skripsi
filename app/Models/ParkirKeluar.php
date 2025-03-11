<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkirKeluar extends Model
{
    protected $table = 'parkir_keluar';
    
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
}