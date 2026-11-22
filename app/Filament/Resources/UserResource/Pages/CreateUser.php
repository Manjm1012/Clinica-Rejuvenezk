<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Support\AdminClinic;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** La clínica es siempre la del administrador; nunca se crea un super admin desde aquí. */
    protected function handleRecordCreation(array $data): Model
    {
        $clinicId = AdminClinic::id();
        abort_unless($clinicId, 403, 'Tu usuario no tiene una clínica asignada.');

        $user = new User(collect($data)->only(['name', 'email', 'password', 'role'])->all());
        $user->clinic_id = $clinicId;
        $user->is_super_admin = false;
        $user->save();

        return $user;
    }

    protected function afterCreate(): void
    {
        UserResource::syncDoctorProfile($this->record, $this->data['doctor_profile_id'] ?? null);
    }
}
