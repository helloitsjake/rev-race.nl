<?php

use App\Models\Motor;
use Illuminate\Database\Migrations\Migration;

/**
 * Uitbreiding van 349 naar ruim 500 motoren (14-10-2026). De keuze komt uit Ahrefs-zoekvolume in
 * Nederland per merk: modellen met de meeste zoekopdrachten die nog ontbraken. De specs zijn door
 * AI uitgezocht (rijklaar gewicht), dus verification_status ai_researched: de simulatiepagina
 * toont daar al een melding bij. Merken waarvan de specs te onzeker waren (Voge, Kove, Zontes, QJ,
 * de meeste CFMoto's) staan er bewust niet in.
 *
 * Alleen toevoegen, nooit overschrijven: een motor die er al staat (bijv. met een correctie via
 * "klopt dit niet?") blijft zoals hij is.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->motors() as $motor) {
            Motor::query()->firstOrCreate(
                ['brand' => $motor['brand'], 'model' => $motor['model'], 'year' => $motor['year']],
                $motor + ['api_fetched_at' => now()],
            );
        }
    }

    public function down(): void
    {
        foreach ($this->motors() as $motor) {
            Motor::query()
                ->where('brand', $motor['brand'])
                ->where('model', $motor['model'])
                ->where('year', $motor['year'])
                ->where('source', $motor['source'])
                ->delete();
        }
    }

    private function motors(): array
    {
        return json_decode(file_get_contents(database_path('data/motors-batch-2026-10-14.json')), true);
    }
};
