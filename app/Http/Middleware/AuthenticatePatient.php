<?php

namespace App\Http\Middleware;

use App\Support\CurrentClinic;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege el portal del paciente. Se usa por clase (no 'auth:patient') porque el guard web
 * no tiene ruta 'login' y Laravel lanzaría RouteNotFoundException.
 */
class AuthenticatePatient
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('patient');

        if (! $guard->check()) {
            // Puede quedar el id en sesión si el paciente no pertenece a la clínica actual
            // (el global scope no lo encuentra): limpiamos la sesión del guard igualmente.
            if ($request->hasSession() && $request->session()->has($guard->getName())) {
                $guard->logout();
            }

            return redirect()->guest(route('patient.login'));
        }

        // Si la clínica resuelta cambió (p. ej. ?clinic=otra), la sesión del paciente no aplica aquí.
        $clinicId = app()->bound(CurrentClinic::class) ? app(CurrentClinic::class)->id() : null;

        if ($clinicId && (int) $guard->user()->clinic_id !== (int) $clinicId) {
            $guard->logout();
            $request->session()->regenerateToken();

            return redirect()->route('patient.login')
                ->with('status', 'Por seguridad cerramos tu sesión. Ingresa de nuevo.');
        }

        return $next($request);
    }
}
