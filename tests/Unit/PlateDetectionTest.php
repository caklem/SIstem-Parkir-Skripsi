<?php

// tests/Unit/PlateDetectionTest.php

use App\Services\PlateDetectionService;

test('deteksi format plat nomor kendaraan', function ($input, $expected) {
    // Lewati test jika service tidak ada
    if (!class_exists('App\Services\PlateDetectionService')) {
        $this->markTestSkipped('PlateDetectionService tidak tersedia');
    }
    
    // Dapatkan instance PlateDetectionService
    // Asumsi service ini memiliki metode sanitizeLicensePlate
    $service = app(PlateDetectionService::class);
    $result = $service->sanitizeLicensePlate($input);
    
    expect($result)->toBe($expected);
})->with([
    ['B 1234 XYZ', 'B 1234 XYZ'],
    ['B-1234-XYZ', 'B 1234 XYZ'],
    ['B1234XYZ', 'B 1234 XYZ'],
    ['B 1234 xyz', 'B 1234 XYZ'],
    ['b 1234 xyz', 'B 1234 XYZ'],
]);