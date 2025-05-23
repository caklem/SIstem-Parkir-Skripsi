<?php

namespace Database\Factories;

use App\Models\Parkir;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ParkirFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Parkir::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $jenisKendaraan = $this->faker->randomElement(['Mobil', 'Motor', 'Bus']);
        $hurufDepan = $this->faker->randomElement(['B', 'D', 'F', 'A', 'T', 'AD', 'AB']);
        $angka = $this->faker->numberBetween(1, 9999);
        $hurufBelakang = Str::upper($this->faker->randomLetter . $this->faker->randomLetter);
        
        return [
            'nomor_kartu' => 'A-' . str_pad($this->faker->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'plat_nomor' => $hurufDepan . ' ' . $angka . ' ' . $hurufBelakang,
            'jenis_kendaraan' => $jenisKendaraan,
            'waktu_masuk' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ];
    }
}