<?php

namespace Tests\Unit;

use App\Models\Motor;
use App\Services\SimulationService;
use Tests\TestCase;

class SimulationServiceTest extends TestCase
{
    private function motor(int $kg, int $hp, int $nm, string $engine): Motor
    {
        return new Motor([
            'weight_kg' => $kg,
            'power_hp' => $hp,
            'torque_nm' => $nm,
            'engine_type' => $engine,
            'drag_coefficient' => 0.6,
            'frontal_area_m2' => 0.6,
        ]);
    }

    // Regressie 12-10-2026: zware motoren kregen een hogere bochtsnelheid, waardoor een CB1300
    // in de bochten van een 1290 Super Duke won.
    public function test_lichtere_sterkere_motor_wint_in_de_bochten(): void
    {
        $light = $this->motor(200, 177, 144, 'V-twin');
        $heavy = $this->motor(256, 100, 117, 'Inline-4');

        $result = app(SimulationService::class)->race($light, $heavy, [
            'road_type' => 'twisty', 'road_condition' => 'dry', 'distance_m' => 1000, 'rider_a_kg' => 75,
        ]);

        $this->assertSame('A', $result['winner']);
    }

    public function test_bij_gelijk_vermogen_is_lichter_sneller_door_de_bochten(): void
    {
        $light = $this->motor(180, 100, 100, 'Inline-4');
        $heavy = $this->motor(250, 100, 100, 'Inline-4');

        $result = app(SimulationService::class)->race($heavy, $light, [
            'road_type' => 'twisty', 'road_condition' => 'dry', 'distance_m' => 500,
        ]);

        $this->assertSame('B', $result['winner']);
    }
}
