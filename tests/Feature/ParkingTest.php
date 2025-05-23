<?php

// tests/Feature/ParkingTest.php

use App\Models\User;
use App\Models\Parking;
use Illuminate\Support\Facades\Schema;

// Test menggunakan global `uses` untuk TestCase dari tests/Pest.php
// jadi kita tidak perlu menggunakan $this->get secara langsung

test('halaman utama dapat diakses', function () {
    // Kunjungi halaman utama sebagai tamu
    $response = $this->get('/');
    
    // Verifikasi halaman memuat dengan sukses
    $response->assertStatus(200);
});

test('login page dapat diakses', function () {
    $response = $this->get('/login');
    
    $response->assertStatus(200);
});

test('admin dapat melihat daftar parkir', function () {
    // Import Schema secara eksplisit
    if (!Schema::hasTable('users') || !Schema::hasTable('parkings')) {
        $this->markTestSkipped('Tabel users atau parkings tidak ditemukan');
    }
    
    // Buat user admin
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        // asumsi ada field role
        'role' => 'admin'
    ]);
    
    // Buat beberapa data parkir
    Parking::factory()->count(3)->create();
    
    // Login sebagai admin
    $response = $this->actingAs($user)
                     ->get(route('parking.index'));
    
    // Verifikasi halaman berhasil dimuat
    $response->assertStatus(200);
});