<?php

use App\Models\parkir;
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
        Schema::table('parkirs', function (Blueprint $table) {
            if (Schema::hasColumn('parkirs', 'nomor kartu')) {
                $table->dropColumn('nomor kartu'); // Hanya hapus jika ada
            }
            $table->string('nomor_kartu')->nullable()->change(); // Pastikan pakai underscore
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parkirs', function (Blueprint $table) {
            //
        });
    }
};
