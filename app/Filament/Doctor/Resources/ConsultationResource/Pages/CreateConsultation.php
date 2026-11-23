<?php

namespace App\Filament\Doctor\Resources\ConsultationResource\Pages;

use App\Filament\Doctor\Resources\ConsultationResource;
use App\Models\Consultation;
use App\Models\Patient;
use App\Support\AdminClinic;
use App\Support\CurrentDoctor;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateConsultation extends CreateRecord
{
    protected static string $resource = ConsultationResource::class;

    protected static ?string $title = 'Nueva consulta';

    /** Clínica, paciente (de la clínica) y médico (el usuario actual) se fijan aquí, nunca desde el formulario. */
    protected function handleRecordCreation(array $data): Model
    {
        $clinicId = AdminClinic::id();
        $doctorId = CurrentDoctor::id();
        abort_unless($clinicId && $doctorId, 403);

        $patient = Patient::query()->withoutGlobalScope('clinic')
            ->where('clinic_id', $clinicId)
            ->findOrFail($data['patient_id']);

        $consultation = new Consultation($data);
        $consultation->clinic_id = $clinicId;
        $consultation->patient_id = $patient->getKey();
        $consultation->doctor_id = $doctorId;
        $consultation->save();

        return $consultation;
    }

    protected function getRedirectUrl(): string
    {
        return ConsultationResource::getUrl('view', ['record' => $this->record]);
    }
}
