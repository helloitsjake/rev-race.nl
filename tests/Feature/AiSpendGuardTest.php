<?php

namespace Tests\Feature;

use App\Models\AiRejectedQuery;
use App\Models\AiUsageLog;
use App\Models\Motor;
use App\Models\User;
use App\Services\AiSpendGuard;
use App\Services\MotorLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AiSpendGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.openai.key' => 'test-key',
            'services.openai.model' => 'gpt-5.4-mini',
            'ai.daily_budget_usd' => 2.20,
            'ai.lookups_per_user_per_day' => 5,
        ]);
    }

    private function fakeMotorResponse(): void
    {
        Http::fake(['api.openai.com/*' => Http::response([
            'usage' => ['prompt_tokens' => 700, 'completion_tokens' => 500],
            'choices' => [['message' => ['content' => json_encode([
                'brand' => 'Yamaha', 'model' => 'MT-09', 'year' => 2024,
                'power_hp' => 119, 'torque_nm' => 93, 'weight_kg' => 193,
                'engine_type' => '3-cilinder', 'category' => 'naked',
                'displacement_cc' => 890, 'top_speed_kmh' => 220,
                'zero_to_hundred_s' => 3.2, 'drag_coefficient' => 0.6,
                'frontal_area_m2' => 0.6,
            ])]]],
        ])]);
    }

    private function fakeRejection(): void
    {
        Http::fake(['api.openai.com/*' => Http::response([
            'usage' => ['prompt_tokens' => 700, 'completion_tokens' => 12],
            'choices' => [['message' => ['content' => json_encode(['error' => 'not_a_motorcycle'])]]],
        ])]);
    }

    public function test_de_ai_lookup_is_niet_bereikbaar_zonder_account(): void
    {
        $this->postJson('/api/motors/lookup', ['query' => 'Yamaha MT-09 2024'])
            ->assertStatus(401)
            ->assertJson(['login_required' => true]);

        // Belangrijkste assertie: er is geen API-call de deur uit gegaan.
        Http::assertNothingSent();
    }

    public function test_een_geslaagde_lookup_legt_de_echte_tokens_en_kosten_vast(): void
    {
        $this->fakeMotorResponse();

        $motor = app(MotorLookupService::class)->findOrFetch('Yamaha MT-09 2024', null, '10.0.0.1');

        $this->assertSame('Yamaha', $motor->brand);

        $log = AiUsageLog::query()->sole();
        $this->assertSame(AiUsageLog::OUTCOME_SUCCESS, $log->outcome);
        $this->assertSame(700, $log->input_tokens);
        $this->assertSame(500, $log->output_tokens);
        $this->assertSame('10.0.0.1', $log->ip_address);
        // 700/1M * $0,75 + 500/1M * $4,50 = 0.000525 + 0.00225 = 0.002775
        $this->assertSame('0.002775', (string) $log->cost_usd);
    }

    public function test_een_lokale_treffer_kost_niets(): void
    {
        Http::fake();

        // Deze staat in de seed, dus de lookup mag hem lokaal vinden.
        $this->assertNotNull(Motor::query()->where('brand', 'Yamaha')->where('model', 'MT-09')->where('year', 2014)->first());

        app(MotorLookupService::class)->findOrFetch('Yamaha MT-09 2014', null, '10.0.0.1');

        Http::assertNothingSent();
        $this->assertSame(0, AiUsageLog::query()->count());
    }

    public function test_dezelfde_afgewezen_zoekopdracht_kost_maar_een_keer_een_api_call(): void
    {
        $this->fakeRejection();
        $service = app(MotorLookupService::class);

        for ($i = 0; $i < 5; $i++) {
            try {
                $service->findOrFetch('gieter', null, '10.0.0.1');
            } catch (RuntimeException) {
                // afwijzing is hier de verwachte uitkomst
            }
        }

        Http::assertSentCount(1);

        $this->assertSame(1, AiUsageLog::query()->where('outcome', AiUsageLog::OUTCOME_REJECTED)->count());
        $this->assertSame(4, AiUsageLog::query()->where('outcome', AiUsageLog::OUTCOME_CACHED)->count());
        $this->assertSame(5, AiRejectedQuery::query()->sole()->hits);
    }

    public function test_de_negatieve_cache_is_niet_te_omzeilen_met_hoofdletters_of_leestekens(): void
    {
        $this->fakeRejection();
        $service = app(MotorLookupService::class);

        foreach (['gieter', 'GIETER', '  Gieter!!  ', 'gieter???'] as $variant) {
            try {
                $service->findOrFetch($variant, null, '10.0.0.1');
            } catch (RuntimeException) {
                // verwacht
            }
        }

        Http::assertSentCount(1);
    }

    public function test_het_globale_dagbudget_stopt_alle_calls(): void
    {
        $this->fakeMotorResponse();

        // Budget vandaag al vol gemaakt door eerder verbruik.
        AiUsageLog::query()->create([
            'purpose' => AiSpendGuard::PURPOSE_MOTOR_LOOKUP,
            'outcome' => AiUsageLog::OUTCOME_SUCCESS,
            'cost_usd' => 2.20,
        ]);

        $this->expectException(RuntimeException::class);

        try {
            app(MotorLookupService::class)->findOrFetch('Honda CB650R 2024', null, '10.0.0.1');
        } finally {
            Http::assertNothingSent();
            $this->assertSame(1, AiUsageLog::query()->where('outcome', AiUsageLog::OUTCOME_BLOCKED)->count());
        }
    }

    public function test_het_dagbudget_geldt_ook_voor_de_nieuwscrawler(): void
    {
        $guard = app(AiSpendGuard::class);

        $this->assertNull($guard->blockedReason(AiSpendGuard::PURPOSE_NEWS_ARTICLE));

        AiUsageLog::query()->create([
            'purpose' => AiSpendGuard::PURPOSE_NEWS_ARTICLE,
            'outcome' => AiUsageLog::OUTCOME_SUCCESS,
            'cost_usd' => 2.20,
        ]);

        $this->assertSame(AiSpendGuard::BLOCKED_BUDGET, $guard->blockedReason(AiSpendGuard::PURPOSE_NEWS_ARTICLE));
    }

    public function test_een_account_loopt_tegen_de_daglimiet_aan(): void
    {
        $this->fakeMotorResponse();
        $user = User::factory()->create();
        $guard = app(AiSpendGuard::class);

        for ($i = 0; $i < 5; $i++) {
            AiUsageLog::query()->create([
                'purpose' => AiSpendGuard::PURPOSE_MOTOR_LOOKUP,
                'outcome' => AiUsageLog::OUTCOME_SUCCESS,
                'user_id' => $user->id,
                'cost_usd' => 0.0096,
            ]);
        }

        $this->assertSame(AiSpendGuard::BLOCKED_USER_LIMIT, $guard->blockedReason(AiSpendGuard::PURPOSE_MOTOR_LOOKUP, $user));

        try {
            app(MotorLookupService::class)->findOrFetch('Honda CB650R 2024', $user, '10.0.0.1');
            $this->fail('Verwachtte dat de daglimiet de lookup zou blokkeren.');
        } catch (RuntimeException) {
            Http::assertNothingSent();
        }
    }

    public function test_geblokkeerde_en_gecachte_pogingen_vullen_de_persoonlijke_limiet_niet(): void
    {
        $user = User::factory()->create();
        $guard = app(AiSpendGuard::class);

        $guard->recordBlocked(AiSpendGuard::PURPOSE_MOTOR_LOOKUP, AiSpendGuard::BLOCKED_BUDGET, $user, '10.0.0.1');
        $guard->recordCached(AiSpendGuard::PURPOSE_MOTOR_LOOKUP, $user, '10.0.0.1', 'gieter');

        $this->assertSame(0, $guard->userLookupsToday($user));
    }

    public function test_een_onbekend_model_wordt_tegen_het_duurste_tarief_gerekend(): void
    {
        $guard = app(AiSpendGuard::class);

        // 1M in + 1M uit tegen het 'unknown'-tarief van $10/$50.
        $this->assertSame(60.0, $guard->cost('een-onbekend-model', 1_000_000, 1_000_000));
    }
}
