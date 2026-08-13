<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motors', function (Blueprint $table) {
            // Nullable en bewust niet in bulk gevuld: net als bij photo_url begint dit bij een
            // kleine, geverifieerde steekproef in plaats van alle 347 in één keer.
            $table->unsignedSmallInteger('seat_height_mm')->nullable()->after('frontal_area_m2');
            $table->string('seat_height_source_url', 400)->nullable()->after('seat_height_mm');
        });
    }

    public function down(): void
    {
        Schema::table('motors', function (Blueprint $table) {
            $table->dropColumn(['seat_height_mm', 'seat_height_source_url']);
        });
    }
};
