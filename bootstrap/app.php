<?php

use App\Http\Middleware\ResolveClinic;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Webhook endpoint is authenticated by HMAC signature instead of CSRF token.
        $middleware->validateCsrfTokens(except: [
            'webhooks/tayrai',
        ]);

        $middleware->web(append: [
            ResolveClinic::class,
        ]);

        // No existe la ruta 'login' del guard web: los invitados van al login del panel Filament.
        // (Las peticiones JSON siguen recibiendo 401; el portal del paciente usa AuthenticatePatient.)
        $middleware->redirectGuestsTo(fn () => route('filament.admin.auth.login'));

        $middleware->api(append: [
            ResolveClinic::class,
        ]);

        // La clínica debe resolverse ANTES del route-model binding: si no, /procedimientos/{slug}
        // busca el servicio sin el filtro por clínica y expone servicios de otras clínicas.
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: ResolveClinic::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
