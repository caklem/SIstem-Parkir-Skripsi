<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('parkirs', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kartu')->nullable();
            $table->string('plat_nomor')->unique();
            $table->string('jenis_kendaraan');
            $table->timestamp('waktu_masuk')->nullable();
            $table->timestamp('waktu_keluar')->nullable();
            $table->timestamps();

            // Tambahkan foreign key
            $table->foreign('nomor_kartu')->references('nomor_kartu')->on('kartu_parkirs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('parkirs');
    }
};
