<?php

namespace Database\Seeders;

use App\Models\KartuParkir;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Iluminate\Support\Facades\DB;

class KartuParkirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {

        $existingCards = KartuParkir::pluck('nomor_kartu')->toArray();

        // Buat 100 kartu parkir
        for ($i = 1; $i <= 100; $i++) {
            if (!in_array($i, $existingCards)){
                KartuParkir::create([
                    'nomor_kartu' => $i,
                    'status' => 0
                ]);
            }
           
        }
    }
}
