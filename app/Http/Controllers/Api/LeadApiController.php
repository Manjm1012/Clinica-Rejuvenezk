<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\TayraiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadApiController extends Controller
{
    public function store(Request $request, TayraiService $tayraiService): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:200'],
            'message' => ['nullable', 'string', 'max:2000'],
            'service_id' => ['nullable', 'exists:services,id'],
            'source' => ['nullable', 'string', 'max:100'],
        ]);

        $lead = Lead::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'message' => $validated['message'] ?? null,
            'service_id' => $validated['service_id'] ?? null,
            'source' => $validated['source'] ?? 'api',
        ]);

        $contactId = $tayraiService->upsertContact($lead->name, $lead->phone ?? '', $lead->email ?? '');
        if ($contactId) {
            $lead->tayrai_contact_id = $contactId;
            $lead->tayrai_lead_id = $tayraiService->createLead($lead);
            $lead->save();
        }

        return response()->json(['message' => 'Lead creado', 'data' => $lead], 201);
    }
}
