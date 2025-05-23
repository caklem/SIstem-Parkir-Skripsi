<?php

namespace Database\Factories;

use App\Models\ParkirKeluar;
use App\Models\Parkir;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ParkirKeluarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ParkirKeluar::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $parkir = Parkir::factory()->create();
        $waktuKeluar = Carbon::parse($parkir->waktu_masuk)->addMinutes(rand(30, 480));
        
        return [
            'id_parkir' => $parkir->id,
            'nomor_kartu' => $parkir->nomor_kartu,
            'plat_nomor' => $parkir->plat_nomor,
            'jenis_kendaraan' => $parkir->jenis_kendaraan,
            'waktu_masuk' => $parkir->waktu_masuk,
            'waktu_keluar' => $waktuKeluar,
            'verified_by' => $this->faker->name,
            'verification_method' => $this->faker->randomElement(['manual_selection', 'qr_scan']),
        ];
    }
}