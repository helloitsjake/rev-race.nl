<?php

use App\Models\Motor;
use Illuminate\Database\Migrations\Migration;

/**
 * De KTM 990 Super Duke stond als "Super Duke 990 (2021)": de specs (999 cc V-twin, 123 pk,
 * 100 Nm) horen bij de generatie 2007-2013, de 990 is na 2013 niet meer gebouwd. Het jaar zit in
 * de slug, de oude URL's krijgen een 301 via Motor::RENAMED_SLUGS.
 */
return new class extends Migration
{
    public function up(): void
    {
        Motor::query()
            ->where('brand', 'KTM')
            ->where('model', 'Super Duke 990')
            ->where('year', 2021)
            ->update(['model' => '990 Super Duke', 'year' => 2007, 'updated_at' => now()]);
    }

    public function down(): void
    {
        Motor::query()
            ->where('brand', 'KTM')
            ->where('model', '990 Super Duke')
            ->where('year', 2007)
            ->update(['model' => 'Super Duke 990', 'year' => 2021, 'updated_at' => now()]);
    }
};
