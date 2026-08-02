<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SetPublicCacheHeaders marks responses as publicly cacheable, but the session- en
 * XSRF-cookies are still attached to the response afterwards (StartSession en
 * VerifyCsrfToken zitten in de 'web'-groep, verder naar binnen dan route-middleware),
 * dus die konden daar niet weggehaald worden. Een cache die Cache-Control: public
 * serieus neemt, mag een response met Set-Cookie eigenlijk niet delen tussen bezoekers.
 *
 * Deze middleware moet daarom vóóraan in de globale 'web'-groep staan (prepend, niet
 * append), zodat 'ie op de terugweg als allerlaatste draait, ná StartSession/
 * VerifyCsrfToken/AddQueuedCookiesToResponse — pas dan staan alle cookies er echt op.
 * Alleen voor gasten (geen ingelogde gebruiker mag zijn sessie kwijtraken) en alleen op
 * responses die al publiek cachebaar zijn (Response::isPublic(), gezet door
 * SetPublicCacheHeaders via setSharedMaxAge()).
 */
class StripGuestCookiesOnPublicPages
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->user() && $response instanceof Response && $response->headers->hasCacheControlDirective('public')) {
            foreach ($response->headers->getCookies() as $cookie) {
                $response->headers->removeCookie($cookie->getName(), $cookie->getPath(), $cookie->getDomain());
            }
        }

        return $response;
    }
}
