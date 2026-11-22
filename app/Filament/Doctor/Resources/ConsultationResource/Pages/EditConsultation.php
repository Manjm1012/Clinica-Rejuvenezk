<?php

namespace App\Filament\Doctor\Resources\ConsultationResource\Pages;

use App\Filament\Doctor\Resources\ConsultationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConsultation extends EditRecord
{
    protected static string $resource = ConsultationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Descartar borrador'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ConsultationResource::getUrl('view', ['record' => $this->record]);
    }
}
