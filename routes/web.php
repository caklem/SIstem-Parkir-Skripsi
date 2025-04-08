<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParkirController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QRCodeController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Dashboard routes
    Route::get('/dashboard', [ParkirController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/export-pdf', [ParkirController::class, 'exportDashboardPDF'])->name('dashboard.export-pdf');
    
    Route::prefix('parkir')->group(function () {
        Route::get('/', [ParkirController::class, 'index'])->name('parkir.index');
        Route::post('/', [ParkirController::class, 'store'])->name('parkir.store');
        
        // Parkir Keluar routes (pisahkan dari route lain untuk menghindari konflik)
        Route::get('/keluar', [ParkirController::class, 'keluarIndex'])->name('parkir.keluar'); // Changed from keluar to keluarIndex
        Route::post('/keluar/cari', [ParkirController::class, 'cariKendaraan'])->name('parkir.cari');
        Route::post('/keluar/proses', [ParkirController::class, 'prosesKeluar'])->name('parkir.proses-keluar');
        Route::get('/keluar/{id}/edit', [ParkirController::class, 'editKeluar'])->name('parkir.keluar.edit');
        Route::put('/keluar/{id}', [ParkirController::class, 'updateKeluar'])->name('parkir.keluar.update');
        Route::get('/keluar/cetak-pdf', [ParkirController::class, 'cetakPdfKeluar'])->name('parkir.keluar.cetak-pdf'); // Route untuk PDF parkir keluar
        Route::get('/parkir/keluar/search', [ParkirController::class, 'searchParkirKeluar'])->name('parkir.keluar.search');

        // PDF Routes
        Route::get('/masuk/cetak-pdf', [ParkirController::class, 'cetakPdfMasuk'])->name('parkir.masuk.cetak-pdf');
        Route::get('/keluar/cetak-pdf', [ParkirController::class, 'cetakPdfKeluar'])->name('parkir.keluar.cetak-pdf');
        Route::get('/parkir/cetak-pdf-masuk', [ParkirController::class, 'cetakPdfMasuk'])->name('parkir.cetak-pdf-masuk');
        Route::get('/parkir/cetak-pdf-keluar', [ParkirController::class, 'cetakPdfKeluar'])->name('parkir.cetak-pdf-keluar');

        // Basic CRUD routes
        Route::get('/', [ParkirController::class, 'index'])->name('parkir.index');
        Route::post('/', [ParkirController::class, 'store'])->name('parkir.store');
        Route::get('/{parkir}/edit', [ParkirController::class, 'edit'])->name('parkir.edit');
        Route::put('/{parkir}', [ParkirController::class, 'update'])->name('parkir.update');
        Route::put('/{id}', [ParkirController::class, 'update'])->name('parkir.update');
        Route::delete('/{parkir}', [ParkirController::class, 'destroy'])->name('parkir.destroy');
        Route::get('/dashboard', [ParkirController::class, 'dashboard'])->name('dashboard');
        
        // Search and PDF routes
        Route::get('/search', [ParkirController::class, 'search'])->name('parkir.search');
        Route::get('/cetak-pdf', [ParkirController::class, 'cetakPdf'])->name('parkir.cetak-pdf'); // Route untuk PDF parkir masuk
    });

    Route::prefix('parkir')->group(function () {
        Route::get('/dashboard', [ParkirController::class, 'dashboard'])->name('parkir.dashboard');
        Route::get('/', [ParkirController::class, 'index'])->name('parkir.index');
        Route::get('/keluar', [ParkirController::class, 'keluarIndex'])->name('parkir.keluar'); // Changed from keluar to keluarIndex
    });

    // QR Code routes
    Route::post('/scan-qr', [QRCodeController::class, 'scanQR'])->name('scan.qr');
    Route::get('/generate-qr/{nomorKartu}', [QRCodeController::class, 'generateQR'])->name('generate.qr');
    Route::get('/qrcode/generate', [QRCodeController::class, 'showGenerateForm'])->name('qrcode.generate');
    Route::get('/qrcode/list', [QRCodeController::class, 'index'])->name('qrcode.list');
    Route::post('/qrcode/scan', [QRCodeController::class, 'scanQR'])->name('qrcode.scan');
    Route::post('/parkir/check-card', [ParkirController::class, 'checkCard'])->name('parkir.check-card');

    Route::prefix('qrcode')->group(function() {
        Route::get('/', [QRCodeController::class, 'index'])->name('qrcode.list');
        Route::get('/generate', [QRCodeController::class, 'showGenerateForm'])->name('qrcode.form');
        Route::post('/generate', [QRCodeController::class, 'generateQR'])->name('qrcode.generate');
        Route::get('/print/{nomorKartu}', [QRCodeController::class, 'print'])->name('qrcode.print');
        Route::delete('/{id}', [QRCodeController::class, 'delete'])->name('qrcode.delete');
    });
});