<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'document_type' => ['nullable', 'string', 'max:10'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['required', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:120'],
            'allergies' => ['nullable', 'string'],
            'medical_background' => ['nullable', 'string'],
        ]);
            $tenant = app('tenant');

            $rules = [
                'first_name' => ['required', 'string', 'max:120'],
                'last_name' => ['nullable', 'string', 'max:120'],
                'document_type' => ['nullable', 'string', 'max:10'],
                'document_number' => ['nullable', 'string', 'max:50'],
                'birth_date' => ['nullable', 'date'],
                'phone' => ['required', 'string', 'max:25'],
                'email' => ['nullable', 'email', 'max:120'],
                'allergies' => ['nullable', 'string'],
                'medical_background' => ['nullable', 'string'],
            ];

            if (!$tenant) {
                $rules['client_id'] = ['required', 'exists:clients,id'];
            }

            $data = $request->validate($rules);

            if ($tenant) {
                $data['client_id'] = $tenant->id;
            }

        $patient = Patient::updateOrCreate(
            [
                'client_id' => $data['client_id'],
                'phone' => $data['phone'],
            ],
            $data
        );

        return response()->json([
            'status' => 'ok',
            'patient' => $patient,
        ], 201);
    }
}
