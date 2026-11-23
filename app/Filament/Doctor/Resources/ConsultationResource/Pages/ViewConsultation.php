<?php

namespace App\Filament\Doctor\Resources\ConsultationResource\Pages;

use App\Filament\Doctor\Resources\ConsultationResource;
use App\Filament\Doctor\Resources\PrescriptionResource;
use App\Models\Consultation;
use App\Support\Audit;
use App\Support\CurrentDoctor;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewConsultation extends ViewRecord
{
    protected static string $resource = ConsultationResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        Audit::log('viewed', $this->record);
    }

    public function getTitle(): string
    {
        return 'Consulta · '.$this->record->patient?->name;
    }

    protected function getHeaderActions(): array
    {
        /** @var Consultation $consultation */
        $consultation = $this->record;
        $isAuthor = (int) $consultation->doctor_id === CurrentDoctor::idOrNone();

        return [
            Actions\Action::make('sign')
                ->label('Firmar consulta')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => ! $this->record->isSigned() && $isAuthor)
                ->requiresConfirmation()
                ->modalHeading('¿Firmar la consulta?')
                ->modalDescription('Después de firmar no podrás modificarla; solo agregar notas aclaratorias.')
                ->action(function (): void {
                    $this->record->sign();
                    Notification::make()->title('Consulta firmada')->success()->send();
                    $this->refreshFormData(['status', 'signed_at']);
                }),

            Actions\Action::make('addendum')
                ->label('Agregar nota aclaratoria')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->visible(fn () => $this->record->isSigned() && CurrentDoctor::id() !== null)
                ->form([
                    Forms\Components\Textarea::make('body')->label('Nota aclaratoria')->required()->rows(4)->maxLength(5000),
                ])
                ->action(function (array $data): void {
                    $this->record->addAddendum($data['body'], auth()->user());
                    Notification::make()->title('Nota agregada')->success()->send();
                }),

            Actions\Action::make('prescription')
                ->label('Crear fórmula')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn () => PrescriptionResource::getUrl('create', [
                    'patient' => $this->record->patient_id,
                    'consultation' => $this->record->getKey(),
                ])),

            Actions\EditAction::make()->visible(fn () => ConsultationResource::canEdit($this->record)),
        ];
    }
}
