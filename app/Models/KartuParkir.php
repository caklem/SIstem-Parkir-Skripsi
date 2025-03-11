<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuParkir extends Model
{
    protected $table = 'kartu_parkirs';
    
    protected $fillable = [
        'nomor_kartu',
        'status'
    ];

    protected $casts = [
        'nomor_kartu' => 'integer',
        'status' => 'boolean'
    ];
}