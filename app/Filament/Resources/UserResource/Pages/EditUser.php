<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        UserResource::guardRole($data['role'] ?? $record->role);

        $record->fill(collect($data)->only(['name', 'email', 'password', 'role'])->all());
        $record->save();

        return $record;
    }

    protected function afterSave(): void
    {
        UserResource::syncDoctorProfile($this->record, $this->data['doctor_profile_id'] ?? null);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(fn () => UserResource::canDelete($this->record)),
        ];
    }
}
