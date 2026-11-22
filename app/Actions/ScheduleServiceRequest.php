<?php

namespace App\Actions;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Convierte una solicitud de renovación en una cita confirmada.
 */
class ScheduleServiceRequest
{
    public function handle(ServiceRequest $request, CarbonInterface $at, ?int $doctorId, ?User $by, ?string $notes = null): Appointment
    {
        return DB::transaction(function () use ($request, $at, $doctorId, $by, $notes): Appointment {
            $patient = $request->patient()->withoutGlobalScope('clinic')->firstOrFail();

            // El médico debe ser de la misma clínica.
            $doctorId = $doctorId
                ? Doctor::query()->withoutGlobalScope('clinic')->where('clinic_id', $request->clinic_id)->whereKey($doctorId)->value('id')
                : null;

            $appointment = $request->appointment_id
                ? Appointment::query()->withoutGlobalScope('clinic')->find($request->appointment_id)
                : null;

            $appointment ??= new Appointment;
            $appointment->fill([
                'service_id' => $request->service_id,
                'patient_id' => $patient->getKey(),
                'doctor_id' => $doctorId,
                'lead_id' => $patient->lead_id,
                'patient_name' => $patient->name,
                'patient_phone' => $patient->phone,
                'patient_email' => $patient->email,
                'appointment_at' => $at,
                'status' => 'confirmed',
                'notes' => $notes ?: 'Renovación solicitada desde el portal del paciente.',
            ]);
            $appointment->clinic_id = $request->clinic_id;
            $appointment->save();

            $request->appointment_id = $appointment->getKey();
            $request->doctor_id = $doctorId;
            $request->status = 'scheduled';
            $request->handled_by = $by?->getKey();
            $request->save();

            return $appointment;
        });
    }
}
