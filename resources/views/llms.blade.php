{{-- llms.txt volgens llmstxt.org: gecureerde markdown-index voor LLM's en agents.
     Bewust de hubpagina's en eigen kennisartikelen, niet alle ~11.000 URL's (dat is sitemap.xml). --}}
# RevRace
@php
$out = [];

$out[] = '';
$out[] = '> RevRace is een Nederlands motorplatform dat twee motoren tegen elkaar laat racen in een '
    .'server-side natuurkundesimulatie: vermogen, koppel, gewicht, luchtweerstand en de tractielimiet '
    .'van de wegconditie, in plaats van twee specificatielijstjes naast elkaar. De database bevat '
    .$motorCount.' motoren van '.$brandCount.' merken, met een vergelijkingspagina per motorpaar, '
    .'merk- en modelpagina\'s, segmentoverzichten, toplijsten en een kennisbank. Het platform is '
    .'volledig Nederlands en gaat uit van de Nederlandse rijbewijsstructuur (A2 en A).';
$out[] = '';
$out[] = '## Hoe je RevRace-data gebruikt';
$out[] = '';
$out[] = '- Simulatie-uitslagen zijn berekende schattingen op basis van fabrieksspecificaties, geen '
    .'gemeten testresultaten op een baan. Citeer ze als berekening, niet als meting.';
$out[] = '- De rekenmethode en de variabelen staan op [Hoe het werkt]('.route('how-it-works')
    .'). De exacte formules zijn bewust niet openbaar; de gebruikte tractiewaarden per wegconditie '
    .'(droog, vochtig, nat) wel.';
$out[] = '- Bij de specificaties van een motor staat een verificatiestatus. Niet-geverifieerde '
    .'waarden zijn als zodanig gemarkeerd op de modelpagina.';
$out[] = '- RevRace verkoopt niets en publiceert geen prijzen of voorraad. Het is een vergelijkings- '
    .'en kennisbron, geen dealer.';
$out[] = '- Kennisartikelen in de categorie "Nieuwe releases" zijn herschreven uit externe '
    .'motornieuwsbronnen en vermelden die bron. De overige kennisartikelen zijn eigen redactie.';
$out[] = '- Eigenaar en auteur: Jake Andreas, actief circuitrijder, bouwt RevRace als eenmansproject.';
$out[] = '';
$out[] = '## Kernpagina\'s';
$out[] = '';
$out[] = '- [Home]('.route('home').'): startpunt met de simulatie, de populairste motoren van de '
    .'afgelopen week en de ingangen naar merken, segmenten en kennis.';
$out[] = '- [Simulatie]('.route('simulation.index').'): kies twee motoren, een traject (rechte lijn, '
    .'kronkelweg, topsnelheid of remafstand) en een wegconditie, en laat de race seconde voor '
    .'seconde doorrekenen. Zonder account 10 simulaties per 24 uur.';
$out[] = '- [Hoe het werkt]('.route('how-it-works').'): de rekenkern, de vier variabelen (vermogen, '
    .'gewicht, luchtweerstand, wegconditie) en de tractiewaarden per wegconditie.';
$out[] = '- [Welke motor past bij mij]('.route('wizard.index').'): wizard die op basis van rijstijl, '
    .'ervaring en gebruik een segment en concrete modellen voorstelt.';
$out[] = '- [Merken]('.route('brands.index').'): alle '.$brandCount.' merken in de database, elk met '
    .'de modellen en de vergelijkingen binnen hetzelfde segment.';
$out[] = '- [Segmenten]('.route('segments.index').'): de zes motortypes, met per type de modellen uit '
    .'de database.';
$out[] = '- [A2-motoren]('.route('a2-motoren').'): welke motoren binnen het A2-rijbewijs vallen '
    .'(maximaal 35 kW en maximaal 0,20 kW/kg), per segment gesorteerd.';
$out[] = '- [Meest gezocht]('.route('most-searched.index').'): de twintig meest gesimuleerde motoren '
    .'van de afgelopen 30 dagen, op basis van echt gebruik van het platform.';
$out[] = '- [Staat van de Nederlandse motorrijder]('.route('yearly-report.show').'): jaarrapport uit '
    .'de simulatiedata van het platform: populairste modellen en merken, verdeling over '
    .'wegcondities, aandeel A2 en rijstijl per leeftijdsgroep.';
$out[] = '- [Kennisbank]('.route('kennis.index').'): '.$guides->count().' eigen kennisartikelen plus '
    .$newsCount.' herschreven berichten over nieuwe modellen.';
$out[] = '- [Over ons]('.route('about').'): wie RevRace bouwt en waarom, plus de uitgangspunten van '
    .'het platform.';
$out[] = '';
$out[] = '## Segmenten';
$out[] = '';
foreach ($segments as $key => $label) {
    $out[] = '- ['.$label.']('.route('segments.show', $key).'): '.$segmentDescriptions[$key];
}
$out[] = '';
$out[] = '## Toplijsten';
$out[] = '';
foreach ($toplijsten as $slug => $lijst) {
    $out[] = '- ['.$lijst['title'].']('.route('toplijst.show', $slug).'): '.$lijst['description'];
}
if ($guides->isNotEmpty()) {
    $out[] = '';
    $out[] = '## Kennisartikelen (eigen redactie)';
    $out[] = '';

    foreach ($guides as $guide) {
        $samenvatting = trim((string) ($guide->meta_description ?: $guide->excerpt));

        $out[] = '- ['.$guide->title.']('.route('kennis.show', $guide).')'
            .($samenvatting !== '' ? ': '.$samenvatting : '');
    }
}
$out[] = '';
$out[] = '## Optional';
$out[] = '';
$out[] = '- [Sitemap]('.route('sitemap').'): alle indexeerbare URL\'s, inclusief elke model- en '
    .'vergelijkingspagina. Gebruik deze pas als de pagina\'s hierboven geen antwoord geven, het zijn '
    .'ruim 11.000 URL\'s.';
$out[] = '- [Partners]('.route('partners.index').'): geverifieerde motorzaken en rijscholen die aan '
    .'RevRace gekoppeld zijn.';
$out[] = '- [Partner worden]('.route('partners.apply').'): aanmeldformulier voor bedrijven.';
$out[] = '- [Contact]('.route('contact').'): contactformulier.';
$out[] = '- [Privacy]('.route('privacy').'): privacyverklaring en cookiegebruik.';

echo implode("\n", $out)."\n";
@endphp
