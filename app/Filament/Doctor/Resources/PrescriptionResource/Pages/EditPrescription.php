<?php

namespace App\Filament\Doctor\Resources\PrescriptionResource\Pages;

use App\Filament\Doctor\Resources\PrescriptionResource;
use App\Models\Consultation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrescription extends EditRecord
{
    protected static string $resource = PrescriptionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['consultation_id'])) {
            $data['consultation_id'] = Consultation::query()->withoutGlobalScope('clinic')
                ->where('clinic_id', $this->record->clinic_id)
                ->where('patient_id', $this->record->patient_id)
                ->whereKey($data['consultation_id'])
                ->value('id');
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Descartar borrador'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PrescriptionResource::getUrl('view', ['record' => $this->record]);
    }
}
