<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Voorkomt duplicate content: rev-race.nl (zonder www) serveerde tot nu toe de volledige site
 * gewoon zelf, met een canonical-tag die naar zichzelf wees in plaats van naar www.rev-race.nl.
 * Zoekmachines konden zo twee losse versies van dezelfde site indexeren en linkwaarde splitsen.
 */
class EnforceCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if ($canonicalHost && $request->getHost() !== $canonicalHost) {
            return redirect()->to('https://'.$canonicalHost.$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
