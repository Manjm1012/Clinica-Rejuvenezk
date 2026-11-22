<?php

namespace App\Filament\Doctor\Resources\PrescriptionResource\Pages;

use App\Filament\Doctor\Resources\PrescriptionResource;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Prescription;
use App\Support\AdminClinic;
use App\Support\CurrentDoctor;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePrescription extends CreateRecord
{
    protected static string $resource = PrescriptionResource::class;

    protected static ?string $title = 'Nueva fórmula';

    protected function handleRecordCreation(array $data): Model
    {
        $clinicId = AdminClinic::id();
        $doctorId = CurrentDoctor::id();
        abort_unless($clinicId && $doctorId, 403);

        $patient = Patient::query()->withoutGlobalScope('clinic')
            ->where('clinic_id', $clinicId)
            ->findOrFail($data['patient_id']);

        // La consulta, si se indica, debe ser del mismo paciente.
        if (! empty($data['consultation_id'])) {
            $data['consultation_id'] = Consultation::query()->withoutGlobalScope('clinic')
                ->where('clinic_id', $clinicId)
                ->where('patient_id', $patient->getKey())
                ->whereKey($data['consultation_id'])
                ->value('id');
        }

        $prescription = new Prescription($data);
        $prescription->clinic_id = $clinicId;
        $prescription->patient_id = $patient->getKey();
        $prescription->doctor_id = $doctorId;
        $prescription->save();

        return $prescription;
    }

    protected function getRedirectUrl(): string
    {
        return PrescriptionResource::getUrl('view', ['record' => $this->record]);
    }
}
