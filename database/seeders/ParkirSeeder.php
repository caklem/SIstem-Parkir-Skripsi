<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ParkirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('parkirs')->insert([
            'nomor_kartu' => 'KP001',
            'plat_nomor' => 'N 123 EDG',
            'jenis_kendaraan' => 'Sepeda Motor',
            'waktu_masuk' => Carbon::now(),
            'waktu_keluar' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
