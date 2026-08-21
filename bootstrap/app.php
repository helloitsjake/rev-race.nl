<?php

use App\Http\Middleware\EnforceCanonicalHost;
use App\Http\Middleware\SetPublicCacheHeaders;
use App\Http\Middleware\StripGuestCookiesOnPublicPages;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [StripGuestCookiesOnPublicPages::class]);
        $middleware->web(append: [EnforceCanonicalHost::class]);
        $middleware->alias(['cache.public' => SetPublicCacheHeaders::class]);

        // /motoren/{motor}/melding staat op een publiek gecachte modelpagina (cache.public),
        // waar StripGuestCookiesOnPublicPages de sessiecookie van gasten weghaalt — een normaal
        // sessie-CSRF-token kan daar dus nooit kloppen. De melding is zelf niet aan een sessie of
        // identiteit gebonden (iedereen mag melden, geen bevoegde actie), dus CSRF beschermt hier
        // niets; honeypot + rate limit (zie routes/web.php) blijven wel gewoon van kracht.
        $middleware->validateCsrfTokens(except: ['motoren/*/melding']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // De AI-lookup zit achter 'auth'. Zonder deze render krijgt een gast daar
        // "Unauthenticated." te zien; de simulatie toont die tekst rechtstreeks aan de
        // bezoeker en valt daarna terug op het handmatige formulier.
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return new JsonResponse([
                'message' => 'Log in om een nieuwe motor met AI te laten opzoeken. Je kunt de specificaties hieronder ook zelf invullen.',
                'login_required' => true,
            ], 401);
        });
    })->create();
