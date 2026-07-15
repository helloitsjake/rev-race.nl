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
        $prompt = <<<PROMPT
Geef technische specificaties voor deze motorfiets: {$query}.
Antwoord uitsluitend als JSON met exact deze velden:
brand, model, year, power_hp, torque_nm, weight_kg, engine_type, displacement_cc,
top_speed_kmh, zero_to_hundred_s, drag_coefficient, frontal_area_m2, photo_url.
Vermogen, koppel, gewicht, motortype en cilinderinhoud zijn altijd bekend en verplicht.
Cd-waarde en frontaal oppervlak zijn geen officiele fabrieksspecificaties: geef hiervoor
altijd een realistische schatting op basis van het motortype en carrosserie (bijv. naked
~0.55-0.65 Cd, sportief/fairing ~0.35-0.45 Cd, adventure/toermotor ~0.5-0.6 Cd), nooit null.
Gebruik null alleen voor top_speed_kmh, zero_to_hundred_s of photo_url als die echt onbekend zijn.
Geen markdown.
PROMPT;

        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
        ])->timeout(20)->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model', 'claude-sonnet-4-6'),
            'max_tokens' => 700,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $response->throw();

        $text = (string) Arr::get($response->json(), 'content.0.text');
        $data = json_decode($text, true);

        if (! is_array($data)) {
            throw new RuntimeException('Anthropic gaf geen geldige JSON terug.');
        }

        foreach (['brand', 'model', 'year', 'power_hp', 'torque_nm', 'weight_kg', 'engine_type', 'displacement_cc'] as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                throw new RuntimeException("Motordata mist verplicht veld: {$field}.");
            }
        }

        return [
            'brand' => (string) $data['brand'],
            'model' => (string) $data['model'],
            'year' => (int) $data['year'],
            'power_hp' => (int) $data['power_hp'],
            'torque_nm' => (int) $data['torque_nm'],
            'weight_kg' => (int) $data['weight_kg'],
            'engine_type' => (string) $data['engine_type'],
            'displacement_cc' => (int) $data['displacement_cc'],
            'top_speed_kmh' => isset($data['top_speed_kmh']) ? (int) $data['top_speed_kmh'] : null,
            'zero_to_hundred_s' => isset($data['zero_to_hundred_s']) ? (float) $data['zero_to_hundred_s'] : null,
            'drag_coefficient' => is_numeric($data['drag_coefficient'] ?? null) ? (float) $data['drag_coefficient'] : 0.55,
            'frontal_area_m2' => is_numeric($data['frontal_area_m2'] ?? null) ? (float) $data['frontal_area_m2'] : 0.6,
            'photo_url' => $data['photo_url'] ?? null,
        ];
    }
}
