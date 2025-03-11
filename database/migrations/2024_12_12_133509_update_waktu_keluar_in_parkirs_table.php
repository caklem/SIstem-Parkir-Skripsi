<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parkirs', function (Blueprint $table) {
            $table->timestamp('waktu_keluar')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('parkirs', function (Blueprint $table) {
            $table->timestamp('waktu_keluar')->nullable(false)->change();
        });
    }
    
};
