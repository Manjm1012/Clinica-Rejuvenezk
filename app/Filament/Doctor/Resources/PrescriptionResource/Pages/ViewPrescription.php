<?php

namespace App\Filament\Doctor\Resources\PrescriptionResource\Pages;

use App\Filament\Doctor\Resources\PrescriptionResource;
use App\Models\Prescription;
use App\Support\Audit;
use App\Support\CurrentDoctor;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use LogicException;

class ViewPrescription extends ViewRecord
{
    protected static string $resource = PrescriptionResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        Audit::log('viewed', $this->record);
    }

    public function getTitle(): string
    {
        return 'Fórmula · '.$this->record->patient?->name;
    }

    private function isAuthor(): bool
    {
        return (int) $this->record->doctor_id === CurrentDoctor::idOrNone();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('issue')
                ->label('Emitir fórmula')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $this->record->isDraft() && $this->isAuthor())
                ->requiresConfirmation()
                ->modalHeading('¿Emitir la fórmula?')
                ->modalDescription('Quedará disponible para el paciente en su portal y ya no se podrá modificar.')
                ->action(function (): void {
                    try {
                        $this->record->issue();
                        Notification::make()->title('Fórmula emitida')->body('Número '.$this->record->code)->success()->send();
                    } catch (LogicException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Actions\Action::make('print')
                ->label('Imprimir / PDF')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->visible(fn () => $this->record->status !== Prescription::STATUS_DRAFT)
                ->url(fn () => route('prescriptions.print', $this->record), shouldOpenInNewTab: true),

            Actions\Action::make('annul')
                ->label('Anular')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->isIssued() && $this->isAuthor())
                ->form([
                    Forms\Components\Textarea::make('reason')->label('Motivo de la anulación')->required()->maxLength(500),
                ])
                ->modalDescription('La fórmula quedará marcada como anulada (no se elimina).')
                ->action(function (array $data): void {
                    $this->record->annul($data['reason']);
                    Notification::make()->title('Fórmula anulada')->warning()->send();
                }),

            Actions\EditAction::make()->visible(fn () => PrescriptionResource::canEdit($this->record)),
        ];
    }
}
