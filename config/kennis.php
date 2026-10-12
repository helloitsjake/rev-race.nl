<?php

/*
 * Kennis-overzicht: de fases van een motorrijder en per eigen artikel de fase, het icoon en een
 * kerngetal uit de tekst. Een nieuw artikel zonder regel hier valt terug op de categorie
 * (fallback_stage) en krijgt geen kerngetal. Kerngetallen altijd uit het artikel zelf halen,
 * nooit verzinnen. Nieuwsartikelen (Article::NEWS_CATEGORY) staan hier niet in.
 */

return [

    'stages' => [
        'rijbewijs' => ['label' => 'Rijbewijs halen', 'sub' => 'Kosten, leeftijd en examens', 'icon' => 'pas'],
        'eerste-motor' => ['label' => 'Eerste motor', 'sub' => 'Kiezen, passen en A2', 'icon' => 'sleutel'],
        'doorstromen' => ['label' => 'Doorstromen', 'sub' => 'Van A2 naar onbeperkt', 'icon' => 'trap'],
        'ervaren' => ['label' => 'Ervaren rijder', 'sub' => 'Techniek en onderhoud', 'icon' => 'bocht'],
    ],

    'fallback_stage' => [
        'Beginnend motorrijder' => 'eerste-motor',
        'Ervaren motorrijder' => 'ervaren',
    ],

    'articles' => [
        'motorrijbewijs-halen-kosten-leeftijd' => ['stage' => 'rijbewijs', 'icon' => 'euro', 'figure' => '€1.000 tot €1.800', 'figure_label' => 'all-in voor je motorrijbewijs'],
        'eerste-motor-kiezen-waar-op-letten' => ['stage' => 'eerste-motor', 'icon' => 'sleutel', 'figure' => '35 kW', 'figure_label' => 'maximaal vermogen met A2'],
        'welke-motor-past-bij-mijn-lengte' => ['stage' => 'eerste-motor', 'icon' => 'liniaal', 'figure' => '225 mm', 'figure_label' => 'tussen laagste en hoogste zadel'],
        'beste-a2-motoren-per-categorie' => ['stage' => 'eerste-motor', 'icon' => 'lijst', 'figure' => '35', 'figure_label' => 'A2-motoren in onze database'],
        'a2-naar-onbeperkt-rijbewijs-doorstromen' => ['stage' => 'doorstromen', 'icon' => 'trap', 'figure' => '2 jaar', 'figure_label' => 'A2 rijden, en minimaal 22 zijn'],
        'bochtentechniek-ideale-lijn-motor' => ['stage' => 'ervaren', 'icon' => 'bocht', 'figure' => 'Buiten, binnen, buiten', 'figure_label' => 'de ideale lijn door de bocht'],
        'motor-winterklaar-maken-stallingscheck' => ['stage' => 'ervaren', 'icon' => 'sneeuw', 'figure' => '5 checks', 'figure_label' => 'voor je de motor wegzet'],
    ],

    // Lijniconen, 24x24, stroke via currentColor.
    'icons' => [
        'pas' => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="8.5" cy="12" r="2"/><path d="M13 10.5h5M13 13.5h3.5"/>',
        'sleutel' => '<circle cx="8" cy="15" r="4"/><path d="M11 12l8-8M16 7l2 2M14 9l1.5 1.5"/>',
        'trap' => '<path d="M4 19h5v-5h5V9h5"/><path d="M15 4h4v4"/>',
        'bocht' => '<path d="M4 20c0-7 5-6 8-10s2-6 8-6"/><circle cx="12" cy="10" r="1.2" fill="currentColor" stroke="none"/>',
        'euro' => '<path d="M17 6.5A6.5 6.5 0 1 0 17 17.5"/><path d="M4.5 10.5h8M4.5 13.5h8"/>',
        'liniaal' => '<rect x="3" y="8" width="18" height="8" rx="1.5"/><path d="M7 8v3M11 8v4M15 8v3M19 8v2"/>',
        'lijst' => '<path d="M9 7h11M9 12h11M9 17h11"/><circle cx="5" cy="7" r="1" fill="currentColor"/><circle cx="5" cy="12" r="1" fill="currentColor"/><circle cx="5" cy="17" r="1" fill="currentColor"/>',
        'sneeuw' => '<path d="M12 3v18M4.2 7.5l15.6 9M4.2 16.5l15.6-9"/><path d="M9.5 4.5L12 7l2.5-2.5M9.5 19.5L12 17l2.5 2.5"/>',
        'artikel' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/>',
    ],

];
