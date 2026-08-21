<?php

namespace App\Services;

use App\Exceptions\NotAMotorcycleException;
use App\Models\AiRejectedQuery;
use App\Models\AiUsageLog;
use App\Models\Motor;
use App\Models\User;
use App\Support\AnthropicModel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MotorLookupService
{
    public function __construct(private readonly AiSpendGuard $guard) {}

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

    /**
     * Zoekt eerst lokaal. Levert dat niets op, dan gaat er pas een betaalde AI-call uit, en
     * alleen als alle remmen dat toestaan: de negatieve cache, het globale dagbudget en de
     * limiet per account. Zonder die remmen was dit de enige route op de site waarmee een
     * bezoeker onbeperkt geld kon uitgeven.
     */
    public function findOrFetch(string $query, ?User $user = null, ?string $ip = null): Motor
    {
        $local = $this->search($query, 1)->first();

        if ($local) {
            return $local;
        }

        if (! config('services.anthropic.key')) {
            throw new RuntimeException('Geen motor gevonden en ANTHROPIC_API_KEY is niet ingesteld.');
        }

        $this->guardAgainstKnownRejection($query, $user, $ip);
        $this->guardAgainstLimits($user, $ip, $query);

        try {
            $payload = $this->fetchFromAnthropic($query, $user, $ip);
        } catch (NotAMotorcycleException $exception) {
            $this->rememberRejection($query);

            throw $exception;
        }

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
     * Is deze invoer eerder al afgewezen, dan hoeft Claude er niet nog eens naar te kijken.
     * Dit was het grootste gat: dezelfde onzin-invoer herhalen kostte elke keer opnieuw een
     * volledige API-call.
     */
    private function guardAgainstKnownRejection(string $query, ?User $user, ?string $ip): void
    {
        $known = AiRejectedQuery::query()
            ->where('normalized_query', AiRejectedQuery::normalize($query))
            ->first();

        if ($known === null) {
            return;
        }

        $known->increment('hits');
        $known->forceFill(['last_seen_at' => now()])->save();

        $this->guard->recordCached(AiSpendGuard::PURPOSE_MOTOR_LOOKUP, $user, $ip, $query);

        throw new NotAMotorcycleException;
    }

    private function guardAgainstLimits(?User $user, ?string $ip, string $query): void
    {
        $reason = $this->guard->blockedReason(AiSpendGuard::PURPOSE_MOTOR_LOOKUP, $user);

        if ($reason === null) {
            return;
        }

        $this->guard->recordBlocked(AiSpendGuard::PURPOSE_MOTOR_LOOKUP, $reason, $user, $ip, $query);

        throw new RuntimeException($reason === AiSpendGuard::BLOCKED_USER_LIMIT
            ? sprintf(
                'Je hebt vandaag al %d nieuwe motors laten opzoeken. Morgen kun je weer verder. Alles wat al in RevRace staat blijft gewoon te vinden.',
                $this->guard->userLimit(),
            )
            : 'Het opzoeken van nieuwe motors staat vandaag even uit omdat de daglimiet bereikt is. Alles wat al in RevRace staat blijft gewoon te vinden, en morgen kun je weer nieuwe motors toevoegen.');
    }

    private function rememberRejection(string $query): void
    {
        AiRejectedQuery::query()->updateOrCreate(
            ['normalized_query' => AiRejectedQuery::normalize($query)],
            [
                'original_query' => Str::limit($query, 200, ''),
                'last_seen_at' => now(),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchFromAnthropic(string $query, ?User $user = null, ?string $ip = null): array
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

        $model = AnthropicModel::resolve();

        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
        ])->timeout(20)->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => 700,
            'temperature' => 0,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $query],
            ],
        ]);

        // Vanaf hier is er geld uitgegeven, dus alles wat volgt wordt vastgelegd met de
        // echte token-aantallen uit het antwoord. Zonder dit was achteraf niet te zien
        // wie het budget had opgestookt.
        if ($response->failed()) {
            $this->guard->recordCall(
                AiSpendGuard::PURPOSE_MOTOR_LOOKUP,
                AiUsageLog::OUTCOME_ERROR,
                $model,
                $response->json('usage'),
                $user,
                $ip,
                $query,
            );

            $response->throw();
        }

        $usage = $response->json('usage');

        try {
            $data = $this->decodeSpecifications($response);
        } catch (RuntimeException $exception) {
            $this->guard->recordCall(
                AiSpendGuard::PURPOSE_MOTOR_LOOKUP,
                $exception instanceof NotAMotorcycleException
                    ? AiUsageLog::OUTCOME_REJECTED
                    : AiUsageLog::OUTCOME_ERROR,
                $model,
                $usage,
                $user,
                $ip,
                $query,
            );

            throw $exception;
        }

        $this->guard->recordCall(
            AiSpendGuard::PURPOSE_MOTOR_LOOKUP,
            AiUsageLog::OUTCOME_SUCCESS,
            $model,
            $usage,
            $user,
            $ip,
            $query,
        );

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

    /**
     * Decodeert het API-antwoord en controleert het. Alles wat erop wijst dat de invoer
     * geen motorfiets is gooit NotAMotorcycleException, zodat de afwijzing onthouden wordt
     * en een herhaling geen API-call meer kost.
     *
     * @return array<string, mixed>
     */
    private function decodeSpecifications(\Illuminate\Http\Client\Response $response): array
    {
        $text = (string) Arr::get($response->json(), 'content.0.text');
        $data = json_decode($text, true);

        if (! is_array($data)) {
            throw new RuntimeException('Kon geen geldige motorfiets-specificaties vinden voor deze zoekopdracht. Controleer of je een geldig motormerk en model hebt ingevoerd.');
        }

        if (($data['error'] ?? null) === 'not_a_motorcycle') {
            throw new NotAMotorcycleException;
        }

        foreach (['brand', 'model', 'year', 'power_hp', 'torque_nm', 'weight_kg', 'engine_type', 'displacement_cc'] as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                throw new RuntimeException("Motordata mist verplicht veld: {$field}.");
            }
        }

        // Vangnet tegen niet-motorfietsen die de instructie in de systeem-prompt toch
        // omzeilen: reeele grenzen voor motorfiets-specificaties, zelfde als bij
        // handmatige invoer.
        if ((int) $data['weight_kg'] < 50 || (int) $data['weight_kg'] > 500) {
            throw new NotAMotorcycleException;
        }

        if ((int) $data['displacement_cc'] < 49 || (int) $data['displacement_cc'] > 3000) {
            throw new NotAMotorcycleException;
        }

        if ((int) $data['power_hp'] < 1 || (int) $data['power_hp'] > 600) {
            throw new NotAMotorcycleException;
        }

        return $data;
    }
}
