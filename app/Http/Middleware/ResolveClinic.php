<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use App\Support\CurrentClinic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveClinic
{
    public function handle(Request $request, Closure $next): Response
    {
        $clinic = null;
        $host = $request->getHost();

        if ($host && ! in_array($host, ['localhost', '127.0.0.1'], true)) {
            $clinic = Clinic::query()
                ->where('is_active', true)
                ->where('domain', $host)
                ->first();
        }

        if (! $clinic && $request->filled('clinic')) {
            $clinic = Clinic::query()
                ->where('is_active', true)
                ->where('slug', $request->string('clinic')->toString())
                ->first();
        }

        if (! $clinic) {
            $clinic = Clinic::query()->where('is_active', true)->orderBy('id')->first();
        }

        app()->instance(CurrentClinic::class, new CurrentClinic($clinic));
        view()->share('currentClinic', $clinic);

        return $next($request);
    }
}
