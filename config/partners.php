<?php

/*
 * Weergave-instellingen per partner die niet in de database staan, zodat ze met een deploy
 * meegaan (de productieserver heeft geen php-cli voor een tinker-update).
 * - spot_image: foto voor de partnerplek in ranglijsten, passend bij het eerste aanbod.
 * - ridden:     eigen ervaring van Jake en Rory. Alleen wat zij zelf bevestigd hebben, nooit
 *               aanvullen of een oordeel verzinnen. Bevestigd door Jake op 13 okt 2026.
 */

return [
    'trackdays4all' => [
        'spot_image' => 'images/partners/trackdays4all/tt-circuit-assen.jpg',
        'ridden' => [
            'short' => 'Zelf gereden op 22 september 2026',
            'title' => 'Wij reden hier zelf',
            'text' => 'Op 22 september 2026 reden we een circuitdag van Trackdays4all op TT Circuit Assen. Rory op zijn KTM 1290 Super Duke R, Jake op zijn Honda CB1300.',
            'photos' => [
                ['src' => 'images/over-ons/pitstraat.jpg', 'alt' => 'Rory en Jake Andreas in de pitstraat van TT Circuit Assen op 22 september 2026', 'w' => 1100, 'h' => 1375],
                ['src' => 'images/over-ons/motoren-box.jpg', 'alt' => 'De KTM 1290 Super Duke R en de Honda CB1300 naast elkaar in de box op TT Circuit Assen', 'w' => 1400, 'h' => 1050],
            ],
        ],
    ],
];
