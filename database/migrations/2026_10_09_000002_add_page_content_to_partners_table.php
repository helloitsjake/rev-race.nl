<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optionele inhoud voor een rijkere partnerpagina: een sfeerfoto, kerncijfers, locaties (bij een
 * circuitorganisatie de circuits) en het aanbod. Alles nullable: een partner zonder deze velden
 * krijgt gewoon de bestaande, kortere pagina.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('hero_image', 300)->nullable()->after('logo_url');
            $table->json('facts')->nullable()->after('usps');
            $table->json('venues')->nullable()->after('facts');
            $table->json('offers')->nullable()->after('venues');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['hero_image', 'facts', 'venues', 'offers']);
        });
    }
};
