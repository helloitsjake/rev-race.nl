<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Publieke, niet-persoonlijke pagina's (vergelijkingen, kennisartikelen, home, toplijsten)
 * kregen tot nu toe "Cache-Control: no-cache, private" van Laravel's standaard sessie-
 * middleware, ook al is de inhoud voor iedere bezoeker identiek. Dit voorkwam elke vorm van
 * browser- of CDN-caching. Deze middleware zet expliciet publieke cache-headers op routes
 * waarvan zeker is dat ze geen sessiegebonden content (flash-meldingen, validatiefouten) tonen.
 */
class SetPublicCacheHeaders
{
    public function handle(Request $request, Closure $next, int $maxAge = 300, int $sMaxAge = 3600, int $staleWhileRevalidate = 86400): Response
    {
        $response = $next($request);

        // Let op: Symfony's ResponseHeaderBag herberekent de Cache-Control header zelf zodra
        // "cache-control" via headers->set() wordt gezet (zie ResponseHeaderBag::computeCacheControlValue()),
        // dus een losse stringwaarde zetten wordt stilzwijgend overschreven. De officiële
        // Response-methodes hieronder werken wel via de interne cacheControl-directives.
        if (in_array($request->method(), ['GET', 'HEAD'], true) && $response->isSuccessful()) {
            $response->setMaxAge($maxAge);
            $response->setSharedMaxAge($sMaxAge);
            $response->setStaleWhileRevalidate($staleWhileRevalidate);
        }

        return $response;
    }
}
