<?php

namespace App\Filament\Doctor\Resources\PatientResource\Pages;

use App\Filament\Doctor\Resources\ConsultationResource;
use App\Filament\Doctor\Resources\PatientResource;
use App\Filament\Doctor\Resources\PrescriptionResource;
use App\Support\Audit;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Cada apertura de la historia clínica queda registrada.
        Audit::log('viewed', $this->record);
    }

    public function getTitle(): string
    {
        return 'Historia de '.$this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('newConsultation')
                ->label('Nueva consulta')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(fn () => ConsultationResource::getUrl('create', ['patient' => $this->record->getKey()])),
            Actions\Action::make('newPrescription')
                ->label('Nueva fórmula')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn () => PrescriptionResource::getUrl('create', ['patient' => $this->record->getKey()])),
            Actions\EditAction::make()->color('gray'),
        ];
    }
}
