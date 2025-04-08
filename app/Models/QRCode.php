<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QRCode extends Model
{
    protected $table = 'q_r_codes';
    
    protected $fillable = [
        'nomor_kartu',
        'generated_at'
    ];

    protected $dates = [
        'generated_at',
        'created_at',
        'updated_at'
    ];
}
