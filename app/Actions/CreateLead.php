<?php

namespace App\Actions;

use App\Jobs\SyncLeadToTayrai;
use App\Models\Lead;
use App\Models\Service;
use Illuminate\Http\Request;

/**
 * Crea un lead desde el sitio web o la API y lo sincroniza con TayrAI después de responder.
 */
class CreateLead
{
    public function handle(array $data, Request $request, string $defaultSource): Lead
    {
        // El servicio solo se acepta si es de la clínica actual (el global scope filtra).
        $service = ! empty($data['service_id']) ? Service::query()->find($data['service_id']) : null;

        $lead = Lead::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'message' => $data['message'] ?? null,
            'service_id' => $service?->id,
            'source' => $data['source'] ?? $defaultSource,
            'utm_source' => $request->string('utm_source')->limit(100, '')->toString() ?: null,
            'utm_medium' => $request->string('utm_medium')->limit(100, '')->toString() ?: null,
            'utm_campaign' => $request->string('utm_campaign')->limit(100, '')->toString() ?: null,
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                'referer' => mb_substr((string) $request->headers->get('referer'), 0, 255),
            ],
        ]);

        SyncLeadToTayrai::dispatchAfterResponse($lead->id, $lead->clinic_id);

        return $lead;
    }
}
