<?php

/*
 * Weergave-instellingen per partner die niet in de database staan, zodat ze met een deploy
 * meegaan (de productieserver heeft geen php-cli voor een tinker-update).
 * - spot_image: foto voor de partnerplek in ranglijsten, passend bij het eerste aanbod.
 */

return [
    'trackdays4all' => [
        'spot_image' => 'images/partners/trackdays4all/tt-circuit-assen.jpg',
    ],
];
