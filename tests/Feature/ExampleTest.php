<?php

namespace Tests\Feature;

use App\Models\Motor;
use App\Models\SimulationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('RevRace')
            ->assertSee('Start simulatie');
    }

    public function test_guest_can_run_a_simulation_and_get_a_share_link(): void
    {
        [$motorA, $motorB] = Motor::query()->take(2)->get();

        $response = $this->postJson('/api/simulatie', [
            'motor_a_id' => $motorA->id,
            'motor_b_id' => $motorB->id,
            'road_type' => 'straight',
            'road_condition' => 'dry',
            'distance_m' => 500,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('result.winner', fn ($winner) => in_array($winner, ['A', 'B'], true))
            ->assertJsonStructure(['result' => ['share_code', 'share_url'], 'limit' => ['used', 'remaining']]);

        $this->assertDatabaseCount('simulation_results', 1);
        $this->assertDatabaseCount('simulation_logs', 1);
    }

    public function test_guest_simulation_limit_is_enforced(): void
    {
        [$motorA, $motorB] = Motor::query()->take(2)->get();
        $payload = [
            'motor_a_id' => $motorA->id,
            'motor_b_id' => $motorB->id,
            'road_type' => 'straight',
            'road_condition' => 'dry',
            'distance_m' => 100,
        ];

        for ($i = 0; $i < SimulationLog::LIMIT; $i++) {
            $this->postJson('/api/simulatie', $payload)->assertOk();
        }

        $this->postJson('/api/simulatie', $payload)
            ->assertStatus(429)
            ->assertJsonPath('message', 'Daglimiet bereikt.');
    }

    public function test_user_can_login_and_save_profile(): void
    {
        $user = User::factory()->create(['password' => bcrypt('wachtwoord123')]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wachtwoord123',
        ])->assertRedirect('/simulatie');

        $this->assertAuthenticated();

        $this->post('/profiel', [
            'name' => $user->name,
            'weight_kg' => 84,
            'height_cm' => 183,
            'age' => 33,
            'riding_style' => 'sportief',
            'riding_experience_years' => 9,
            'license_category' => 'A',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'weight_kg' => 84,
        ]);
    }

    public function test_authenticated_user_can_add_motor_to_garage(): void
    {
        $user = User::factory()->create();
        $motor = Motor::query()->firstOrFail();

        $this->actingAs($user)
            ->post('/garage', ['motor_id' => $motor->id])
            ->assertRedirect();

        $this->assertDatabaseHas('garage_motors', [
            'user_id' => $user->id,
            'motor_id' => $motor->id,
        ]);
    }
}
