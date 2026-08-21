<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * De AI heeft geantwoord dat de invoer geen motorfiets is, of het antwoord viel buiten
 * reeele motorfiets-grenzen. Aparte klasse zodat MotorLookupService deze afwijzing in de
 * negatieve cache kan zetten: dezelfde invoer nog eens kost dan geen API-call meer.
 */
class NotAMotorcycleException extends RuntimeException
{
    public const MESSAGE = 'Dit is geen motorfiets. RevRace ondersteunt alleen motorfietsen, geen auto\'s, vrachtwagens, scooters of andere voertuigen.';

    public function __construct(string $message = self::MESSAGE)
    {
        parent::__construct($message);
    }
}
