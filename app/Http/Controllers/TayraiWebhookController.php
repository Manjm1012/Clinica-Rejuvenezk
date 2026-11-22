<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\TayraiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TayraiWebhookController extends Controller
{
    public function __invoke(Request $request, TayraiService $tayraiService): JsonResponse
    {
        $signature = (string) $request->header('X-Tayrai-Signature', '');
        $payload = $request->getContent();

        if ($signature === '') {
            return response()->json(['message' => 'Missing signature'], 401);
        }

        if (! $tayraiService->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $data = $request->all();
        $tayraiLeadId = data_get($data, 'lead_id');
        $status = data_get($data, 'status');

        if (! is_scalar($tayraiLeadId) || ! is_string($status) || ! array_key_exists($status, Lead::STATUSES)) {
            return response()->json(['message' => 'Invalid payload'], 422);
        }

        // El global scope limita la actualización a la clínica resuelta por el host.
        Lead::query()->where('tayrai_lead_id', (string) $tayraiLeadId)->update(['status' => $status]);

        return response()->json(['ok' => true]);
    }
}
