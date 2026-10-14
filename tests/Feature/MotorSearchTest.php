<?php

namespace Tests\Feature;

use App\Models\Motor;
use App\Services\MotorLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MotorSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Motor::query()->delete();

        foreach ([
            ['KTM', '1290 Super Duke R', 2021],
            ['KTM', '890 Duke R', 2020],
            ['Yamaha', 'MT-09', 2021],
            ['Yamaha', 'YZF-R1', 2021],
            ['BMW', 'R 18', 2021],
            ['Honda', 'CB650R', 2019],
        ] as [$brand, $model, $year]) {
            Motor::create([
                'brand' => $brand, 'model' => $model, 'year' => $year,
                'power_hp' => 100, 'torque_nm' => 80, 'weight_kg' => 200, 'engine_type' => 'test',
                'category' => 'naked', 'displacement_cc' => 900, 'top_speed_kmh' => 230,
                'zero_to_hundred_s' => 3.5, 'drag_coefficient' => 0.6, 'frontal_area_m2' => 0.6,
            ]);
        }
    }

    private function labels(string $query): array
    {
        return collect($this->getJson('/api/motors?q='.urlencode($query))->json('motors'))->pluck('label')->all();
    }

    public function test_modelnaam_zonder_spatie_of_streepje_wordt_gevonden(): void
    {
        $this->assertStringContainsString('Super Duke', $this->labels('ktm superduke')[0] ?? '');
        $this->assertStringContainsString('MT-09', $this->labels('mt09')[0] ?? '');
    }

    public function test_onbekend_bouwjaar_geeft_toch_het_model_en_niet_alles_uit_dat_jaar(): void
    {
        $labels = $this->labels('superduke 2019');

        $this->assertCount(1, $labels);
        $this->assertStringContainsString('Super Duke', $labels[0]);
    }

    public function test_heel_woord_gaat_voor_deel_van_een_woord(): void
    {
        $this->assertStringContainsString('YZF-R1', $this->labels('r1')[0] ?? '');
    }

    public function test_ai_lookup_slaat_een_halve_match_niet_over(): void
    {
        $this->assertNull(app(MotorLookupService::class)->search('superduke 2019', 1)->first());
    }

    public function test_oude_url_van_de_990_super_duke_stuurt_door(): void
    {
        $this->get('/merken/ktm/ktm-super-duke-990-2021')
            ->assertRedirect('/merken/ktm/ktm-990-super-duke-2007')
            ->assertStatus(301);

        $this->get('/vergelijk/ktm-super-duke-990-2021-vs-ktm-1290-super-duke-r-2021')
            ->assertRedirect('/vergelijk/ktm-990-super-duke-2007-vs-ktm-1290-super-duke-r-2021')
            ->assertStatus(301);
    }
}
