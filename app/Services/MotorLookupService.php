<?php

namespace App\Services;

use App\Models\Motor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MotorLookupService
{
    public function search(string $query, int $limit = 8)
    {
        $normalized = trim($query);

        if ($normalized === '') {
            return Motor::query()
                ->orderBy('brand')
                ->orderBy('model')
                ->limit($limit)
                ->get();
        }

        $tokens = preg_split('/\s+/', Str::lower($normalized)) ?: [];

        return Motor::query()
            ->where(function ($builder) use ($tokens) {
                foreach ($tokens as $token) {
                    $like = "%{$token}%";
                    $builder->where(function ($part) use ($like) {
                        $part->where('brand', 'like', $like)
                            ->orWhere('model', 'like', $like)
                            ->orWhere('year', 'like', $like);
                    });
                }
            })
            ->orderBy('brand')
            ->orderBy('model')
            ->limit($limit)
            ->get();
    }

    public function findOrFetch(string $query): Motor
    {
        $local = $this->search($query, 1)->first();

        if ($local) {
            return $local;
        }

        if (! config('services.anthropic.key')) {
            throw new RuntimeException('Geen motor gevonden en ANTHROPIC_API_KEY is niet ingesteld.');
        }

        $payload = $this->fetchFromAnthropic($query);

        return Motor::query()->updateOrCreate(
            [
                'brand' => $payload['brand'],
                'model' => $payload['model'],
                'year' => $payload['year'],
            ],
            $payload + [
                'source' => 'anthropic',
                'api_fetched_at' => now(),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchFromAnthropic(string $query): array
    {
        $system = <<<SYSTEM
Je bent een specificatie-opzoekdienst voor UITSLUITEND motorfietsen (voertuigen op twee
wielen met een motorblok, bijv. naked, sport, tourer, adventure, cruiser, retro).

Deze invoer moet je NIET accepteren, ook niet als er specificaties voor te vinden zijn:
auto's, vrachtwagens, bestelbusjes, quads/ATV's, scooters, brommers/snorfietsen, fietsen
(elektrisch of niet), boten, vliegtuigen, of elk ander voertuig dat geen motorfiets is.
Twijfel je of de invoer een motorfiets beschrijft: wijs af, geef geen specificaties.

Je antwoord bestaat ALTIJD uitsluitend uit één JSON object, niets anders: geen uitleg, geen
vraag, geen markdown, geen tekst voor of na de JSON.

Is de invoer geen motorfiets, of beschrijft die niet duidelijk een specifiek motorfiets
merk+model: antwoord dan uitsluitend met exact dit JSON object:
{"error": "not_a_motorcycle"}

Is de invoer wel een motorfiets, antwoord dan uitsluitend als JSON met exact deze velden:
brand, model, year, power_hp, torque_nm, weight_kg, engine_type, category, displacement_cc,
top_speed_kmh, zero_to_hundred_s, drag_coefficient, frontal_area_m2.
Vermogen, koppel, gewicht, motortype en cilinderinhoud zijn altijd bekend en verplicht.
category moet exact een van deze waarden zijn: naked, sport, tourer, adventure, cruiser, retro.
Kies de categorie die het beste past bij hoe de motor in de markt gepositioneerd wordt.
Cd-waarde en frontaal oppervlak zijn geen officiele fabrieksspecificaties: geef hiervoor
altijd een realistische schatting op basis van het motortype en carrosserie (bijv. naked
~0.55-0.65 Cd, sportief/fairing ~0.35-0.45 Cd, adventure/toermotor ~0.5-0.6 Cd), nooit null.
Gebruik null alleen voor top_speed_kmh of zero_to_hundred_s als die echt onbekend zijn.
SYSTEM;

        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
        ])->timeout(20)->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model', 'claude-sonnet-4-6'),
            'max_tokens' => 700,
            'temperature' => 0,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $query],
            ],
        ]);

        $response->throw();

        $text = (string) Arr::get($response->json(), 'content.0.text');
        $data = json_decode($text, true);

        if (! is_array($data)) {
            throw new RuntimeException('Kon geen geldige motorfiets-specificaties vinden voor deze zoekopdracht. Controleer of je een geldig motormerk en model hebt ingevoerd.');
        }

        if (($data['error'] ?? null) === 'not_a_motorcycle') {
            throw new RuntimeException('Dit is geen motorfiets. RevRace ondersteunt alleen motorfietsen, geen auto\'s, vrachtwagens, scooters of andere voertuigen.');
        }

        foreach (['brand', 'model', 'year', 'power_hp', 'torque_nm', 'weight_kg', 'engine_type', 'displacement_cc'] as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                throw new RuntimeException("Motordata mist verplicht veld: {$field}.");
            }
        }

        // Vangnet tegen niet-motorfietsen die de instructie hierboven toch omzeilen:
        // reeele grenzen voor motorfiets-specificaties, zelfde als bij handmatige invoer.
        if ((int) $data['weight_kg'] < 50 || (int) $data['weight_kg'] > 500) {
            throw new RuntimeException('Dit is geen motorfiets. RevRace ondersteunt alleen motorfietsen, geen auto\'s, vrachtwagens, scooters of andere voertuigen.');
        }

        if ((int) $data['displacement_cc'] < 49 || (int) $data['displacement_cc'] > 3000) {
            throw new RuntimeException('Dit is geen motorfiets. RevRace ondersteunt alleen motorfietsen, geen auto\'s, vrachtwagens, scooters of andere voertuigen.');
        }

        if ((int) $data['power_hp'] < 1 || (int) $data['power_hp'] > 600) {
            throw new RuntimeException('Dit is geen motorfiets. RevRace ondersteunt alleen motorfietsen, geen auto\'s, vrachtwagens, scooters of andere voertuigen.');
        }

        return [
            'brand' => (string) $data['brand'],
            'model' => (string) $data['model'],
            'year' => (int) $data['year'],
            'power_hp' => (int) $data['power_hp'],
            'torque_nm' => (int) $data['torque_nm'],
            'weight_kg' => (int) $data['weight_kg'],
            'engine_type' => (string) $data['engine_type'],
            'category' => array_key_exists((string) ($data['category'] ?? ''), Motor::CATEGORIES) ? $data['category'] : null,
            'displacement_cc' => (int) $data['displacement_cc'],
            'top_speed_kmh' => isset($data['top_speed_kmh']) ? (int) $data['top_speed_kmh'] : null,
            'zero_to_hundred_s' => isset($data['zero_to_hundred_s']) ? (float) $data['zero_to_hundred_s'] : null,
            'drag_coefficient' => is_numeric($data['drag_coefficient'] ?? null) ? (float) $data['drag_coefficient'] : 0.55,
            'frontal_area_m2' => is_numeric($data['frontal_area_m2'] ?? null) ? (float) $data['frontal_area_m2'] : 0.6,
            // Foto's komen via een aparte, geverifieerde pijplijn (echte bron, gedownload en
            // zelf gehost), niet via de AI-lookup: die verzon eerder plausibel klinkende maar
            // kapotte URL's (bijv. voor Yamaha MT-09 en Suzuki Katana, allebei 404).
            'photo_url' => null,
        ];
    }
}
