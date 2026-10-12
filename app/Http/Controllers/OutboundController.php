<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use App\Models\OutboundClick;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Eén doorgang voor alle links naar aanbodsites. Komt er later een partner of
 * affiliateprogramma bij, dan verandert alleen DESTINATIONS en niet de duizenden pagina's
 * die ernaar linken. Tegelijk telt hij de kliks (zie de migratie van outbound_clicks).
 */
class OutboundController extends Controller
{
    public const DESTINATIONS = ['marktplaats', 'motoroccasion', 'google'];

    /** Volgorde, naam en logo zoals ze naast elkaar op de keuzekaarten staan. */
    public const SHOPS = [
        'marktplaats' => ['label' => 'Marktplaats', 'logo' => 'images/aanbod/marktplaats.svg'],
        'motoroccasion' => ['label' => 'Motoroccasion', 'logo' => 'images/aanbod/motoroccasion.png'],
        'google' => ['label' => 'Google', 'logo' => 'images/aanbod/google.svg'],
    ];

    /**
     * Motoroccasion gebruikt eigen typenamen in de url (/motoren/honda-cb-1300-x4), die niet uit
     * onze modelnaam af te leiden zijn. Staat een motor hier niet in, dan valt de link terug op de
     * merkpagina. Sleutel: shortLabel() in kleine letters. Aanvullen zodra de kliktelling laat zien
     * welke modellen vaak naar Motoroccasion gaan.
     */
    public const MOTOROCCASION_TYPES = [
        'honda cb1300' => 'honda-cb-1300-x4',
    ];

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
            'motoroccasion' => self::motoroccasionUrl($motor),
            'marktplaats' => 'https://www.marktplaats.nl/l/motoren/q/'
                .implode('+', array_map('rawurlencode', preg_split('/\s+/', mb_strtolower($query)))).'/',
            default => 'https://www.google.com/search?q='.urlencode($query.' te koop'),
        };
    }

    /**
     * De drie aanbodlinks voor een motor, via de klikteller.
     *
     * @return array<int, array{key: string, label: string, logo: string, url: string}>
     */
    public static function shopLinks(Motor $motor, string $source): array
    {
        return collect(self::SHOPS)->map(fn ($shop, $key) => [
            'key' => $key,
            'label' => $shop['label'],
            'logo' => asset($shop['logo']),
            'url' => route('outbound', [$motor, $key]).'?van='.$source,
        ])->values()->all();
    }

    private static function motoroccasionUrl(Motor $motor): string
    {
        $base = 'https://www.motoroccasion.nl/motoren';
        $type = self::MOTOROCCASION_TYPES[mb_strtolower($motor->shortLabel())] ?? null;
        $path = $type ?? Str::slug($motor->brand);

        if ($path === '' || $motor->brand === 'Zelfbouw') {
            return $base;
        }

        // Filter op bouwjaar, zoals Motoroccasion het zelf in de url zet.
        return "{$base}/{$path}".($motor->year ? '?filters=bouwjaar-vanaf-'.$motor->year : '');
    }

    private function looksLikeBot(Request $request): bool
    {
        return (bool) preg_match('/bot|crawl|spider|slurp|preview|monitor/i', (string) $request->userAgent());
    }
}
