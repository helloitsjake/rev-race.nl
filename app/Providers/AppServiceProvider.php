<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerRateLimiters();
    }

    /**
     * Benoemde rate limiters, elk met een eigen teller.
     *
     * Dit moet met benoemde limiters en kan niet met de kale vorm `throttle:6,1`. De sleutel
     * die ThrottleRequests daarvoor gebruikt is namelijk `domain|ip` voor een gast en het
     * gebruikers-id voor een ingelogde bezoeker, ZONDER de route erin. Alle kale
     * throttle-middleware op de site delen dus één teller per bezoeker, elk met een eigen
     * drempel. Praktisch gevolg: drie keer een formulier posten kon het inloggen blokkeren,
     * en een limiet met een langere vervaltijd zette die vervaltijd op de gedeelde teller
     * en remde daarmee ook alle andere formulieren.
     *
     * Een benoemde limiter krijgt zijn naam in de cachesleutel en staat daardoor los.
     */
    private function registerRateLimiters(): void
    {
        // Zonder rem is een wachtwoord onbeperkt te raden. Zes per minuut is ruim voor
        // iemand die zich vertypt en onbruikbaar om te brute-forcen. Bewust op IP en niet
        // op e-mailadres: per e-mail zou een aanvaller vanaf hetzelfde IP eindeloos langs
        // verschillende accounts kunnen sprayen.
        RateLimiter::for('inloggen', fn (Request $request) => Limit::perMinute(6)->by((string) $request->ip()));

        // De AI-lookup is beperkt tot een aantal per account per dag. Zonder rem op
        // registratie is die limiet zinloos: een bot maakt honderd accounts aan en heeft
        // honderd keer zoveel ruimte. E-mailverificatie staat uit, dus die accounts hebben
        // ook geen geldig mailadres nodig.
        RateLimiter::for('registreren', fn (Request $request) => Limit::perMinutes(10, 3)->by((string) $request->ip()));

        // Eigen teller voor de AI-lookup, zodat handmatige invoer die teller niet opvult.
        // Deelden ze er een, dan blokkeerde vijf keer een motor handmatig toevoegen ook de
        // AI-lookup van diezelfde gebruiker.
        RateLimiter::for('ai-lookup', fn (Request $request) => Limit::perMinute(5)
            ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));
    }
}
