<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Client;
use Illuminate\Http\Request;

class TenantResolver
{
    /**
     * Handle an incoming request and resolve tenant.
     */
    public function handle(Request $request, Closure $next)
    {
        // Priority: X-Tenant-Slug header -> subdomain.slug.domain -> query param tenant
        $slug = $request->header('X-Tenant-Slug') ?? null;

        if (empty($slug)) {
            $host = $request->getHost();
            // assume slug.domain.tld or slug.local
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $slug = $parts[0];
            }
        }

        if (empty($slug)) {
            $slug = $request->query('tenant') ?? env('WHATICKET_DEFAULT_CLIENT_ID');
        }

        $client = null;
        if (!empty($slug)) {
            // Try find by slug or external_id
            $client = Client::where('slug', $slug)
                ->orWhere('external_id', $slug)
                ->first();
        }

        if ($client) {
            app()->instance('tenant', $client);
        } else {
            // set null tenant to avoid accidental leakage
            app()->instance('tenant', null);
        }

        return $next($request);
    }
}
