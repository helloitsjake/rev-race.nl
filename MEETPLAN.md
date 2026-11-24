# Meetplan RevRace

Stand: 24 november 2026. Doel: zien wie RevRace gebruikt, welke tools werken en hoeveel
koopintentie we doorsturen. Dat laatste is het bewijs voor partners en dealers.

## Opbouw

| Laag | Wat | Toestemming |
|---|---|---|
| Ahrefs Web Analytics | totaal bezoek, bronnen, pagina's | niet nodig (cookieloos) |
| GA4 `G-25PZJ98VJM` via GTM `GTM-NFH7Z6V5` | gedrag, events, key events | alleen na "Alles toestaan" |
| Database `outbound_clicks` | elke klik naar aanbod, ook zonder toestemming | n.v.t. (server, geen persoonsgegevens) |
| Search Console `sc-domain:rev-race.nl` | organische zoekprestaties | n.v.t. |

**Consent Mode v2.** De layout zet vóór GTM `gtag('consent','default', …)`: analytics op
`granted` als de bezoeker eerder akkoord gaf, anders alles `denied`. Advertentiesignalen staan
altijd op `denied` (er zijn geen advertentietags). De banner zet bij een keuze
`consent update` en pusht `consent_granted` of `consent_denied`. Beide GA4-tags in GTM hebben
de extra eis `analytics_storage`: zonder toestemming gaat er niets naar Google.

Bij nieuwe advertentietags (Meta, Google Ads) moeten de bannertekst en de privacypagina mee,
en moet "Alles toestaan" ook `ad_storage`, `ad_user_data` en `ad_personalization` toekennen.

## Events

Allemaal via `window.rrTrack(event, params)` in `public/js/site.js`. De parameters staan onder
`rr` in de dataLayer. Na een redirect gaat het via `->with('track', [...])` in de controller,
en de layout pusht dat event één keer.

| Event | Wanneer | Parameters | Key event |
|---|---|---|---|
| `offer_click` | klik naar Marktplaats, Motoroccasion of Google via `/naar/` | motor, destination, source | ja |
| `partner_click` | klik naar een partnersite (link met `utm_source=rev-race.nl`) | partner, link_url | ja |
| `generate_lead` | contact- of partnerformulier verzonden (bevestigd door de server) | lead_type | ja |
| `sign_up` | account aangemaakt | method, via | ja |
| `race_complete` | simulatie-uitslag getoond | mode, motor_a, motor_b, road_type, road_condition, source | nee |
| `wizard_complete` | wizard toont advies | ervaring, voorkeur, terrein, merk, matches | nee |
| `wizard_no_match` | wizard vindt niets | | nee |
| `garage_add` | motor in garage gezet | motor_ids | nee |
| `share` | uitslag of garage gedeeld | method | nee |

Elke pageview krijgt `content_group` = de Laravel-routenaam (`compare.show`, `wizard.index`,
…). Zo zie je in GA4 per paginasoort wat er gebeurt, zonder 27.000 losse URL's.

## Intern verkeer

Open één keer `https://www.rev-race.nl/?intern=1` op elk apparaat van Jake en Rory. Daarna
krijgen alle hits `traffic_type=internal`, en het GA4-filter "Internal Traffic" haalt ze eruit.
Terugzetten kan met `?intern=0`.

## In vijf minuten controleren

1. Open de site in een privévenster met `?intern=1` en klik op "Alles toestaan".
2. Open GA4 > Beheer > DebugView, of Realtime: je ziet `page_view`.
3. Draai een race in de simulator: je ziet `race_complete`. Klik op Marktplaats: je ziet `offer_click`.
4. In de console: `dataLayer.filter(e => e.event)` toont alle events.

## Wat het kan breken

- Een nieuwe cookiebanner of consent-tool: de consent-standaard in `layouts/app.blade.php` moet blijven.
- Hernoemde `data-share-*`-attributen, `/naar/`-route of `utm_source` bij partners.
- Een GA4-tag in GTM zonder de eis `analytics_storage`: dat is meten zonder toestemming.

## Bewust niet gedaan

- Geen GA4 zonder toestemming (Consent Mode "advanced", met cookieloze pings): de AP is daar
  streng over, en Ahrefs dekt het totale bezoek al.
- Geen server-side GTM: het volume is daar te klein voor.
