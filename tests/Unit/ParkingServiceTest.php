<?php

// tests/Unit/ParkingServiceTest.php

use App\Services\ParkingService;
use App\Services\BillingService;
use Mockery;

test('menghitung biaya parkir dengan benar', function () {
    // Buat mock untuk BillingService
    $billingMock = Mockery::mock(BillingService::class);
    
    // Set ekspektasi bahwa calculateFee akan dipanggil dengan parameter tertentu dan mengembalikan nilai 10000
    $billingMock->shouldReceive('calculateFee')
                ->with('mobil', Mockery::any())
                ->andReturn(10000);
    
    // Inject mock ke container
    $this->app->instance(BillingService::class, $billingMock);
    
    // Panggil service yang ingin diuji
    $parkingService = app(ParkingService::class);
    $fee = $parkingService->calculateParkingFee('mobil', now(), now()->addHours(2));
    
    // Verifikasi hasil
    expect($fee)->toBe(10000);
});