<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_inloggen_wordt_geremd_na_zes_pogingen(): void
    {
        User::factory()->create(['email' => 'rijder@example.com']);

        // Zes foute pogingen mogen (redirect terug met een validatiefout), de zevende niet.
        for ($i = 1; $i <= 6; $i++) {
            $this->from('/login')
                ->post('/login', ['email' => 'rijder@example.com', 'password' => 'fout'])
                ->assertStatus(302);
        }

        $this->from('/login')
            ->post('/login', ['email' => 'rijder@example.com', 'password' => 'fout'])
            ->assertStatus(429);
    }

    public function test_de_rem_op_inloggen_geldt_ook_bij_het_juiste_wachtwoord(): void
    {
        User::factory()->create(['email' => 'rijder@example.com', 'password' => bcrypt('geheim123')]);

        for ($i = 1; $i <= 6; $i++) {
            $this->from('/login')->post('/login', ['email' => 'rijder@example.com', 'password' => 'fout']);
        }

        // Anders kan een aanvaller de rem omzeilen door de laatste poging te laten slagen.
        $this->from('/login')
            ->post('/login', ['email' => 'rijder@example.com', 'password' => 'geheim123'])
            ->assertStatus(429);
        $this->assertGuest();
    }

    public function test_registreren_wordt_geremd_na_drie_pogingen(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->from('/register')->post('/register', ['email' => 'ongeldig'])->assertStatus(302);
        }

        $this->from('/register')->post('/register', ['email' => 'ongeldig'])->assertStatus(429);
    }

    public function test_een_gewone_bezoeker_kan_nog_gewoon_registreren_en_inloggen(): void
    {
        $this->post('/register', [
            'name' => 'Test Rijder',
            'email' => 'nieuw@example.com',
            'password' => 'geheim123',
            'password_confirmation' => 'geheim123',
        ])->assertRedirect(route('profile.edit'));

        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect();
        $this->assertGuest();

        $this->post('/login', ['email' => 'nieuw@example.com', 'password' => 'geheim123'])
            ->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_de_inlogpagina_zelf_wordt_niet_geremd(): void
    {
        // Alleen de POST heeft een rem; de pagina bezoeken moet vrij blijven.
        for ($i = 1; $i <= 10; $i++) {
            $this->get('/login')->assertOk();
        }
    }

    /**
     * De kern van de fout die dit oploste: ThrottleRequests sleutelt voor een gast op
     * `domain|ip` zonder de route erin, dus alle kale throttle-middleware deelt een teller.
     * Met benoemde limiters mag de ene rem de andere niet meer raken.
     */
    public function test_de_rem_op_registreren_raakt_het_contactformulier_niet(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $this->from('/register')->post('/register', ['email' => 'ongeldig']);
        }

        // Registratie zit nu op zijn limiet.
        $this->from('/register')->post('/register', ['email' => 'ongeldig'])->assertStatus(429);

        // Het contactformulier moet daar niets van merken.
        $this->from('/contact')->post('/contact', ['name' => ''])->assertStatus(302);
    }

    public function test_het_contactformulier_raakt_het_inloggen_niet(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->from('/contact')->post('/contact', ['name' => '']);
        }

        $this->from('/login')
            ->post('/login', ['email' => 'bestaat-niet@example.com', 'password' => 'fout'])
            ->assertStatus(302);
    }

    public function test_handmatige_invoer_raakt_de_ai_lookup_niet(): void
    {
        $user = User::factory()->create();

        // Handmatige invoer heeft een eigen, ruimere rem; die mag de AI-lookup niet opvullen.
        for ($i = 1; $i <= 6; $i++) {
            $this->actingAs($user)->postJson('/api/motors/manual', []);
        }

        // Niet 429: de AI-lookup heeft zijn eigen teller. 404 of 422 is hier prima, want de
        // lookup zelf faalt zonder API-sleutel; het gaat er alleen om dat hij niet geremd is.
        $response = $this->actingAs($user)->postJson('/api/motors/lookup', ['query' => 'iets']);
        $this->assertNotSame(429, $response->status());
    }
}
