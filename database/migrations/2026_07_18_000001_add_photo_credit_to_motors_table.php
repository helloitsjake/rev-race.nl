<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motors', function (Blueprint $table) {
            $table->string('photo_credit', 200)->nullable()->after('photo_url');
            $table->string('photo_source_url', 400)->nullable()->after('photo_credit');
        });

        // photo_url betekende tot nu toe "externe, door AI verzonnen URL" (2 bestaande waarden
        // bleken kapotte 404-links). Vanaf nu betekent het "lokaal pad naar een geverifieerd,
        // zelf gehost bestand", dus de oude externe waarden zijn niet meer bruikbaar.
        DB::table('motors')->where('photo_url', 'like', 'http%')->update(['photo_url' => null]);
    }

    public function down(): void
    {
        Schema::table('motors', function (Blueprint $table) {
            $table->dropColumn(['photo_credit', 'photo_source_url']);
        });
    }
};
