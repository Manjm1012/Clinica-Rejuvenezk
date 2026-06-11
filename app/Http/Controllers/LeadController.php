<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Service;
use App\Services\TayraiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(Request $request, TayraiService $tayraiService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:200'],
            'message' => ['nullable', 'string', 'max:2000'],
            'service_id' => ['nullable', 'exists:services,id'],
            'source' => ['nullable', 'string', 'max:100'],
        ]);

        $service = isset($validated['service_id']) ? Service::find($validated['service_id']) : null;

        $lead = Lead::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'message' => $validated['message'] ?? null,
            'service_id' => $service?->id,
            'source' => $validated['source'] ?? 'website',
            'utm_source' => $request->string('utm_source')->toString(),
            'utm_medium' => $request->string('utm_medium')->toString(),
            'utm_campaign' => $request->string('utm_campaign')->toString(),
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->headers->get('referer'),
            ],
        ]);

        $contactId = $tayraiService->upsertContact($lead->name, $lead->phone ?? '', $lead->email ?? '');
        if ($contactId) {
            $lead->tayrai_contact_id = $contactId;
            $lead->tayrai_lead_id = $tayraiService->createLead($lead);
            $lead->save();
        }

        return back()->with('success', 'Tu solicitud fue enviada correctamente. Te contactaremos pronto.');
    }
}
