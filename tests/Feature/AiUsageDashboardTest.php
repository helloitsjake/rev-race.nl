<?php

namespace Tests\Feature;

use App\Models\AiUsageLog;
use App\Services\AiSpendGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiUsageDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_het_dashboard_bestaat_niet_zonder_token_in_de_config(): void
    {
        config(['ai.dashboard_token' => null]);

        $this->get('/ai-gebruik/wat-dan-ook')->assertNotFound();
    }

    public function test_een_verkeerd_token_geeft_een_404(): void
    {
        config(['ai.dashboard_token' => 'goed-token']);

        $this->get('/ai-gebruik/fout-token')->assertNotFound();
    }

    public function test_het_juiste_token_toont_het_verbruik(): void
    {
        config(['ai.dashboard_token' => 'goed-token']);

        AiUsageLog::query()->create([
            'purpose' => AiSpendGuard::PURPOSE_MOTOR_LOOKUP,
            'outcome' => AiUsageLog::OUTCOME_SUCCESS,
            'ip_address' => '203.0.113.7',
            'model' => 'claude-sonnet-4-6',
            'query' => 'Yamaha MT-09 2024',
            'input_tokens' => 700,
            'output_tokens' => 500,
            'cost_usd' => 0.0096,
        ]);

        $this->get('/ai-gebruik/goed-token')
            ->assertOk()
            ->assertSee('AI-verbruik')
            ->assertSee('203.0.113.7')
            ->assertSee('Yamaha MT-09 2024');
    }

    public function test_de_migratieroute_staat_uit_tenzij_hij_expliciet_aangezet_is(): void
    {
        config(['ai.dashboard_token' => 'goed-token', 'ai.allow_remote_migrate' => false]);

        $this->get('/ai-migratie/goed-token')->assertNotFound();
    }
}
