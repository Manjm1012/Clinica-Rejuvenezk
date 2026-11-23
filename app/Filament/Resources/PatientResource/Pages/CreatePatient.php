<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use App\Models\Patient;
use App\Support\AdminClinic;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

    /** clinic_id se asigna explícitamente con la clínica del usuario (no es asignable en masa). */
    protected function handleRecordCreation(array $data): Model
    {
        $clinicId = AdminClinic::id();
        abort_unless($clinicId, 403, 'Tu usuario no tiene una clínica asignada.');

        $patient = new Patient($data);
        $patient->clinic_id = $clinicId;
        $patient->save();

        return $patient;
    }
}
