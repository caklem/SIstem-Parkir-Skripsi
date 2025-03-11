<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParkirController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('parkir')->group(function () {
    Route::get('/', [ParkirController::class, 'index'])->name('parkir.index');
    Route::post('/', [ParkirController::class, 'store'])->name('parkir.store');
    
    // Parkir Keluar routes (pisahkan dari route lain untuk menghindari konflik)
    Route::get('/keluar', [ParkirController::class, 'keluarIndex'])->name('parkir.keluar');
    Route::post('/keluar/cari', [ParkirController::class, 'cariKendaraan'])->name('parkir.cari');
    Route::post('/keluar/proses', [ParkirController::class, 'prosesKeluar'])->name('parkir.proses-keluar');
    Route::get('/keluar/{id}/edit', [ParkirController::class, 'editKeluar'])->name('parkir.keluar.edit');
    Route::put('/keluar/{id}', [ParkirController::class, 'updateKeluar'])->name('parkir.keluar.update');
    Route::get('/keluar/cetak-pdf', [ParkirController::class, 'cetakPdfKeluar'])->name('parkir.keluar.cetak-pdf'); // Route untuk PDF parkir keluar

    // PDF Routes
    Route::get('/masuk/cetak-pdf', [ParkirController::class, 'cetakPdfMasuk'])->name('parkir.masuk.cetak-pdf');
    Route::get('/keluar/cetak-pdf', [ParkirController::class, 'cetakPdfKeluar'])->name('parkir.keluar.cetak-pdf');

    // Basic CRUD routes
    Route::get('/', [ParkirController::class, 'index'])->name('parkir.index');
    Route::post('/', [ParkirController::class, 'store'])->name('parkir.store');
    Route::get('/{parkir}/edit', [ParkirController::class, 'edit'])->name('parkir.edit');
    Route::put('/{parkir}', [ParkirController::class, 'update'])->name('parkir.update');
    Route::put('/{id}', [ParkirController::class, 'update'])->name('parkir.update');
    Route::delete('/{parkir}', [ParkirController::class, 'destroy'])->name('parkir.destroy');
    
    // Search and PDF routes
    Route::get('/search', [ParkirController::class, 'search'])->name('parkir.search');
    Route::get('/cetak-pdf', [ParkirController::class, 'cetakPdf'])->name('parkir.cetak-pdf'); // Route untuk PDF parkir masuk
});