<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parkir_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kartu');
            $table->string('plat_nomor');
            $table->string('jenis_kendaraan');
            $table->dateTime('waktu_masuk');
            $table->dateTime('waktu_keluar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('parkir_keluar');
    }
};
