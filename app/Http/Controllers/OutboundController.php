<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use App\Models\OutboundClick;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Eén doorgang voor alle links naar aanbodsites. Komt er later een partner of
 * affiliateprogramma bij, dan verandert alleen DESTINATIONS en niet de duizenden pagina's
 * die ernaar linken. Tegelijk telt hij de kliks (zie de migratie van outbound_clicks).
 */
class OutboundController extends Controller
{
    public const DESTINATIONS = ['marktplaats', 'google'];

    public const SOURCES = ['vergelijk', 'vergelijk-balk', 'wizard', 'simulatie', 'model'];

    public function go(Request $request, Motor $motor, string $destination): RedirectResponse
    {
        abort_unless(in_array($destination, self::DESTINATIONS, true), 404);

        $source = $request->query('van');

        if (! $this->looksLikeBot($request)) {
            OutboundClick::query()->create([
                'motor_id' => $motor->id,
                'destination' => $destination,
                'source' => in_array($source, self::SOURCES, true) ? $source : null,
            ]);
        }

        return redirect()->away(self::targetUrl($motor, $destination));
    }

    public static function targetUrl(Motor $motor, string $destination): string
    {
        // shortLabel(): zonder bouwjaar en zonder "(...)"-toelichting, want daarop vindt een
        // marktplaats niets. Gecontroleerd op 12-10-2026: /l/motoren/q/<term>/ geeft resultaten.
        $query = $motor->shortLabel();

        return match ($destination) {
            'marktplaats' => 'https://www.marktplaats.nl/l/motoren/q/'
                .implode('+', array_map('rawurlencode', preg_split('/\s+/', mb_strtolower($query)))).'/',
            default => 'https://www.google.com/search?q='.urlencode($query.' te koop'),
        };
    }

    private function looksLikeBot(Request $request): bool
    {
        return (bool) preg_match('/bot|crawl|spider|slurp|preview|monitor/i', (string) $request->userAgent());
    }
}
