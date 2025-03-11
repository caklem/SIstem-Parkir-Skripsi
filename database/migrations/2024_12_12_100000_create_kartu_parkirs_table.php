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
        Schema::create('kartu_parkirs', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kartu')->unique();
            $table->boolean('status')->default(0); // 0 = tersedia, 1 = digunakan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('kartu_parkirs');
    }
};
