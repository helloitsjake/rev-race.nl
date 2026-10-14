<?php

use App\Models\Motor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Opruimen op akkoord van Jake (14-10-2026). De eerste drie zijn geen motorfietsen en kwamen
 * binnen via AI-lookups van vóór de bescherming van augustus. "bmw k 1300 s" is een handmatige
 * invoer in kleine letters, dubbel met de BMW K 1300 S uit de batch van vandaag. "Zelfbouw
 * Special" was een testrecord. Gekoppelde simulaties, garage-items, klikken en meldingen gaan mee,
 * anders blokkeren de foreign keys.
 */
return new class extends Migration
{
    private const REMOVE = [
        ['Scania', 'R 500 V8', 2008],
        ['Polaris', 'Slingshot SLR', 2020],
        ['Tribo', 'Mini Racer 49cc CR PRM 6 Orange', 2023],
        ['bmw', 'k 1300 s', 2009],
        ['Zelfbouw', 'Special', 2020],
    ];

    public function up(): void
    {
        foreach (self::REMOVE as [$brand, $model, $year]) {
            // Exacte, hoofdlettergevoelige match: "bmw" mag de echte "BMW K 1300 S" niet raken.
            $ids = Motor::query()
                ->whereRaw('brand = ? and model = ? and year = ?', [$brand, $model, $year])
                ->pluck('id');

            if ($ids->isEmpty()) {
                continue;
            }

            DB::table('simulation_results')->whereIn('motor_a_id', $ids)->orWhereIn('motor_b_id', $ids)->delete();
            DB::table('garage_motors')->whereIn('motor_id', $ids)->delete();
            DB::table('outbound_clicks')->whereIn('motor_id', $ids)->delete();
            DB::table('motor_reports')->whereIn('motor_id', $ids)->delete();
            Motor::query()->whereIn('id', $ids)->delete();
        }
    }

    public function down(): void {}
};
