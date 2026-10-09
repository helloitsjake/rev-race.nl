<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * RevRace is van Jake en Rory Andreas samen (9 okt 2026). Artikelen die op naam van
 * alleen Jake stonden, krijgen beide namen en de nieuwe bio.
 */
return new class extends Migration
{
    private const BIO = 'Broers en oprichters van RevRace. Ze rijden trackdays op een Honda CB1300 en een KTM 1290 Super Duke. Kennisartikelen onderbouwen ze met officiële bronnen, "nieuwe releases" worden automatisch herschreven uit geverifieerde motornieuwsbronnen.';

    public function up(): void
    {
        DB::table('articles')
            ->where('author_name', 'Jake Andreas')
            ->update(['author_name' => 'Jake en Rory Andreas', 'author_bio' => self::BIO]);
    }

    public function down(): void
    {
        DB::table('articles')
            ->where('author_name', 'Jake en Rory Andreas')
            ->update(['author_name' => 'Jake Andreas']);
    }
};
