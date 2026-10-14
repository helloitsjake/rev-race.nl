<?php

use App\Models\Motor;
use Illuminate\Database\Migrations\Migration;

/**
 * Zes motoren stonden lokaal wel en live niet (vergeleken op 14-10-2026): de lokale database en
 * productie liepen al een tijd uiteen omdat AI-lookups op beide kanten los van elkaar rijen
 * toevoegden. RSV4 Factory 2023 en Ninja H2 2022 hadden lokaal geen categorie, die is hier gezet.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (json_decode(file_get_contents(database_path('data/motors-sync-2026-10-14.json')), true) as $motor) {
            $existing = Motor::query()->firstOrCreate(
                ['brand' => $motor['brand'], 'model' => $motor['model'], 'year' => $motor['year']],
                $motor,
            );

            if (! $existing->category) {
                $existing->forceFill([
                    'category' => $motor['category'],
                    'verification_status' => $existing->verification_status === 'flagged' ? 'ai_researched' : $existing->verification_status,
                    'data_flags' => null,
                ])->save();
            }
        }
    }

    public function down(): void {}
};
