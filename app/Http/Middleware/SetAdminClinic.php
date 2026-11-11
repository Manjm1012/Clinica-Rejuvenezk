<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use App\Support\AdminClinic;
use App\Support\CurrentClinic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aislamiento por clínica en el panel Filament.
 *
 * Fija CurrentClinic con la clínica del usuario autenticado (o la clínica activa por defecto
 * para un super admin sin clinic_id). Así el global scope de BelongsToClinic filtra todos los
 * recursos y el hook "creating" asigna el clinic_id correcto.
 *
 * Se registra como middleware persistente del panel: también corre en las peticiones de
 * Livewire (/livewire/update), donde ResolveClinic resolvería la clínica por host.
 */
class SetAdminClinic
{
    public function handle(Request $request, Closure $next): Response
    {
        $clinicId = AdminClinic::id();
        $clinic = $clinicId ? Clinic::query()->find($clinicId) : null;

        app()->instance(CurrentClinic::class, new CurrentClinic($clinic));
        view()->share('currentClinic', $clinic);

        return $next($request);
    }
}
