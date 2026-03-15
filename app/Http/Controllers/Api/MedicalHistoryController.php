<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalHistory;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalHistoryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'patient_id' => ['nullable', 'exists:patients,id'],
            'patient.first_name' => ['required_without:patient_id', 'string', 'max:120'],
            'patient.last_name' => ['nullable', 'string', 'max:120'],
            'patient.phone' => ['required_without:patient_id', 'string', 'max:25'],
            'patient.email' => ['nullable', 'email', 'max:120'],
            'patient.document_type' => ['nullable', 'string', 'max:10'],
            'patient.document_number' => ['nullable', 'string', 'max:50'],
            'reason_for_consultation' => ['nullable', 'string', 'max:255'],
            'diagnosis' => ['nullable', 'string'],
            'treatment_plan' => ['nullable', 'string'],
            'consulted_at' => ['nullable', 'date'],
            'procedures' => ['nullable', 'array'],
            'procedures.*.name' => ['required', 'string', 'max:180'],
            'procedures.*.notes' => ['nullable', 'string'],
            'procedures.*.amount' => ['nullable', 'numeric'],
            'procedures.*.performed_at' => ['nullable', 'date'],
        ]);

        $patient = $this->resolvePatient($data);

        $history = MedicalHistory::create([
            'client_id' => $data['client_id'],
            'patient_id' => $patient->id,
            'doctor_id' => $data['doctor_id'] ?? null,
            'created_by' => $request->user()?->id,
            'reason_for_consultation' => $data['reason_for_consultation'] ?? null,
            'diagnosis' => $data['diagnosis'] ?? null,
            'treatment_plan' => $data['treatment_plan'] ?? null,
            'consulted_at' => $data['consulted_at'] ?? now(),
        ]);

        foreach ($data['procedures'] ?? [] as $procedure) {
            $history->procedures()->create([
                'client_id' => $data['client_id'],
                'name' => $procedure['name'],
                'notes' => $procedure['notes'] ?? null,
                'amount' => $procedure['amount'] ?? null,
                'performed_at' => $procedure['performed_at'] ?? null,
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'history' => $history->load(['patient', 'doctor', 'procedures']),
        ], 201);
    }

    private function resolvePatient(array $data): Patient
    {
        if (! empty($data['patient_id'])) {
            $patient = Patient::where('id', $data['patient_id'])
                ->where('client_id', $data['client_id'])
                ->first();

            if (! $patient) {
                abort(422, 'The selected patient does not belong to the provided client.');
            }

            return $patient;
        }

        return Patient::updateOrCreate(
            [
                'client_id' => $data['client_id'],
                'phone' => $data['patient']['phone'],
            ],
            [
                'first_name' => $data['patient']['first_name'],
                'last_name' => $data['patient']['last_name'] ?? null,
                'email' => $data['patient']['email'] ?? null,
                'document_type' => $data['patient']['document_type'] ?? null,
                'document_number' => $data['patient']['document_number'] ?? null,
            ]
        );
    }
}
