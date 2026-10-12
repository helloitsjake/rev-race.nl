<?php

namespace Tests\Feature;

use App\Http\Controllers\ComparisonController;
use App\Models\GarageMotor;
use App\Models\Motor;
use App\Models\OutboundClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // De migraties vullen de motortabel met de echte database; hier een eigen set.
        Motor::query()->delete();
    }

    private function motor(string $brand, string $model, int $hp = 42, int $kg = 167): Motor
    {
        return Motor::create([
            'brand' => $brand, 'model' => $model, 'year' => 2019,
            'power_hp' => $hp, 'torque_nm' => 30, 'weight_kg' => $kg, 'engine_type' => 'test',
            'category' => 'sport', 'displacement_cc' => 321, 'top_speed_kmh' => 180,
            'zero_to_hundred_s' => 5.0, 'drag_coefficient' => 0.6, 'frontal_area_m2' => 0.6,
        ]);
    }

    public function test_uitgaande_klik_wordt_geteld_en_stuurt_door_naar_marktplaats(): void
    {
        $motor = $this->motor('Yamaha', 'YZF-R3 (RH12)');

        $this->get(route('outbound', [$motor, 'marktplaats']).'?van=vergelijk')
            ->assertRedirect('https://www.marktplaats.nl/l/motoren/q/yamaha+yzf-r3/');

        $this->assertDatabaseHas('outbound_clicks', ['motor_id' => $motor->id, 'destination' => 'marktplaats', 'source' => 'vergelijk']);
    }

    public function test_motoroccasion_gaat_naar_type_of_anders_naar_het_merk(): void
    {
        $bekend = Motor::create(array_merge($this->motor('Honda', 'CB1300')->only(['power_hp', 'torque_nm', 'weight_kg', 'engine_type', 'category', 'displacement_cc', 'top_speed_kmh', 'zero_to_hundred_s', 'drag_coefficient', 'frontal_area_m2']), ['brand' => 'Honda', 'model' => 'CB1300', 'year' => 2006]));
        $onbekend = $this->motor('Moto Guzzi', 'V85 TT');

        $this->get(route('outbound', [$bekend, 'motoroccasion']).'?van=simulatie')
            ->assertRedirect('https://www.motoroccasion.nl/motoren/honda-cb-1300-x4?filters=bouwjaar-vanaf-2006');
        $this->get(route('outbound', [$onbekend, 'motoroccasion']))
            ->assertRedirect('https://www.motoroccasion.nl/motoren/moto-guzzi?filters=bouwjaar-vanaf-2019');

        $this->assertDatabaseHas('outbound_clicks', ['motor_id' => $bekend->id, 'destination' => 'motoroccasion', 'source' => 'simulatie']);
    }

    public function test_bots_en_onbekende_bestemmingen_tellen_niet(): void
    {
        $motor = $this->motor('Yamaha', 'YZF-R3');

        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1)')
            ->get(route('outbound', [$motor, 'google']))->assertRedirect();
        $this->get('/naar/'.$motor->id.'/ebay')->assertNotFound();

        $this->assertSame(0, OutboundClick::query()->count());
    }

    public function test_vergelijkpagina_toont_race_keuze_en_aanbodlinks(): void
    {
        $a = $this->motor('Yamaha', 'YZF-R3');
        $b = $this->motor('Kawasaki', 'Ninja 400', 45, 168);

        $this->get(route('compare.show', ComparisonController::canonicalSlug($a, $b)))
            ->assertOk()
            ->assertSee('data-race', false)
            ->assertSee('Zoek aanbod op')
            ->assertSee(route('outbound', [$a, 'marktplaats']), false)
            ->assertSee(route('outbound', [$b, 'marktplaats']), false)
            ->assertSee(route('outbound', [$a, 'motoroccasion']), false)
            ->assertSee(route('outbound', [$a, 'google']), false);
    }

    public function test_garageknop_krijgt_json_en_respecteert_de_limiet(): void
    {
        $user = User::factory()->create();
        [$m1, $m2, $m3] = [$this->motor('A', 'Een'), $this->motor('B', 'Twee'), $this->motor('C', 'Drie')];

        $this->actingAs($user)->postJson(route('garage.store'), ['motor_id' => $m1->id])->assertJson(['state' => 'added']);
        $this->actingAs($user)->postJson(route('garage.store'), ['motor_id' => $m1->id])->assertJson(['state' => 'exists']);
        $this->actingAs($user)->postJson(route('garage.store'), ['motor_id' => $m2->id])->assertJson(['state' => 'added']);
        $this->actingAs($user)->postJson(route('garage.store'), ['motor_id' => $m3->id])->assertJson(['state' => 'full']);

        $this->assertSame(2, GarageMotor::query()->where('user_id', $user->id)->count());
    }

    public function test_gast_die_wil_bewaren_krijgt_de_motor_na_registreren_in_de_garage(): void
    {
        $motor = $this->motor('Yamaha', 'YZF-R3');

        $this->get(route('register', ['bewaar' => $motor->id]))->assertOk();
        $this->post(route('register.store'), [
            'name' => 'Rory', 'email' => 'rory@example.com',
            'password' => 'geheim123', 'password_confirmation' => 'geheim123',
        ])->assertRedirect(route('garage.index'));

        $user = User::query()->where('email', 'rory@example.com')->firstOrFail();
        $this->assertTrue($user->garageMotors()->where('motor_id', $motor->id)->exists());
    }
}
