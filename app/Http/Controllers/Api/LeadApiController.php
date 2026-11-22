<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateLead;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use Illuminate\Http\JsonResponse;

class LeadApiController extends Controller
{
    public function store(StoreLeadRequest $request, CreateLead $createLead): JsonResponse
    {
        $lead = $createLead->handle($request->validated(), $request, 'api');

        // Solo datos públicos del lead (sin IP, user agent ni ids del CRM).
        return response()->json([
            'message' => 'Lead creado',
            'data' => $lead->only(['id', 'name', 'phone', 'email', 'service_id', 'source', 'status', 'created_at']),
        ], 201);
    }
}
