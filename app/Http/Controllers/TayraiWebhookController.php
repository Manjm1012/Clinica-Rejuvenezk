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

        if ($signature && ! $tayraiService->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $data = $request->all();
        $tayraiLeadId = data_get($data, 'lead_id');
        $status = data_get($data, 'status');

        if ($tayraiLeadId && $status) {
            Lead::query()->where('tayrai_lead_id', $tayraiLeadId)->update(['status' => $status]);
        }

        return response()->json(['ok' => true]);
    }
}
