<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('author_name', 120)->nullable()->after('source_url');
            $table->text('author_bio')->nullable()->after('author_name');
        });

        // Alle content op RevRace komt van dezelfde persoon; backfill bestaande artikelen zodat
        // er geen kennisartikel zonder auteursvermelding blijft staan.
        \Illuminate\Support\Facades\DB::table('articles')->whereNull('author_name')->update([
            'author_name' => 'Jake Andreas',
            'author_bio' => 'Oprichter en bouwer van RevRace. Kennisartikelen worden onderbouwd met officiële bronnen en Ahrefs-zoekwoordonderzoek, "nieuwe releases" worden automatisch herschreven uit geverifieerde motornieuwsbronnen.',
        ]);
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'author_bio']);
        });
    }
};
