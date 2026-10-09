<?php

namespace Tests\Feature;

use App\Models\Motor;
use App\Services\MotorAdvisor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MotorAdvisorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // De migraties vullen de motortabel al met de echte database; deze tests werken met
        // een eigen, kleine set zodat de verwachte volgorde vastligt.
        Motor::query()->delete();
    }

    private function motor(string $brand, string $model, int $year, int $hp, int $kg, string $category): Motor
    {
        return Motor::create([
            'brand' => $brand, 'model' => $model, 'year' => $year,
            'power_hp' => $hp, 'torque_nm' => 50, 'weight_kg' => $kg, 'engine_type' => 'test',
            'category' => $category, 'displacement_cc' => 500, 'top_speed_kmh' => 200,
            'zero_to_hundred_s' => 4.0, 'drag_coefficient' => 0.6, 'frontal_area_m2' => 0.6,
        ]);
    }

    private function names(string $ervaring, string $voorkeur): array
    {
        return app(MotorAdvisor::class)->advise($ervaring, $voorkeur)['matches']->map->label()->all();
    }

    public function test_ervaren_rijders_krijgen_bij_plezier_geen_motor_onder_a2_niveau(): void
    {
        $this->motor('Mash', 'Seventy Five', 2018, 11, 110, 'retro');
        $this->motor('Triumph', 'Bonneville T120', 2017, 80, 224, 'retro');
        $this->motor('Honda', 'CB1100 EX', 2017, 90, 255, 'retro');

        $names = $this->names('ervaren', 'relax');

        $this->assertNotContains('Mash Seventy Five 2018', $names);
        $this->assertContains('Triumph Bonneville T120 2017', $names);
    }

    public function test_beginners_krijgen_alleen_a2_motoren_en_geen_125cc_als_er_sterkere_zijn(): void
    {
        $this->motor('Suzuki', 'GSX-R125', 2019, 15, 134, 'sport');
        $this->motor('Yamaha', 'YZF-R3', 2019, 42, 167, 'sport');
        $this->motor('Ducati', 'Panigale V4', 2022, 214, 198, 'sport');

        $this->assertSame(['Yamaha YZF-R3 2019'], $this->names('beginner', 'bochten'));
    }

    public function test_beginners_zonder_sterkere_optie_krijgen_toch_een_advies(): void
    {
        $this->motor('Suzuki', 'GSX-R125', 2019, 15, 134, 'sport');

        $this->assertSame(['Suzuki GSX-R125 2019'], $this->names('beginner', 'bochten'));
    }

    public function test_bochten_voor_ervaren_rijders_kiest_een_licht_middengewicht_boven_de_zwaarste_superbike(): void
    {
        $this->motor('Triumph', 'Daytona 675', 2009, 126, 162, 'sport');
        $this->motor('Ducati', 'Panigale V4 R', 2019, 221, 193, 'sport');
        $this->motor('Kawasaki', 'ZX-14R', 2016, 200, 268, 'sport');

        $this->assertSame('Triumph Daytona 675 2009', $this->names('ervaren', 'bochten')[0]);
    }

    public function test_snelheid_zet_het_meeste_vermogen_per_kilo_bovenaan(): void
    {
        $this->motor('Triumph', 'Daytona 675', 2009, 126, 162, 'sport');
        $this->motor('Ducati', 'Panigale V4 R', 2019, 221, 193, 'sport');

        $this->assertSame('Ducati Panigale V4 R 2019', $this->names('ervaren', 'snelheid')[0]);
    }

    public function test_hetzelfde_model_staat_maar_een_keer_in_het_advies(): void
    {
        $this->motor('Ducati', 'Panigale V4', 2018, 214, 198, 'sport');
        $this->motor('Ducati', 'Panigale V4', 2022, 214, 198, 'sport');
        $this->motor('BMW', 'M1000RR', 2022, 212, 192, 'sport');

        $names = $this->names('ervaren', 'snelheid');

        $this->assertCount(2, $names);
        $this->assertCount(1, array_filter($names, fn ($n) => str_contains($n, 'Panigale V4')));
    }

    public function test_de_wizardpagina_toont_het_advies_van_de_service(): void
    {
        $this->motor('Triumph', 'Bonneville T120', 2017, 80, 224, 'retro');

        $this->get('/welke-motor-past-bij-mij?ervaring=ervaren&voorkeur=relax')
            ->assertOk()
            ->assertSee('Triumph Bonneville T120');
    }
}
