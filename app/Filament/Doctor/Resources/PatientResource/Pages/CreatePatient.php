<?php

namespace App\Filament\Doctor\Resources\PatientResource\Pages;

use App\Filament\Doctor\Resources\PatientResource;
use App\Models\Patient;
use App\Support\AdminClinic;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

    protected static ?string $title = 'Registrar paciente';

    protected function handleRecordCreation(array $data): Model
    {
        $clinicId = AdminClinic::id();
        abort_unless($clinicId, 403, 'Tu usuario no tiene una clínica asignada.');

        $patient = new Patient($data);
        $patient->clinic_id = $clinicId;
        $patient->save();

        return $patient;
    }

    protected function getRedirectUrl(): string
    {
        return PatientResource::getUrl('view', ['record' => $this->record]);
    }
}
